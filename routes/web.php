<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Usuarios\UserController;

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
    Route::post('/usuarios/actualizar/{id}', [UserController::class, 'actualizar'])->name('usuarios.actualizar');
    Route::post('/usuarios/eliminar/{id}', [UserController::class, 'eliminar'])->name('usuarios.eliminar');
    Route::post('/usuarios/cambiar-estado', [UserController::class, 'cambiarEstadoUsuario'])->name('usuarios.cambiarEstado');
    Route::post('/usuarios/cambiar-clave', [UserController::class, 'cambiarClaveUsuario'])->name('usuarios.cambiarClave');
});

// ================= PÁGINA INICIAL =================
Route::get('/', function () {
    return view('welcome');
});
