<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MachineController;

Route::get('/', function () {
    return view('welcome');
});

//Endpoint para que Vue consuma los datos de la máquina
Route::get('/api/machine-status', [MachineController::class, 'status']);