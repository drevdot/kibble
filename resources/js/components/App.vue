<template>
    <div style="font-family: sans-serif; padding: 2rem; max-width: 600px; margin: auto;">
        <h1>Kibble - Panel IoT</h1>

        <div v-if="machine" style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2>{{ machine.alias }}</h2>
            <p><strong>MAC:</strong> {{ machine.mac_address }}</p>
            <p><strong>Nivel de Alimento:</strong> {{ machine.food_level_pct }}%</p>
            <p><strong>Nivel de Agua:</strong> {{ machine.water_level_pct }}%</p>

            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button @click="dispense('food')" :disabled="sending">
                    {{ sending ? 'Enviando...' : 'Servir comida' }}
                </button>
                <button @click="dispense('water')" :disabled="sending">
                    {{ sending ? 'Enviando...' : 'Servir agua' }}
                </button>
            </div>
            <p v-if="statusMsg" style="margin-top: 0.5rem;">{{ statusMsg }}</p>
            <p v-if="lastSignal" style="margin-top: 0.5rem; font-size: 0.9rem; color: #555;">
                Última señal WS: {{ lastSignal.action }} ({{ lastSignal.timestamp }})
            </p>

            <h3 style="margin-top: 1.5rem;">Últimas Acciones:</h3>
            <ul>
                <li v-for="log in machine.dispensations" :key="log.id">
                    Dispensado de {{ log.dispense_type === 'food' ? 'Alimento' : 'Agua' }}
                    ({{ log.trigger_source === 'manual' ? 'Manual' : 'Programado' }})
                </li>
            </ul>
        </div>

        <div v-else>
            <p>Conectando con el servidor...</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const machine = ref(null);
const sending = ref(false);
const statusMsg = ref('');
const lastSignal = ref(null);
let echoChannelName = null;

 // Misma regla que el backend: minúsculas + ':' -> '-'
const safeMac = (mac) => mac.toLowerCase().replaceAll(':', '-');

const loadMachine = async () => {
    const response = await fetch('/api/machine-status');
    machine.value = await response.json();
};

const subscribeToMachine = () => {
    if (!machine.value?.mac_address || !window.Echo) return;
    echoChannelName = `machine.${safeMac(machine.value.mac_address)}`;
    window.Echo.channel(echoChannelName)
        .listen('DispenseTriggered', (e) => {
            lastSignal.value = e;
        });
};

// El endpoint nuevo usa el ID numérico, no la MAC en la URL:
// POST /api/machine/{machineId}/dispense { dispense_type: 'food' | 'water' }
const dispense = async (type) => {
    if (!machine.value) return;
    sending.value = true;
    statusMsg.value = '';
    try {
        const response = await fetch(`/api/machine/${machine.value.id}/dispense`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ dispense_type: type }),
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message ?? 'Error al enviar la señal');
        statusMsg.value = data.message ?? 'Señal enviada correctamente';
        await loadMachine();
    } catch (error) {
        statusMsg.value = `Error: ${error.message}`;
        console.error('Error al dispensar:', error);
    } finally {
        sending.value = false;
    }
};

onMounted(async () => {
    try {
        await loadMachine();
        subscribeToMachine();
    } catch (error) {
        console.error("Error al cargar los datos:", error);
    }
});

onUnmounted(() => {
    if (echoChannelName) window.Echo.leave(echoChannelName);
});
</script>
