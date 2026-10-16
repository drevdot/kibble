<template>
  <div class="nothing-dashboard">
    <!-- Header Técnico -->
    <header class="n-header">
      <div class="n-brand">
        <h1>KIBBLE - PANEL DE ALIMENTACIÓN</h1>
        <span class="n-version">KIB-v1.0</span>
      </div>
      <div class="n-status-panel">
        <div class="n-machine-id">
          [ ID: {{ machine ? machine.name.toUpperCase() : 'AWAITING_DATA' }} ]
        </div>
        <div class="n-connection" :class="{ 'is-online': machine && machine.is_online }">
          <span class="n-dot"></span>
          <span class="n-status-text">{{ machine && machine.is_online ? 'SYS.ONLINE' : 'SYS.OFFLINE' }}</span>
        </div>
      </div>
    </header>

    <!-- Interfaz Principal Elaborada -->
    <main v-if="machine" class="n-grid">
      
      <!-- Módulo de Agua -->
      <section class="n-module">
        <div class="n-module-header">
          <h2>[ AGUA ]</h2>
          <span class="n-hex">H2O</span>
        </div>
        
        <div class="n-module-content">
          <!-- Barra Vertical Elaborada -->
          <div class="n-bar-wrapper">
            <div class="n-bar-container">
              <div class="n-bar-grid"></div>
              <div class="n-bar-fill" :style="{ height: machine.water_level + '%' }"></div>
            </div>
            <div class="n-scale">
              <span>MAX</span>
              <span>MED</span>
              <span>MIN</span>
            </div>
          </div>
          
          <!-- Lectura de Datos -->
          <div class="n-data-cluster">
            <div class="n-percentage">{{ machine.water_level }}<span>%</span></div>
            <div class="n-readout">RESTANTE</div>
            <button class="n-btn" @click="dispense('water')">
              SERVIR-AGUA <span class="n-arrow">-></span>
            </button>
          </div>
        </div>
      </section>

      <!-- Módulo de Comida -->
      <section class="n-module">
        <div class="n-module-header">
          <h2>[ COMIDA ]</h2>
          <span class="n-hex">HOLA PROFE PONGANOS 100</span>
        </div>
        
        <div class="n-module-content">
          <!-- Barra Vertical Elaborada -->
          <div class="n-bar-wrapper">
            <div class="n-bar-container">
              <div class="n-bar-grid"></div>
              <div class="n-bar-fill food-fill" :style="{ height: machine.food_level + '%' }"></div>
            </div>
            <div class="n-scale">
              <span>MAX</span>
              <span>MED</span>
              <span>MIN</span>
            </div>
          </div>
          
          <!-- Lectura de Datos -->
          <div class="n-data-cluster">
            <div class="n-percentage">{{ machine.food_level }}<span>%</span></div>
            <div class="n-readout">RESTANTE</div>
            <button class="n-btn" @click="dispense('food')">
              SERVIR-COMIDA <span class="n-arrow">-></span>
            </button>
          </div>
        </div>
      </section>
      
    </main>

    <!-- Estado de Carga -->
    <div v-else class="n-loading">
      <span class="n-dot is-blinking"></span> ESTABLECIENDO ENLACE DE DATOS...
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const machine = ref(null);
let interval = null;

const fetchMachineData = async () => {
    try {
        const response = await axios.get('/api/machine/1');
        machine.value = response.data;
    } catch (error) {
        console.error("SYS.ERR: Data fetch failed", error);
    }
};

const dispense = async (type) => {
    try {
        await axios.post('/api/machine/1/dispense', { type });
        // Efecto visual tipo consola
        console.log(`[SYS] Orden de ${type.toUpperCase()} enviada al hardware.`);
    } catch (error) {
        console.error("SYS.ERR: Command rejected", error);
    }
};

