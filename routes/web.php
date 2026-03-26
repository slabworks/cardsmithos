<?php

use App\Http\Controllers\CardActivityController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CardPhotoController;
use App\Http\Controllers\CardTimelineController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PricingCalculatorController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\WaiverController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/cards/{card}/timeline/{token}', [CardTimelineController::class, 'show'])
    ->name('card.timeline.show');
Route::get('/cards/{card}/timeline/{token}/photos/{media}', [CardTimelineController::class, 'photo'])
    ->name('card.timeline.photo');

Route::middleware(['signed:relative'])->group(function (): void {
    Route::get('/waiver/{customer}', [WaiverController::class, 'show'])->name('waiver.show');
    Route::post('/waiver/{customer}', [WaiverController::class, 'sign'])->name('waiver.sign');
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
    Route::resource('expenses', ExpenseController::class);
    Route::resource('inquiries', InquiryController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('customers.cards', CardController::class)->except(['index', 'show'])->scoped();
    Route::post('customers/{customer}/cards/{card}/timeline/rotate-token', [CardTimelineController::class, 'rotateToken'])
        ->name('customers.cards.timeline.rotate-token');
    Route::resource('customers.cards.activities', CardActivityController::class)->only(['store', 'update', 'destroy'])->scoped();
    Route::post('customers/{customer}/cards/{card}/photos', [CardPhotoController::class, 'store'])->name('customers.cards.photos.store');
    Route::get('customers/{customer}/cards/{card}/photos/{media}', [CardPhotoController::class, 'show'])->name('customers.cards.photos.show');
    Route::post('customers/{customer}/cards/{card}/photos/{media}/toggle-timeline', [CardPhotoController::class, 'toggleTimeline'])->name('customers.cards.photos.toggle-timeline');
    Route::delete('customers/{customer}/cards/{card}/photos/{media}', [CardPhotoController::class, 'destroy'])->name('customers.cards.photos.destroy');
    Route::resource('customers.payments', PaymentController::class)->except(['index', 'show'])->scoped();
    Route::get('customers/{customer}/invoices/create', [InvoiceController::class, 'create'])->name('customers.invoices.create');
    Route::post('customers/{customer}/invoices/download', [InvoiceController::class, 'download'])->name('customers.invoices.download')->middleware('signed:relative');
    Route::post('customers/{customer}/shipments', [ShipmentController::class, 'store'])->name('customers.shipments.store');
});

require __DIR__.'/settings.php';
