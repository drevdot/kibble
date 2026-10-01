# Kibble — Dispensador IoT de alimento y agua

Backend Laravel 13 + panel Vue 3 + WebSockets (Reverb) + ESP32. Cada máquina se identifica por su **MAC address**.

```
ESP32 --POST /sensors--> Laravel --GET /machine-status--> Vue (panel)
Vue --POST /dispense--> Laravel --WS DispenseTriggered--> ESP32 (servo)
```

## 1. Requisitos

- Laravel Herd (recomendado para el equipo) con PHP 8.5, o PHP 8.3+ + Composer en su defecto. Node 22, SQLite (dev).
- Para tiempo real: un proceso `php artisan reverb:start --port=8080` corriendo (Herd no lo levanta solo).

## 2. Instalación

### Opción A — con Herd (la del equipo, no usar `serve`)

```bash
# 1. Ubica la carpeta del proyecto donde Herd la vea (directorio parqueado o linkeado)
herd link kibble        # si la carpeta no se llama kibble; si ya está parqueada, omite esto
herd secure kibble      # para https://kibble.test (coincide con APP_URL)

# 2. Dependencias y BD
composer install
cp .env.example .env   # o usa tu .env existente (debe tener APP_URL=https://kibble.test)
php artisan key:generate
php artisan migrate
npm install
npm run build          # o npm run dev para desarrollo
```

Abre `https://kibble.test`. **No ejecutes `php artisan serve`**: Herd ya sirve el sitio por Nginx y `serve` solo crearía un segundo servidor en `:8000` que confunde URLs, CORS y el host del WebSocket.

En otra terminal (obligatorio para WS, también con Herd):

```bash
php artisan reverb:start --port=8080
```

### Opción B — sin Herd

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve      # app en http://localhost:8000 (ajusta APP_URL a http://localhost:8000)
```

Verifica rutas (ambas opciones):

```bash
php artisan route:list --path=api
```

## 3. Configuración `.env` (Reverb)

```ini
APP_URL=https://kibble.test
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=554557
REVERB_APP_KEY=mkgwlpvhs5ile3vhhkxk
REVERB_APP_SECRET=9d5j56xytrvw73u50uh6
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY=mkgwlpvhs5ile3vhhkxk
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

> `VITE_*` debe llevar valores explícitos (no `${...}`). Tras cambiar `.env`, corre `npm run build` de nuevo.
> Nota Herd: la página se sirve por HTTPS (`herd secure`), pero Reverb aquí escucha `ws` plano en `8080`. El navegador intentará `wss://` y fallará el WS (el REST sigue funcionando). Para tiempo real completo hay que exponer Reverb con TLS o usar el mismo host público; de momento el panel funciona sin live-update.

## 4. API (contrato oficial con el ESP32)

Base: `https://kibble.test` (sin auth por ahora, prototipo abierto).

| Método | Ruta | Quién la usa | Body | Respuesta |
|---|---|---|---|---|
| `GET` | `/api/machine-status` | Vue | — | Máquina + últimas 5 dispensaciones |
| `POST` | `/api/machine/{mac}/sensors` | ESP32 | `{food_level: 0-100, water_level: 0-100}` | `{message}` |
| `POST` | `/api/machine/{machineId}/dispense` | Vue | `{dispense_type: food\|water}` | `{message, log}` + dispara WS |
| `POST` | `/api/dispensations` | ESP32 | `{machine_id, dispense_type: food\|water, trigger_source: manual\|schedule}` | log `201` |
| `GET` | `/api/schedules/{machineId}` | Vue / ESP32 fallback | — | lista de horarios |
| `POST` | `/api/schedules` | Vue | `{machine_id, trigger_time, dispense_type, portion_grams?}` | horario creado |
| `DELETE` | `/api/schedules/{id}` | Vue | — | `{message}` |
| `GET` | `/api/test-dispense/{mac}` | solo pruebas | — | dispara evento `food` (temporal) |

Notas:
- `{mac}` en URL es la MAC **cruda** (`AA:BB:CC:DD:EE:FF`). En código usa `encodeURIComponent(mac)` porque los `:` son especiales en URLs.
- `{machineId}` es el ID numérico de la tabla `machines`, no la MAC.
- `updateSensors` conserva el campo no enviado (puedes mandar solo `food_level`).
- MAC inexistente → `404`. Validación fallida → `422`.

## 5. WebSockets (regla de oro)

