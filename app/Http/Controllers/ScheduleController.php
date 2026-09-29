<?php
namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // Obtener los horarios de una máquina
    public function index($machineId) {
        return response()->json(Schedule::where('machine_id', $machineId)->get());
    }

    // Crear un nuevo horario desde Vue
    public function store(Request $request) {
        $schedule = Schedule::create($request->validate([
            'machine_id' => 'required|exists:machines,id',
            'trigger_time' => 'required',
            'dispense_type' => 'required|in:food,water',
            'portion_grams' => 'integer'
        ]));
        return response()->json($schedule);
    }

    // Eliminar un horario
    public function destroy($id) {
        Schedule::destroy($id);
        return response()->json(['message' => 'Horario eliminado']);
    }
}