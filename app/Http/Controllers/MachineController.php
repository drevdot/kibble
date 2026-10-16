<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function sync(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);

        //Actualizar el estado de la máquina con los datos del ESP32
        $machine->update([
            'water_level' => $request->input('water_level', $machine->water_level),
            'food_level'  => $request->input('food_level', $machine->food_level),
            'is_online'   => true,
        ]);

        //Verificar si hay órdenes manuales pendientes en la tabla dispensations
        $pendingWater = $machine->dispensations()->where('type', 'water')->exists();
        $pendingFood  = $machine->dispensations()->where('type', 'food')->exists();

        //Limpiar la cola eliminando las órdenes que estamos a punto de enviar al hardware
        if ($pendingWater) {
            $machine->dispensations()->where('type', 'water')->delete();
        }
        if ($pendingFood) {
            $machine->dispensations()->where('type', 'food')->delete();
        }

        //Responder al ESP32 con formato JSON
        return response()->json([
            'dispense_water' => $pendingWater,
            'dispense_food'  => $pendingFood
        ]);
    }

    // Método para que Vue lea el estado actual
    public function show($id)
    {
        return response()->json(Machine::findOrFail($id));
    }

    // Método para que Vue registre una orden manual
    public function dispense(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);
        
        $machine->dispensations()->create([
            'type' => $request->input('type'), // 'water' o 'food'
            'trigger_source' => 'manual'
        ]);

        return response()->json(['message' => 'Orden registrada']);
    }
}
