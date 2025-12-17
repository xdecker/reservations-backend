<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpaceController;


Route::post('/login', [AuthController::class,'login']);
Route::post('/register', [AuthController::class,'register']);

//rutas autenticadas
Route::middleware('auth:api')->group(function () {
    Route::get('/profile',[AuthController::class,'profile']);

    //spaces
    Route::get('/spaces', [SpaceController::class, 'getAll']);
    Route::post('/spaces', [SpaceController::class, 'create']);
    Route::get('/spaces/{space}', [SpaceController::class, 'get']);
    Route::put('/spaces/{space}', [SpaceController::class, 'update']);
    Route::delete('/spaces/{space}', [SpaceController::class, 'delete']);
});