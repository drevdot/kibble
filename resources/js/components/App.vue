<template>
    <div style="font-family: sans-serif; padding: 2rem; max-width: 600px; margin: auto;">
        <h1>Kibble - Panel IoT</h1>
        
        <div v-if="machine" style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2>{{ machine.alias }}</h2>
            <p><strong>Nivel de Alimento:</strong> {{ machine.food_level_pct }}%</p>
            <p><strong>Nivel de Agua:</strong> {{ machine.water_level_pct }}%</p>
            
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
import { ref, onMounted } from 'vue';

const machine = ref(null);

onMounted(async () => {
    try {
        const response = await fetch('/api/machine-status');
        machine.value = await response.json();
    } catch (error) {
        console.error("Error al cargar los datos:", error);
    }
});
</script>