<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Usuarios\UserController;
use App\Http\Controllers\Reportes\ReporteMovimientosController;
use App\Http\Controllers\Prorrateo\ProrrateoController;
use App\Http\Controllers\TerceroController;
use App\Http\Controllers\TerceroDireccionController;
use App\Http\Controllers\TerceroContactoController;
use App\Http\Controllers\CentroCostoController;

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
Route::prefix('usuarios')->name('usuarios.')->middleware('web')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/crear', [UserController::class, 'crear'])->name('crear');
    Route::post('/guardar', [UserController::class, 'guardar'])->name('guardar');
    Route::get('/editar/{id}', [UserController::class, 'editar'])->name('editar');
    Route::put('/actualizar/{id}', [UserController::class, 'actualizar'])->name('actualizar');
    Route::delete('/eliminar/{id}', [UserController::class, 'eliminar'])->name('eliminar');
    Route::post('/cambiar-estado', [UserController::class, 'cambiarEstadoUsuario'])->name('cambiarEstado');
    Route::post('/cambiar-clave', [UserController::class, 'cambiarClaveUsuario'])->name('cambiarClave');
});

// ================= PRORRATEO =================
Route::prefix('prorrateo')->name('prorrateo.')->middleware('web')->group(function () {
    Route::get('/', [ProrrateoController::class, 'index'])->name('index');
    Route::get('/terceros/{idDetalle}', [ProrrateoController::class, 'prorratearTerceros'])->name('terceros');
    Route::get('/costos/{idDetalle}', [ProrrateoController::class, 'prorratearCostos'])->name('costos');
    Route::post('/guardar', [ProrrateoController::class, 'guardarProrrateo'])->name('guardar');
});

// ================= ASIENTOS =================
Route::prefix('asientos')->name('asientos.')->middleware('web')->group(function () {
    Route::get('/', [ProrrateoController::class, 'index'])->name('index');
    Route::get('/{id}/detalles', [ProrrateoController::class, 'obtenerDetalles'])->name('detalles');
});

// ================= REPORTES =================
Route::prefix('reportes')->name('reportes.')->middleware('web')->group(function () {
    Route::get('/terceros', [ReporteMovimientosController::class, 'reporteTerceros'])->name('terceros');
    Route::get('/centros', [ReporteMovimientosController::class, 'reporteCentros'])->name('centros');
});

// ================= TERCEROS (AUX5) =================
Route::prefix('terceros')->name('terceros.')->middleware('web')->group(function () {
    Route::get('/', [TerceroController::class, 'index'])->name('index');
    Route::get('/crear', [TerceroController::class, 'crear'])->name('crear');
    Route::post('/guardar', [TerceroController::class, 'guardar'])->name('guardar');
    Route::get('/editar/{id}', [TerceroController::class, 'editar'])->name('editar');
    Route::put('/actualizar/{id}', [TerceroController::class, 'actualizar'])->name('actualizar');
    Route::delete('/eliminar/{id}', [TerceroController::class, 'eliminar'])->name('eliminar');
});

// ================= DIRECCIONES DE TERCEROS (AUX11) =================
Route::prefix('terceros/{idTercero}/direcciones')->name('terceros.direcciones.')->middleware('web')->group(function () {
    Route::get('/', [TerceroDireccionController::class, 'index'])->name('index');
    Route::get('/crear', [TerceroDireccionController::class, 'crear'])->name('crear');
    Route::post('/guardar', [TerceroDireccionController::class, 'guardar'])->name('guardar');
    Route::get('/editar/{idDireccion}', [TerceroDireccionController::class, 'editar'])->name('editar');
    Route::put('/actualizar/{idDireccion}', [TerceroDireccionController::class, 'actualizar'])->name('actualizar');
    Route::delete('/eliminar/{idDireccion}', [TerceroDireccionController::class, 'eliminar'])->name('eliminar');
});

// ================= CONTACTOS DE TERCEROS (AUX12) =================
Route::prefix('terceros/{idTercero}/contactos')->name('terceros.contactos.')->middleware('web')->group(function () {
    Route::get('/', [TerceroContactoController::class, 'index'])->name('index');
    Route::get('/crear', [TerceroContactoController::class, 'crear'])->name('crear');
    Route::post('/guardar', [TerceroContactoController::class, 'guardar'])->name('guardar');
    Route::get('/editar/{idContacto}', [TerceroContactoController::class, 'editar'])->name('editar');
    Route::put('/actualizar/{idContacto}', [TerceroContactoController::class, 'actualizar'])->name('actualizar');
    Route::delete('/eliminar/{idContacto}', [TerceroContactoController::class, 'eliminar'])->name('eliminar');
});

// ================= CENTROS DE COSTO (AUX6) =================
Route::prefix('centroscosto')->name('centroscosto.')->middleware('web')->group(function () {
    Route::get('/', [CentroCostoController::class, 'index'])->name('index');
    Route::get('/crear', [CentroCostoController::class, 'crear'])->name('crear');
    Route::post('/guardar', [CentroCostoController::class, 'guardar'])->name('guardar');
    Route::get('/editar/{id}', [CentroCostoController::class, 'editar'])->name('editar');
    Route::put('/actualizar/{id}', [CentroCostoController::class, 'actualizar'])->name('actualizar');
    Route::delete('/eliminar/{id}', [CentroCostoController::class, 'eliminar'])->name('eliminar');
});

// ================= PÁGINA INICIAL =================
Route::get('/', function () {
    return redirect()->route('login');
});