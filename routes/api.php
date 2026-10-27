<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controlador;
use App\Http\Controllers\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/', [Controlador::class, 'addUsuario']);
    Route::get('/{id}', [Controlador::class, 'getUsuario']);
    Route::delete('/{id}', [Controlador::class, 'deleteUsuario']);
    Route::delete('/partidas/{id}', [Controlador::class, 'deletePartidaUsuario']);
});

Route::prefix('user')->middleware(['auth:sanctum', 'user'])->group(function () {
    Route::post('/', [Controlador::class, 'jugar']);
    Route::post('/partida', [Controlador::class, 'crearPartida']);
    Route::get('/partidas/{id}', [Controlador::class, 'getPartidas']);
    Route::post('/tirada/{id}', [Controlador::class, 'getTiradasPartidaUsuario']);
    Route::get('/ranking', [Controlador::class, 'getRanking']);
});

Route::post('/login', [AuthController::class, 'login']);

Route::post('/recuperar-password', [Controlador::class, 'recuperarPassword']);