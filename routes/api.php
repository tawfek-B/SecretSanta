<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::get('/get-usernames', function() {
    return response()->json([
        'success' => true,
        'usernames' => \App\Models\User::where('is_active', true)
            ->get(['username'])
            ->pluck('username'),
    ]);
});

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
                'got_gift' => $request->user()->got_gift,
            ],
        ]);
    });

    Route::put('/toggle', function (Request $request) {
        $user = $request->user();
        $user->got_gift = !$user->got_gift;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User status toggled successfully.',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'got_gift' => $user->got_gift,
            ],
        ]);
    });

    Route::get('/users', function() {
        return response()->json([
            'success' => true,
            'users' => \App\Models\User::where('is_active', true)
                ->whereNotIn('id', [auth()->user()->id])
                ->get(['id', 'nickname', 'username', 'got_gift']),
        ]);
    });

    // Administrator endpoints.
    Route::middleware('admin')->prefix('admin')->group(function () {

        Route::patch('/spinning', [AdminController::class, 'updateSpinning']);

        Route::get('/spins', [AdminController::class, 'spins']);

        Route::get('/users', [AdminController::class, 'users']);

    });

});
