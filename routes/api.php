<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'active'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Regular user endpoints.
    Route::get('/app-state', [GameController::class, 'state']);

    Route::get('/my-result', [GameController::class, 'myResult']);

    Route::post('/spin', [GameController::class, 'spin']);

    Route::get('/me', function (Request $request) {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'username' => $request->user()->username,
                'nickname' => $request->user()->nickname,
            ],
        ]);
    });

    Route::get('/users', function() {
        return response()->json([
            'success' => true,
            'users' => \App\Models\User::where('is_active', true)
                ->whereNotIn('id', [auth()->user()->id])
                ->get(['id', 'nickname', 'username']),
        ]);
    });

    // Administrator endpoints.
    Route::middleware('admin')->prefix('admin')->group(function () {

        Route::patch('/spinning', [AdminController::class, 'updateSpinning']);

        Route::get('/spins', [AdminController::class, 'spins']);

        Route::get('/users', [AdminController::class, 'users']);

    });

});
