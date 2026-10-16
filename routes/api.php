<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MachineController;

// Endpoint único de sincronización bidireccional
Route::post('/machine/{id}/sync', [MachineController::class, 'sync']);

Route::get('/machine/{id}', [MachineController::class, 'show']);
Route::post('/machine/{id}/dispense', [MachineController::class, 'dispense']);
