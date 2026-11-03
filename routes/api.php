<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GrupoAlumnoController;
use App\Http\Controllers\Api\GrupoController;
use App\Http\Controllers\Api\ImportacionUsuariosController;
use App\Http\Controllers\Api\MateriaController;
use App\Http\Controllers\Api\SemestreController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/mi-perfil', [AuthController::class, 'miPerfil']);
    Route::post('/cambiar-nip', [AuthController::class, 'cambiarNip']);

    // * Gestión de usuarios: los permisos los decide UsuarioPolicy.
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update']);
    Route::patch('/usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado']);
    Route::post('/usuarios/{usuario}/restablecer-nip', [UsuarioController::class, 'restablecerNip']);
    Route::post('/usuarios/importaciones', [ImportacionUsuariosController::class, 'store']);
    Route::get('/usuarios/importaciones/{id}', [ImportacionUsuariosController::class, 'show']);

    // * Materias por semestre: los permisos los decide MateriaPolicy.
    Route::get('/semestres', [SemestreController::class, 'index']);
    Route::post('/materias', [MateriaController::class, 'store']);
    Route::put('/materias/{materia}', [MateriaController::class, 'update']);
    Route::patch('/materias/{materia}/estado', [MateriaController::class, 'cambiarEstado']);

    // * Grupos: los permisos los decide GrupoPolicy.
    Route::get('/grupos', [GrupoController::class, 'index']);
    Route::post('/grupos', [GrupoController::class, 'store']);
    Route::put('/grupos/{grupo}', [GrupoController::class, 'update']);
    Route::get('/grupos/{grupo}/alumnos', [GrupoAlumnoController::class, 'index']);
    Route::post('/grupos/{grupo}/alumnos', [GrupoAlumnoController::class, 'store']);
    Route::patch('/grupos/{grupo}/alumnos/{alumno}', [GrupoAlumnoController::class, 'cambiarEstado']);
});
