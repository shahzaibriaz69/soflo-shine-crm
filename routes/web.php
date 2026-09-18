<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GHLAuthController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\RequestQuoteController;
use App\Http\Controllers\CustomerMessageController;
use App\Http\Controllers\GhlSyncController;
use App\Http\Controllers\EstimatorController;
use App\Http\Controllers\GhlWebhookController;
use App\Http\Controllers\TeamController;
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

// Authenticated CRM Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authenticated Group for CRM & Profile Management
Route::middleware(['auth'])->group(function () {

    // Profile Routes (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

// Estimator, Services, Packages & Quotes Routes (Middleware updated to 'auth' to fix 403 error)
Route::middleware(['auth'])->group(function () {
    // Estimator & Quotes
    Route::get('/estimator', [EstimatorController::class, 'index'])->name('estimator.index');
    Route::post('/estimator/calculate', [EstimatorController::class, 'calculate'])->name('estimator.calculate');
    Route::post('/estimator/store', [EstimatorController::class, 'store'])->name('estimator.store');
    Route::get('/quotes', [EstimatorController::class, 'quotes'])->name('quotes.index');

    // Services Management Routes
    Route::get('/services', [EstimatorController::class, 'servicesIndex'])->name('services.index');
    Route::post('/services', [EstimatorController::class, 'serviceStore'])->name('services.store');

    // Packages Management Routes
    Route::get('/packages', [EstimatorController::class, 'packagesIndex'])->name('packages.index');
    Route::post('/packages', [EstimatorController::class, 'packageStore'])->name('packages.store');
});

// GHL Webhook Listener Route (Public API endpoint)
Route::post('/ghl/webhook', [GhlWebhookController::class, 'handle'])
    ->name('ghl.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Team / Staff Management Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::get('/team/create', [TeamController::class, 'create'])->name('team.create');
    Route::post('/team', [TeamController::class, 'store'])->name('team.store');
    Route::get('/team/{id}/edit', [TeamController::class, 'edit'])->name('team.edit');
    Route::put('/team/{id}', [TeamController::class, 'update'])->name('team.update');
    Route::delete('/team/{id}', [TeamController::class, 'destroy'])->name('team.destroy');
});

// Require Breeze Auth Routes (Login, Register, Password Reset, etc.)
require __DIR__ . '/auth.php';