<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\ReservationController;


Route::post('/login', [AuthController::class,'login']);
Route::post('/register', [AuthController::class,'register']);

//rutas autenticadas
Route::middleware('auth:api')->group(function () {
    Route::get('/profile',[AuthController::class,'profile']);

    //spaces
    Route::get('/spaces', [SpaceController::class, 'getAll']);
    Route::get('/spaces/{id}', [SpaceController::class, 'get']);
    
    //spaces - admin
    Route::middleware('admin')->group(function () {
        Route::post('/spaces', [SpaceController::class, 'create']);
        Route::put('/spaces/{id}', [SpaceController::class, 'update']);
        Route::delete('/spaces/{id}', [SpaceController::class, 'delete']);
    });

    //reservations
    Route::get('/reservations', [ReservationController::class, 'getAll']);
    Route::post('/reservations',[ReservationController::class,'create']);
    Route::get('/reservations/{id}',[ReservationController::class,'get']);
    Route::put('/reservations/{id}',[ReservationController::class,'update']);
    Route::delete('/reservations/{id}',[ReservationController::class,'delete']);
});