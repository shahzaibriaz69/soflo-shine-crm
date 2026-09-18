<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GHLAuthController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\RequestQuoteController;
use App\Http\Controllers\CustomerMessageController;
use App\Http\Controllers\GhlSyncController;
use App\Http\Controllers\EstimatorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root Route: Redirects directly to login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Authenticated CRM Dashboard Route (Breeze default /dashboard replaced with your DashboardController)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authenticated Group for CRM & Profile Management
Route::middleware(['auth'])->group(function () {
    
    // Profile Routes (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Services & Packages Resource Routes
    Route::resource('services', ServiceController::class);
    Route::resource('packages', PackageController::class);

    // GHL Auth Routes
    Route::get('/ghl/connect', [GHLAuthController::class, 'redirectToGHL'])->name('ghl.connect');
    Route::get('/ghl/callback', [GHLAuthController::class, 'handleCallback'])->name('ghl.callback');

    // Pipeline Routes
    Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
    Route::get('/pipeline/view', [PipelineController::class, 'index'])->name('pipeline');
    Route::post('/pipeline/update-stage', [PipelineController::class, 'updateStage'])->name('pipeline.update-stage');

    // RequestQuote Routes
    Route::get('/requests', [RequestQuoteController::class, 'index'])->name('requests.index');
    Route::post('/requests/{id}/build-quote', [RequestQuoteController::class, 'buildQuote'])->name('requests.build-quote');
    Route::delete('/requests/{id}/decline', [RequestQuoteController::class, 'decline'])->name('requests.decline');

    // Customer Message & SMS Routes
    Route::post('/customer/send-text', [CustomerMessageController::class, 'store'])->name('customer.send-text');

    // GHL Sync Route
    Route::get('/ghl/sync', [GhlSyncController::class, 'sync'])->name('ghl.sync');

});

// Protected Super Admin Routes (Estimator & Quotes)
Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    Route::get('/estimator', [EstimatorController::class, 'index'])->name('estimator.index');
    Route::post('/estimator/calculate', [EstimatorController::class, 'calculate'])->name('estimator.calculate');
    Route::post('/estimator/store', [EstimatorController::class, 'store'])->name('estimator.store');
    Route::get('/quotes', [EstimatorController::class, 'quotes'])->name('quotes.index');
});

// GHL Webhook Listener Route (Public API endpoint)
Route::post('/ghl/webhook', [GhlSyncController::class, 'handleWebhook'])->name('ghl.webhook');

// Require Breeze Auth Routes (Login, Register, Password Reset, etc.)
require __DIR__.'/auth.php';