onMounted(() => {
    fetchMachineData();
    interval = setInterval(fetchMachineData, 2000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>

<style scoped>
/* Tipografía monoespaciada para el look Nothing / Terminal */
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap');

.nothing-dashboard {
  font-family: 'Space Mono', monospace;
  background-color: #000000;
  color: #FFFFFF;
  min-height: 100vh;
  padding: 40px 20px;
  box-sizing: border-box;
  background-image: radial-gradient(#333 1px, transparent 1px);
  background-size: 20px 20px;
}

/* Header */
.n-header {
  max-width: 900px;
  margin: 0 auto 40px auto;
  border-bottom: 2px dashed #333333;
  padding-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.n-brand h1 {
  font-size: 2.5rem;
  margin: 0;
  letter-spacing: -2px;
  text-transform: uppercase;
}

.n-version {
  color: #EA1616; /* Nothing Red */
  font-size: 0.9rem;
  font-weight: bold;
}

.n-status-panel {
  text-align: right;
  font-size: 0.9rem;
}

.n-machine-id {
  color: #888888;
  margin-bottom: 8px;
}

.n-connection {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 4px 12px;
  border: 1px solid #333333;
  border-radius: 20px;
}

.n-dot {
  width: 10px;
  height: 10px;
  background-color: #555555;
  border-radius: 50%;
}

.is-online .n-dot {
  background-color: #EA1616;
  box-shadow: 0 0 8px #EA1616;
}

.is-online .n-status-text {
  color: #FFFFFF;
}

/* Cuadrícula Principal */
.n-grid {
  max-width: 900px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 30px;
}

/* Tarjetas de Módulo */
.n-module {
  background-color: rgba(0, 0, 0, 0.8);
  border: 1px solid #333333;
  border-radius: 15px;
  padding: 25px;
  backdrop-filter: blur(10px);
  position: relative;
  overflow: hidden;
}

/* Decoración estilo hardware expuesto */
.n-module::before {
  content: '';
  position: absolute;
  top: 10px;
  right: 10px;
  width: 30px;
  height: 30px;
  border-top: 2px solid #EA1616;
  border-right: 2px solid #EA1616;
  opacity: 0.5;
}

.n-module-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #222222;
  padding-bottom: 15px;
  margin-bottom: 25px;
}

.n-module-header h2 {
  font-size: 1.2rem;
  margin: 0;
  color: #DDDDDD;
}

.n-hex {
  background: #111111;
  padding: 2px 8px;
  font-size: 0.8rem;
  color: #666666;
  border: 1px solid #333;
}

/* Contenido del Módulo */
.n-module-content {
  display: flex;
  gap: 30px;
  align-items: flex-end;
}

/* Barras de Nivel */
.n-bar-wrapper {
  display: flex;
  gap: 10px;
  height: 200px;
}

.n-bar-container {
  width: 40px;
  background-color: #111111;
  border: 1px solid #333333;
  border-radius: 20px;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: flex-end;
}

.n-bar-grid {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background-image: linear-gradient(#333 1px, transparent 1px);
  background-size: 100% 10px;
  z-index: 2;
  opacity: 0.5;
}

.n-bar-fill {
  width: 100%;
  background-color: #FFFFFF;
  transition: height 0.5s ease-out;
  position: relative;
  z-index: 1;
}

/* Variación para comida (opcional, aunque Nothing usa monocromo, el rojo resalta) */
.food-fill {
  background-color: #EA1616;
}

.n-scale {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  font-size: 0.7rem;
  color: #555555;
  padding: 10px 0;
}

/* Zona de Datos y Botones */
.n-data-cluster {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.n-percentage {
  font-size: 3.5rem;
  font-weight: bold;
  line-height: 1;
  margin-bottom: 5px;
}

.n-percentage span {
  font-size: 1.5rem;
  color: #EA1616;
}

.n-readout {
  font-size: 0.8rem;
  color: #777777;
  margin-bottom: 30px;
  letter-spacing: 1px;
}

.n-btn {
  background-color: transparent;
  color: #FFFFFF;
  border: 1px dashed #FFFFFF;
  padding: 15px 20px;
  font-family: inherit;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.2s ease;
  border-radius: 5px;
}

.n-btn:hover {
  background-color: #FFFFFF;
  color: #000000;
  border-style: solid;
}

.n-arrow {
  color: #EA1616;
  font-weight: bold;
}

.n-btn:hover .n-arrow {
  color: #000000;
}

/* Pantalla de Carga */
.n-loading {
  text-align: center;
  margin-top: 100px;
  font-size: 1.2rem;
  color: #888888;
}

.is-blinking {
  animation: blink 1s infinite;
  display: inline-block;
  margin-right: 10px;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}
</style>