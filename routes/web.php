<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Usuarios\UserController;
use App\Http\Controllers\Reportes\ReporteMovimientosController;

// ================= LOGIN / LOGOUT =================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ================= PÁGINA PRINCIPAL =================
Route::get('/principal', function () {
    if (!session('user_id')) return redirect('/login');
    return view('principal');
})->name('principal');

// ================= USUARIOS =================
Route::middleware('web')->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UserController::class, 'crear'])->name('usuarios.crear');
    Route::post('/usuarios/guardar', [UserController::class, 'guardar'])->name('usuarios.guardar');
    Route::get('/usuarios/editar/{id}', [UserController::class, 'editar'])->name('usuarios.editar');
    Route::put('/usuarios/actualizar/{id}', [UserController::class, 'actualizar'])->name('usuarios.actualizar');
    Route::delete('/usuarios/eliminar/{id}', [UserController::class, 'eliminar'])->name('usuarios.eliminar');
    Route::post('/usuarios/cambiar-estado', [UserController::class, 'cambiarEstadoUsuario'])->name('usuarios.cambiarEstado');
    Route::post('/usuarios/cambiar-clave', [UserController::class, 'cambiarClaveUsuario'])->name('usuarios.cambiarClave');
});

// ================= PRORRATEO =================
use App\Http\Controllers\Prorrateo\ProrrateoController;

Route::middleware('web')->group(function () {
    Route::get('/prorrateo', [ProrrateoController::class, 'index'])->name('asientos.index');

    Route::get('/asientos/{id}/detalles', [ProrrateoController::class, 'obtenerDetalles'])->name('asientos.detalles');
    Route::get('/prorrateo/terceros/{idDetalle}', [ProrrateoController::class, 'prorratearTerceros'])->name('prorrateo.terceros');
    Route::get('/prorrateo/costos/{idDetalle}', [ProrrateoController::class, 'prorratearCostos'])->name('prorrateo.costos');
    Route::post('/prorrateo/guardar', [ProrrateoController::class, 'guardarProrrateo'])->name('prorrateo.guardar');
});

// ================= REPORTES =================
Route::middleware('web')->group(function () {
    Route::get('/reportes/terceros', [ReporteMovimientosController::class, 'reporteTerceros'])->name('reportes.terceros');
    Route::get('/reportes/centros', [ReporteMovimientosController::class, 'reporteCentros'])->name('reportes.centros');
});

// ================= PÁGINA INICIAL =================
Route::get('/', function () {
    return redirect()->route('login');
});