Reverb/Pusher **no acepta `:`** en nombres de canal. Regla única en backend, Vue y ESP32:

```
MAC cruda:  AA:BB:CC:DD:EE:FF
Canal:      machine.aa-bb-cc-dd-ee-ff   (minúsculas, ':' -> '-')
```

- Backend: `app/Events/DispenseTriggered.php` (`broadcastOn` / `broadcastWith`).
- Evento: `DispenseTriggered`, payload `{action: food|water, mac_address (original), timestamp}`.
- Vue: `resources/js/echo.js` + `App.vue` (`safeMac()` + `Echo.channel(...).listen('DispenseTriggered', ...)`).
- Si un lado usa la cruda y otro la sanitizada, **no se encuentran** aunque sea la misma máquina. Cada MAC tiene su canal propio: 2 fierros no se molestan.

### ESP32 — pseudocódigo

```cpp
#include <WiFi.h>
// + cliente WS compatible con protocolo Pusher/Reverb

String mac = WiFi.macAddress();          // AA:BB:CC:DD:EE:FF
mac.toLowerCase();
mac.replace(":", "-");                  // aa-bb-cc-dd-ee-ff
String channel = "machine." + mac;
ws.subscribe(channel);                  // escucha evento DispenseTriggered

// al recibir {"action":"food","mac_address":"AA:BB:..."}:
if (msg.mac_address == WiFi.macAddress()) {
  if (msg.action == "food") servirComida();
  else servirAgua();
  // opcional: POST /api/dispensations para dejar historial
}

// cada X segundos:
int food = distanciaAUltrasonicoA_porcentaje();
int water = distanciaAUltrasonicoB_porcentaje();
POST("https://kibble.test/api/machine/" + urlEncode(WiFi.macAddress()) + "/sensors",
     "{\"food_level\":" + String(food) + ",\"water_level\":" + String(water) + "}");
```

Pendiente de definir en hardware: fórmula distancia→% (qué distancia = 0% y 100%), cada cuántos segundos reportar, pines/GPIOs y modelo exacto de placa, servos y ultrasónicos.

## 6. Alta de una máquina nueva

1. Flashea el ESP32, obtén su MAC real (`WiFi.macAddress()` por serial).
2. Crea su fila en `machines` (`mac_address` única, `alias` = nombre mascota). Hoy es manual (tinker/seeder); no hay auto-registro.
3. Prueba: `POST /sensors` → revisa el panel; `POST /dispense` desde el panel → el servo se mueve.

## 7. Base de datos (3 tablas)

- `machines`: estado actual. `user_id (FK)`, `mac_address unique`, `alias`, `food_level_pct`, `water_level_pct`.
- `dispensations`: pasado inmutable. `machine_id (FK)`, `dispense_type (food|water)`, `trigger_source (manual|schedule)`.
- `schedules`: futuro. `machine_id (FK)`, `trigger_time`, `dispense_type`, `portion_grams`, `is_active`.

Relaciones en `app/Models/Machine.php`: `belongsTo(User)`, `hasMany(Dispensation)`, `hasMany(Schedule)`.

## 8. Errores comunes

| Síntoma | Causa | Fix |
|---|---|---|
| `Invalid channel name machine.AA:BB...` (500 en dispense) | canal con `:` | Ya corregido: usar MAC sanitizada en los 3 lados |
| `WebSocket connection to 'wss://localhost:8080...' failed`, la app igual carga | `VITE_REVERB_HOST=localhost` no existe desde el navegador + `wss` vs `ws` plano | Exponer Reverb con host/TLS alcanzable; sin WS solo falta el tiempo real, el REST sigue |
| `404` en `/sensors` | MAC no dada de alta | Crear fila en `machines` |
| `500` en `POST /dispensations` | (histórico) faltaba `store()` | Ya restaurado |
| Panel muestra `0%` siempre | el ESP32 aún no reportó (default `0`) | Revisar loop de reporte |

## 9. Lo que falta (roadmap)

- [ ] Scheduler automático: job que lea `schedules` (`trigger_time` + `is_active`) y dispare solo (hoy los horarios se guardan pero nada los ejecuta).
- [ ] Auth para ESP32 (Sanctum instalado, rutas aún abiertas) + auto-registro de MAC.
- [ ] Seeders/factories de `Machine`/`Schedule` y tests de los endpoints.
- [ ] Quitar ruta temporal `test-dispense` cuando haya botón real en prod.
- [ ] Definir BOM, pinout y calibración de sensores con el equipo de hardware.
