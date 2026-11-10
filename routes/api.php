<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\InscripcionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (no requieren token)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (requieren token de Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // --- Módulo Solo Administrador (RF-13, RF-15, RF-16) ---
    Route::middleware('role:Administrador')->group(function () {
        // Route::apiResource('usuarios', UsuarioController::class);
        // Route::apiResource('auditorias', AuditoriaController::class)->only(['index','show']);
    });

    // --- Módulo Directiva y Administrador (RF-01 al 12) ---
    Route::middleware('role:Administrador,Directiva')->group(function () {
        Route::apiResource('categorias', CategoriaController::class);
        Route::apiResource('productos', ProductoController::class);
        
        // Para ventas, usamos apiResource pero limitamos a index, store y show
        Route::apiResource('ventas', VentaController::class)->only(['index', 'store', 'show']);
        
        // Las demás rutas se irán agregando en las siguientes fases:
        // Route::apiResource('ferias', FeriaController::class);
        // Route::apiResource('cursos', CursoController::class);
        // Route::apiResource('alumnos', AlumnoController::class);
    });

    // --- Módulo Alumno (RF-08) ---
    Route::middleware('role:Administrador,Directiva,Alumno')->group(function () {
        // Route::post('/inscribirse', [InscripcionController::class, 'store']);
    });

        // --- Módulo Directiva y Administrador (RF-01 al 12) ---
    Route::middleware('role:Administrador,Directiva')->group(function () {
        // ... (las anteriores: categorias, productos, ventas)

        // Cursos e Inscripciones
        Route::apiResource('cursos', CursoController::class)->only(['index', 'store']);
        Route::post('/inscripciones/{id}/confirmar', [InscripcionController::class, 'confirmar']);
    });

    // --- Módulo Alumno y Directiva (Inscribirse) ---
    Route::middleware('role:Administrador,Directiva,Alumno')->group(function () {
        Route::post('/inscripciones', [InscripcionController::class, 'store']);
    });
    
});