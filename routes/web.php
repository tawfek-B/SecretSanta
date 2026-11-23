<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function() {
    return Inertia::render('welcome', ['users' => App\Models\User::all()]);
})->name('home');

Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');