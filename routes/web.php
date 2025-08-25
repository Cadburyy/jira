<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DandoryController;

// Redirect root URL to /home if logged in, or to login otherwise
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

// Auth routes (login, register, forgot password, etc.)
Auth::routes();

// Home page after login
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Protected routes (only accessible when logged in)
Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('dandories', DandoryController::class);
    
    // Add new routes for specific actions that return JSON
    Route::put('dandories/{dandory}/status', [DandoryController::class, 'updateStatus'])->name('dandories.updateStatus');
    Route::put('dandories/{dandory}/planning', [DandoryController::class, 'updatePlanning'])->name('dandories.updatePlanning');
    Route::put('dandories/{dandory}/assign', [DandoryController::class, 'assign'])->name('dandories.assign');
});
