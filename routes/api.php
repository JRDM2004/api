<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImportacionUsuariosController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // * Gestión de usuarios: solo el administrador.
    Route::middleware('rol:administrador')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index']);
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update']);
        Route::post('/usuarios/importaciones', [ImportacionUsuariosController::class, 'store']);
        Route::get('/usuarios/importaciones/{id}', [ImportacionUsuariosController::class, 'show']);
    });
});
