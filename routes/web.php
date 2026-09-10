<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GHLAuthController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Services aur Packages Resource Routes
Route::resource('services', ServiceController::class);
Route::resource('packages', PackageController::class);
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/ghl/connect', [GHLAuthController::class, 'redirectToGHL'])->name('ghl.connect');
Route::get('/ghl/callback', [GHLAuthController::class, 'handleCallback'])->name('ghl.callback');