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

// ================= TERCEROS (AUX5) =================
Route::middleware('web')->group(function () {
    Route::get('/terceros', [App\Http\Controllers\TerceroController::class, 'index'])->name('terceros.index');
    Route::get('/terceros/crear', [App\Http\Controllers\TerceroController::class, 'crear'])->name('terceros.crear');
    Route::post('/terceros/guardar', [App\Http\Controllers\TerceroController::class, 'guardar'])->name('terceros.guardar');
    Route::get('/terceros/editar/{id}', [App\Http\Controllers\TerceroController::class, 'editar'])->name('terceros.editar');
    Route::put('/terceros/actualizar/{id}', [App\Http\Controllers\TerceroController::class, 'actualizar'])->name('terceros.actualizar');
    Route::delete('/terceros/eliminar/{id}', [App\Http\Controllers\TerceroController::class, 'eliminar'])->name('terceros.eliminar');

    // ================= DIRECCIONES DE TERCEROS (AUX11) =================
    Route::get('/terceros/{idTercero}/direcciones', [App\Http\Controllers\TerceroDireccionController::class, 'index'])->name('terceros.direcciones.index');
    Route::get('/terceros/{idTercero}/direcciones/crear', [App\Http\Controllers\TerceroDireccionController::class, 'crear'])->name('terceros.direcciones.crear');
    Route::post('/terceros/{idTercero}/direcciones/guardar', [App\Http\Controllers\TerceroDireccionController::class, 'guardar'])->name('terceros.direcciones.guardar');
    Route::get('/terceros/{idTercero}/direcciones/editar/{idDireccion}', [App\Http\Controllers\TerceroDireccionController::class, 'editar'])->name('terceros.direcciones.editar');
    Route::put('/terceros/{idTercero}/direcciones/actualizar/{idDireccion}', [App\Http\Controllers\TerceroDireccionController::class, 'actualizar'])->name('terceros.direcciones.actualizar');
    Route::delete('/terceros/{idTercero}/direcciones/eliminar/{idDireccion}', [App\Http\Controllers\TerceroDireccionController::class, 'eliminar'])->name('terceros.direcciones.eliminar');

    // ================= CONTACTOS DE TERCEROS (AUX12) =================
    Route::get('/terceros/{idTercero}/contactos', [App\Http\Controllers\TerceroContactoController::class, 'index'])->name('terceros.contactos.index');
    Route::get('/terceros/{idTercero}/contactos/crear', [App\Http\Controllers\TerceroContactoController::class, 'crear'])->name('terceros.contactos.crear');
    Route::post('/terceros/{idTercero}/contactos/guardar', [App\Http\Controllers\TerceroContactoController::class, 'guardar'])->name('terceros.contactos.guardar');
    Route::get('/terceros/{idTercero}/contactos/editar/{idContacto}', [App\Http\Controllers\TerceroContactoController::class, 'editar'])->name('terceros.contactos.editar');
    Route::put('/terceros/{idTercero}/contactos/actualizar/{idContacto}', [App\Http\Controllers\TerceroContactoController::class, 'actualizar'])->name('terceros.contactos.actualizar');
    Route::delete('/terceros/{idTercero}/contactos/eliminar/{idContacto}', [App\Http\Controllers\TerceroContactoController::class, 'eliminar'])->name('terceros.contactos.eliminar');
});

// ================= CENTROS DE COSTO (AUX6) =================
Route::middleware('web')->group(function () {
    Route::get('/centroscosto', [App\Http\Controllers\CentroCostoController::class, 'index'])->name('centroscosto.index');
    Route::get('/centroscosto/crear', [App\Http\Controllers\CentroCostoController::class, 'crear'])->name('centroscosto.crear');
    Route::post('/centroscosto/guardar', [App\Http\Controllers\CentroCostoController::class, 'guardar'])->name('centroscosto.guardar');
    Route::get('/centroscosto/editar/{id}', [App\Http\Controllers\CentroCostoController::class, 'editar'])->name('centroscosto.editar');
    Route::put('/centroscosto/actualizar/{id}', [App\Http\Controllers\CentroCostoController::class, 'actualizar'])->name('centroscosto.actualizar');
    Route::delete('/centroscosto/eliminar/{id}', [App\Http\Controllers\CentroCostoController::class, 'eliminar'])->name('centroscosto.eliminar');
});

// ================= PÁGINA INICIAL =================
Route::get('/', function () {
    return redirect()->route('login');
});
