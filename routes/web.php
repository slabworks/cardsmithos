<?php

use App\Http\Controllers\BusinessStatisticController;
use App\Http\Controllers\BusinessStatisticRecordController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CardPhotoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PricingCalculatorController;
use App\Http\Controllers\ServiceWaiverController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\WaiverController;
use App\Http\Middleware\EnsurePhotosEnabled;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['signed:relative'])->group(function (): void {
    Route::get('/waiver/{serviceWaiver}', [WaiverController::class, 'show'])->name('waiver.show');
    Route::post('/waiver/{serviceWaiver}', [WaiverController::class, 'sign'])->name('waiver.sign');
});

Route::get('/sitemap.xml', function () {
    $url = config('app.url');

    return response()->view('sitemap', ['url' => $url], 200, [
        'Content-Type' => 'application/xml',
    ]);
})->name('sitemap');

Route::get('/directory', [StorefrontController::class, 'index'])
    ->name('storefront.index');

Route::get('/c/{slug}', [StorefrontController::class, 'show'])
    ->name('storefront.show')
    ->where('slug', '[a-z0-9](?:[a-z0-9-]*[a-z0-9])?');

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('pricing-calculator', PricingCalculatorController::class)->name('pricing-calculator.index');
    Route::resource('waivers', ServiceWaiverController::class)->only(['index', 'store', 'destroy']);
    Route::resource('statistics', BusinessStatisticController::class)
        ->parameters(['statistics' => 'businessStatistic']);
    Route::post('statistics/{businessStatistic}/records', [BusinessStatisticRecordController::class, 'store'])->name('statistics.records.store');
    Route::delete('statistics/{businessStatistic}/records/{businessStatisticRecord}', [BusinessStatisticRecordController::class, 'destroy'])->name('statistics.records.destroy');
    Route::resource('expenses', ExpenseController::class);
    Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('submissions', SubmissionController::class);
    Route::resource('submissions.cards', CardController::class)->except(['index', 'show'])->scoped();
    Route::resource('submissions.payments', PaymentController::class)->only(['store', 'update', 'destroy'])->scoped();
    Route::resource('submissions.shipments', ShipmentController::class)->only(['store', 'update', 'destroy'])->scoped();
    Route::middleware(EnsurePhotosEnabled::class)->group(function (): void {
        Route::post('submissions/{submission}/cards/{card}/photos', [CardPhotoController::class, 'store'])->name('submissions.cards.photos.store');
        Route::get('submissions/{submission}/cards/{card}/photos/{media}', [CardPhotoController::class, 'show'])->name('submissions.cards.photos.show');
        Route::delete('submissions/{submission}/cards/{card}/photos/{media}', [CardPhotoController::class, 'destroy'])->name('submissions.cards.photos.destroy');
    });
    Route::get('submissions/{submission}/invoices/create', [InvoiceController::class, 'create'])->name('submissions.invoices.create');
    Route::post('submissions/{submission}/invoices/download', [InvoiceController::class, 'download'])->name('submissions.invoices.download')->middleware('signed:relative');
});

require __DIR__.'/settings.php';
