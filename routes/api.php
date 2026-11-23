<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\AdminController;


Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');


Route::middleware(['auth:sanctum', 'active'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Regular user endpoints.
    Route::get('/app-state', [GameController::class, 'state']);

    Route::get('/my-result', [GameController::class, 'myResult']);

    Route::post('/spin', [GameController::class, 'spin']);


    // Administrator endpoints.
    Route::middleware('admin')->prefix('admin')->group(function () {

        Route::patch('/spinning', [AdminController::class, 'updateSpinning']);

        Route::get('/spins', [AdminController::class, 'spins']);

        Route::get('/users', [AdminController::class, 'users']);

    });

});