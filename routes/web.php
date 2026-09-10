<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Services aur Packages Resource Routes
Route::resource('services', ServiceController::class);
Route::resource('packages', PackageController::class);
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');