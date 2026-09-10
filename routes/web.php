<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GHLAuthController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestQuoteController;

// Dashboard Route
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Services aur Packages Resource Routes
Route::resource('services', ServiceController::class);
Route::resource('packages', PackageController::class);

// GHL Auth Routes
Route::get('/ghl/connect', [GHLAuthController::class, 'redirectToGHL'])->name('ghl.connect');
Route::get('/ghl/callback', [GHLAuthController::class, 'handleCallback'])->name('ghl.callback');

// Pipeline Routes (Supporting both pipeline and pipeline.index names)
Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
Route::get('/pipeline/view', [PipelineController::class, 'index'])->name('pipeline');
Route::post('/pipeline/update-stage', [PipelineController::class, 'updateStage'])->name('pipeline.update-stage');

// RequestQuote Routes
Route::get('/requests', [RequestQuoteController::class, 'index'])->name('requests.index');
Route::post('/requests/{id}/build-quote', [RequestQuoteController::class, 'buildQuote'])->name('requests.build-quote');
Route::delete('/requests/{id}/decline', [RequestQuoteController::class, 'decline'])->name('requests.decline');