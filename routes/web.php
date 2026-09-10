<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GHLAuthController;
use App\Http\Controllers\PipelineController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Services aur Packages Resource Routes
Route::resource('services', ServiceController::class);
Route::resource('packages', PackageController::class);
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/ghl/connect', [GHLAuthController::class, 'redirectToGHL'])->name('ghl.connect');
Route::get('/ghl/callback', [GHLAuthController::class, 'handleCallback'])->name('ghl.callback');
Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
Route::post('/pipeline/update-stage', [PipelineController::class, 'updateStage'])->name('pipeline.updateStage');