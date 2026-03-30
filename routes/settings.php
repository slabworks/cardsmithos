<?php

use App\Http\Controllers\Settings\BusinessSettingsController;
use App\Http\Controllers\Settings\GmailConnectionController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings/business', [BusinessSettingsController::class, 'edit'])->name('business.edit');
    Route::patch('settings/business', [BusinessSettingsController::class, 'update'])->name('business.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');

    Route::get('settings/gmail', [GmailConnectionController::class, 'edit'])->name('gmail.edit');
    Route::get('settings/gmail/connect', [GmailConnectionController::class, 'connect'])->name('gmail.connect');
    Route::get('settings/gmail/callback', [GmailConnectionController::class, 'callback'])->name('gmail.callback');
    Route::post('settings/gmail/disconnect', [GmailConnectionController::class, 'disconnect'])->name('gmail.disconnect');
});
