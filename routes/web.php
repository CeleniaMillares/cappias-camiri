<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\CursoController;
use App\Http\Controllers\Web\InscripcionController;
use App\Http\Controllers\Web\AlumnoController;
use App\Http\Controllers\Web\ProductoController;
use App\Http\Controllers\Web\VentaController;
use App\Http\Controllers\Web\LiquidacionController;
use App\Http\Controllers\Web\FeriaController;
use App\Http\Controllers\Web\ReporteController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\CategoriaController;
use App\Http\Controllers\Web\SocioController;
use App\Http\Controllers\Web\PublicController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\PerfilController;
use App\Http\Controllers\Web\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Sin Login)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome'); 
});

// Login
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);
Route::get('/password/reset/{token}', [PasswordResetController::class, 'create'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'store'])->name('password.update');

// Vitrina Pública (Catálogo para Alumnos y Clientes)
Route::get('/vitrina/cursos', [PublicController::class, 'cursos'])->name('public.cursos');
Route::get('/vitrina/productos', [PublicController::class, 'productos'])->name('public.productos');

// Checkout de Invitado (Registro automático)
Route::get('/checkout/curso/{id_curso}', [CheckoutController::class, 'createCurso']);
Route::post('/checkout/curso/{id_curso}', [CheckoutController::class, 'storeCurso']);
Route::get('/checkout/producto/{id_producto}', [CheckoutController::class, 'createProducto']);
Route::post('/checkout/producto/{id_producto}', [CheckoutController::class, 'storeProducto']);

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren Login)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->group(function () {
    
    // 1. Dashboard (Todos los roles entran, el controlador filtra qué ven)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    /*
    |----------------------------------------------------------------------
    | 2. RUTAS EXCLUSIVAS DEL ADMINISTRADOR (TI)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:Administrador')->group(function () {
        // Rutas fijas de usuarios (deben ir antes de las dinámicas)
        Route::get('/usuarios', [AdminController::class, 'usuarios']);
        Route::get('/usuarios/crear', [AdminController::class, 'createUsuario']);
        Route::post('/usuarios', [AdminController::class, 'storeUsuario']);
        Route::get('/usuarios/promover', [AdminController::class, 'promoverSocios']);
        Route::post('/usuarios/promover/{id_socio}', [AdminController::class, 'asignarDirectiva']);
        Route::post('/usuarios/retirar-directiva/{id_socio}', [AdminController::class, 'retirarDirectiva']);

        // Rutas dinámicas de usuarios (con ID)
        Route::get('/usuarios/{id}/editar', [AdminController::class, 'editUsuario']);
        Route::put('/usuarios/{id}', [AdminController::class, 'updateUsuario']);
        Route::delete('/usuarios/{id}', [AdminController::class, 'destroyUsuario']);
        
        // Parámetros y Auditoría
        Route::get('/parametros', [AdminController::class, 'parametros']);
        Route::post('/parametros', [AdminController::class, 'updateParametros']);
        Route::post('/parametros/qr', [AdminController::class, 'uploadQr']); // 🔥 RUTA DEL QR AGREGADA
        Route::get('/auditoria', [AdminController::class, 'auditoria']);
    });

    /*
    |----------------------------------------------------------------------
    | 3. RUTAS COMPARTIDAS (Directiva, Socio y Alumno)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:Directiva,Socio/Instructor,Alumno')->group(function () {
        Route::get('/inscripciones', [InscripcionController::class, 'index']);
    });

    // Portal del Alumno
    Route::middleware('role:Alumno')->group(function () {
        Route::get('/mi-perfil', [PerfilController::class, 'alumno']);
    });

    /*
    |----------------------------------------------------------------------
    | 4. RUTAS COMPARTIDAS (Directiva y Socio/Instructor)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:Directiva,Socio/Instructor')->group(function () {
        Route::get('/alumnos', [AlumnoController::class, 'index']);
        Route::get('/alumnos/inscribir', [AlumnoController::class, 'create']);
        Route::post('/alumnos/inscribir', [AlumnoController::class, 'store']);
        Route::get('/alumnos/{id}/editar', [AlumnoController::class, 'edit']);
        Route::put('/alumnos/{id}', [AlumnoController::class, 'update']);
        Route::get('/alumnos/{id}/historial', [AlumnoController::class, 'historial']);

        // Listados
        Route::get('/cursos', [CursoController::class, 'index']);
        Route::get('/productos', [ProductoController::class, 'index']);
        Route::get('/ventas', [VentaController::class, 'index']);
        Route::get('/liquidaciones', [LiquidacionController::class, 'index']);

        // Inscripciones
        Route::get('/inscripciones/crear/{id_horario_curso}', [InscripcionController::class, 'create']);
        Route::post('/inscripciones', [InscripcionController::class, 'store']);
        
        // Ver alumnos de un curso
        Route::get('/cursos/{id}/alumnos', [CursoController::class, 'showAlumnos']);

        // Perfil del Socio
        Route::get('/mi-perfil-socio', [PerfilController::class, 'socio']);
        Route::put('/mi-perfil-socio', [PerfilController::class, 'updateSocio']);
        Route::put('/mi-perfil-socio/password', [PerfilController::class, 'updatePassword']);
    });

    /*
    |----------------------------------------------------------------------
    | 5. RUTAS OPERATIVAS (Solo Directiva)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:Directiva')->group(function () {
        Route::post('/inscripciones/{id}/confirmar', [InscripcionController::class, 'confirmar']);
        
        // --- Cursos ---
        Route::get('/cursos/crear', [CursoController::class, 'create']);
        Route::post('/cursos', [CursoController::class, 'store']);
        Route::get('/cursos/{id}/editar', [CursoController::class, 'edit']);
        Route::get('/cursos/{id}/edit', [CursoController::class, 'edit']); // Fallback
        Route::put('/cursos/{id}', [CursoController::class, 'update']);
        Route::delete('/cursos/{id}', [CursoController::class, 'destroy']);

        // --- Productos ---
        Route::get('/productos/crear', [ProductoController::class, 'create']);
        Route::post('/productos', [ProductoController::class, 'store']);
        Route::get('/productos/{id}/editar', [ProductoController::class, 'edit']);
        Route::get('/productos/{id}/edit', [ProductoController::class, 'edit']); // Fallback
        Route::put('/productos/{id}', [ProductoController::class, 'update']);
        Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);

        // --- Ventas ---
        Route::get('/ventas/crear', [VentaController::class, 'create']);
        Route::post('/ventas', [VentaController::class, 'store']);
        Route::post('/ventas/{id_venta}/confirmar-pago', [VentaController::class, 'confirmarPago']);

        // --- Categorías ---
        Route::get('/categorias', [CategoriaController::class, 'index']);
        Route::get('/categorias/crear', [CategoriaController::class, 'create']);
        Route::post('/categorias', [CategoriaController::class, 'store']);
        Route::get('/categorias/{id}/editar', [CategoriaController::class, 'edit']);
        Route::get('/categorias/{id}/edit', [CategoriaController::class, 'edit']); // Fallback
        Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

        // --- Ferias ---
        Route::get('/ferias', [FeriaController::class, 'index']);
        Route::get('/ferias/crear', [FeriaController::class, 'create']); 
        Route::get('/ferias/create', [FeriaController::class, 'create']); // Fallback
        Route::post('/ferias', [FeriaController::class, 'store']);
        Route::get('/ferias/{id}/editar', [FeriaController::class, 'edit']);
        Route::get('/ferias/{id}/edit', [FeriaController::class, 'edit']); // Fallback
        Route::put('/ferias/{id}', [FeriaController::class, 'update']);
        Route::delete('/ferias/{id}', [FeriaController::class, 'destroy']);

        // --- Gestión de Socios ---
        Route::get('/socios', [SocioController::class, 'index']);
        Route::get('/socios/crear', [SocioController::class, 'create']);
        Route::post('/socios', [SocioController::class, 'store']);
        Route::post('/socios/{id_socio}/confirmar-pendientes', [SocioController::class, 'confirmarPendientes']);
        Route::get('/socios/{id}/pago-mensual', [SocioController::class, 'createPagoMensual']);
        Route::post('/socios/{id}/pago-mensual', [SocioController::class, 'storePagoMensual']);
        Route::get('/socios/{id}/historial', [SocioController::class, 'historial']);
        
        // Confirmar pago de cuota (QR pendiente)
        Route::post('/socios/confirmar-pago/{id_cuota}', [SocioController::class, 'confirmarPago']);
        
        // --- Pagos a Socios (Liquidaciones) ---
        Route::get('/liquidaciones/crear/{id_usuario_socio}', [LiquidacionController::class, 'create']);
        Route::post('/liquidaciones/crear/{id_usuario_socio}', [LiquidacionController::class, 'store']);
        Route::post('/liquidaciones/{id}/anular', [LiquidacionController::class, 'anular']);

        // --- Reportes ---
        Route::get('/reportes', [ReporteController::class, 'index']);
        Route::get('/reportes/pdf', [ReporteController::class, 'exportPdf']);
        Route::get('/reportes/excel', [ReporteController::class, 'exportCsv']);
    });

});