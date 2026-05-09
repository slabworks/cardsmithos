<?php

use App\Http\Controllers\Settings\BusinessSettingsController;
use App\Http\Controllers\Settings\GmailIntegrationController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings/business', [BusinessSettingsController::class, 'edit'])->name('business.edit');
    Route::patch('settings/business', [BusinessSettingsController::class, 'update'])->name('business.update');

    Route::get('settings/integrations', [GmailIntegrationController::class, 'edit'])->name('integrations.edit');
    Route::get('settings/integrations/gmail/redirect', [GmailIntegrationController::class, 'redirect'])->name('integrations.gmail.redirect');
    Route::get('settings/integrations/gmail/callback', [GmailIntegrationController::class, 'callback'])->name('integrations.gmail.callback');
    Route::delete('settings/integrations/gmail', [GmailIntegrationController::class, 'destroy'])->name('integrations.gmail.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');
});
