<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->group(function () {
    // Rota para obter o usuário autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Rota para registro de usuário
Route::post('register', [AuthController::class, 'register']);

Route::resource('contacts', ContactController::class);

Route::post('/login', 'App\Http\Controllers\AuthController@login');