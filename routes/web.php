<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// rutas de autenticación

// Rutas públicas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirección raíz
Route::get('/', function () {
    return redirect('/login');
});

// Panel protegido exclusívamente para PROFESORES
Route::middleware(['auth', \App\Http\Middleware\CheckRol::class . ':profesor'])->group(function () {
    Route::get('/profesor/dashboard', function () {
        return view('profesor.dashboard');
    });
});

// Panel protegido exclusívamente para ALUMNOS
Route::middleware(['auth', \App\Http\Middleware\CheckRol::class . ':alumno'])->group(function () {
    Route::get('/alumno/dashboard', function () {
        return view('alumno.dashboard');
    });
});