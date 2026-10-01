<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DispensationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Endpoint para que el ESP32 mande los porcentajes del ultrasonico
Route::post('/machine/{mac}/sensors', [MachineController::class, 'updateSensors']);

//Endpoint para que el ESP32 o Vue registren que ya sirvieron alimento
Route::post('/dispensations', [DispensationController::class, 'store']);

//Endpoints para que Vue administre los horarios (CRUD)
Route::get('/schedules/{machineId}', [ScheduleController::class, 'index']);
Route::post('/schedules', [ScheduleController::class, 'store']);
Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);

//Endpoint para que Vue administre la dispensación manual
Route::post('/machine/{machineId}/dispense', [DispensationController::class, 'manualDispense']);
