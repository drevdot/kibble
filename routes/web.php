<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MachineController;

Route::get('/', function () {
    return view('welcome');
});

//Endpoint para que Vue consuma los datos de la máquina
Route::get('/api/machine-status', [MachineController::class, 'status']);

//Ruta temporal para probar la transmisión de eventos al WebSocket
Route::get('/api/test-dispense/{mac}', function($mac) {
    // Despacha el evento al WebSocket
    DispenseTriggered::dispatch($mac, 'food');
    return response()->json(['message' => 'Señal enviada a la máquina ' . $mac]);
});