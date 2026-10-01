<?php
namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Dispensation;
use App\Events\DispenseTriggered;
use Illuminate\Http\Request;

class DispensationController extends Controller
{
    // Lo usa el ESP32 para confirmar "ya serví" (no dispara WS, solo guarda historial)
    public function store(Request $request) {
        $log = Dispensation::create($request->validate([
            'machine_id' => 'required|exists:machines,id',
            'dispense_type' => 'required|in:food,water',
            'trigger_source' => 'required|in:manual,schedule'
        ]));
        return response()->json($log, 201);
    }

    public function manualDispense(Request $request, $machineId)
    {
        //Validar que la interfaz envíe 'food' o 'water'
        $request->validate([
            'dispense_type' => 'required|in:food,water',
        ]);

        //Buscar la máquina para obtener su dirección MAC
        $machine = Machine::findOrFail($machineId);

        //Registrar la acción en el historial de la base de datos
        $log = Dispensation::create([
            'machine_id' => $machine->id,
            'dispense_type' => $request->dispense_type,
            'trigger_source' => 'manual',
        ]);

        //Disparar el evento en tiempo real por WebSockets hacia el ESP32
        DispenseTriggered::dispatch($machine->mac_address, $request->dispense_type);

        //Responder al frontend que todo salió bien
        return response()->json([
            'message' => 'Señal enviada a la máquina correctamente',
            'log' => $log
        ]);
    }
    
}