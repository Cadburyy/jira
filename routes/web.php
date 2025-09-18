<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DandoryController;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/home/dandories/data', [HomeController::class, 'getDandoriTicketsData'])->name('home.dandories.data');
Route::get('/home/charts-data', [HomeController::class, 'getChartData'])->name('home.charts.data');

Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('dandories', DandoryController::class);

    Route::put('dandories/{dandory}/status', [DandoryController::class, 'updateStatus'])->name('dandories.updateStatus');
    Route::put('dandories/{dandory}/planning', [DandoryController::class, 'updatePlanning'])->name('dandories.updatePlanning');
    Route::put('dandories/{dandory}/assign', [DandoryController::class, 'assign'])->name('dandories.assign');
    Route::put('dandories/{dandory}/update-notes', [DandoryController::class, 'updateNotes'])->name('dandories.updateNotes');

    Route::get('dandories/download/{type}', [DandoryController::class, 'download'])->name('dandories.download');
});

Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    Route::get('/settings/appearance', [SettingsController::class, 'editAppearance'])->name('settings.appearance');
    Route::put('/settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.appearance.update');
});
