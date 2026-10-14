<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImportacionUsuariosController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/usuarios/importaciones', [ImportacionUsuariosController::class, 'store']);
});
