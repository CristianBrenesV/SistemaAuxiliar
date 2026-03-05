<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/principal', function () {
    if (!session('user_id')) return redirect('/login');
    return view('principal');
});
Route::get('/', function () {
    return view('welcome');
});
