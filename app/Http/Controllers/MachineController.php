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
}
