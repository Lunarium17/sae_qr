<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QrController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:api')->group(function () {
    Route::get('/qr/generar', [QrController::class, 'generarTokenQR']); // alumno solicita token dinamico
    Route::post('/qr/validar', [QrController::class, 'validarEscaneo']); // profesor envia el token para validarlo
});
