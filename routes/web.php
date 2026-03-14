<?php

use App\Http\Controllers\CardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WaiverController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['signed:relative'])->group(function (): void {
    Route::get('/waiver/{customer}', [WaiverController::class, 'show'])->name('waiver.show');
    Route::post('/waiver/{customer}', [WaiverController::class, 'sign'])->name('waiver.sign');
});

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::resource('customers', CustomerController::class);
    Route::resource('customers.cards', CardController::class)->except(['index', 'show'])->scoped();
    Route::resource('customers.payments', PaymentController::class)->except(['index', 'show'])->scoped();
});

require __DIR__.'/settings.php';
