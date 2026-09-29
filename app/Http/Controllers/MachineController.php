<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;

class MachineController extends Controller
{
    public function status()
    {
        //Recupera la primera máquina y sus últimos registros de dispensación
        $machine = Machine::with(['dispensations' => function($query) {
            $query->latest()->limit(5);
        }])->first();

        return response()->json($machine);
    }
        public function updateSensors(Request $request, $mac)
    {
        $machine = Machine::where('mac_address', $mac)->firstOrFail();
    
        $machine->update([
            'food_level_pct' => $request->input('food_level', $machine->food_level_pct),
            'water_level_pct' => $request->input('water_level', $machine->water_level_pct),
        ]);

        return response()->json(['message' => 'Sensores actualizados correctamente']);
    }
}
