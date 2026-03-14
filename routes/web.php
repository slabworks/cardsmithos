<?php

use App\Http\Controllers\CardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::resource('customers', CustomerController::class);
    Route::resource('customers.cards', CardController::class)->except(['index', 'show'])->scoped();
    Route::resource('customers.payments', PaymentController::class)->except(['index', 'show'])->scoped();
});

require __DIR__.'/settings.php';
