<?php
namespace App\Http\Controllers;

use App\Models\Dispensation;
use Illuminate\Http\Request;

class DispensationController extends Controller
{
    // Registrar cada vez que se sirve comida/agua (manual o automático)
    public function store(Request $request) {
        $log = Dispensation::create($request->validate([
            'machine_id' => 'required|exists:machines,id',
            'dispense_type' => 'required|in:food,water',
            'trigger_source' => 'required|in:manual,schedule'
        ]));
        return response()->json($log);
    }
}