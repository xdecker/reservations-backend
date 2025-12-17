<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/ping', function () {
    return 'pong';
});

Route::post('/login', [AuthController::class,'login']);
Route::post('/register', [AuthController::class,'register']);

//rutas autenticadas
Route::middleware('auth:api')->group(function () {
    Route::get('/profile',[AuthController::class,'profile']);
});