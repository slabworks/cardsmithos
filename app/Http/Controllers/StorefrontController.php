<?php

namespace App\Http\Controllers;

use App\Models\BusinessSettings;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function show(string $slug): Response
    {
        $settings = BusinessSettings::where('store_slug', $slug)->firstOrFail();

        return Inertia::render('storefront/show', [
            'companyName' => $settings->company_name,
            'hourlyRate' => $settings->hourly_rate,
            'fixedRate' => $settings->default_fixed_rate,
            'currency' => $settings->currency,
            'bio' => $settings->bio,
            'instagramHandle' => $settings->instagram_handle,
            'tiktokHandle' => $settings->tiktok_handle,
        ]);
    }
}
