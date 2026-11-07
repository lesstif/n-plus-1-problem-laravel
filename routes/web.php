<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [SessionController::class, 'create'])->name('login');
    Route::post('login', [SessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::post('logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::resource('posts', PostController::class)
    ->only(['index', 'create', 'store', 'show'])
    ->middlewareFor(['create', 'store'], 'auth');

Route::resource('users', UserController::class)
    ->only(['index', 'show']);
