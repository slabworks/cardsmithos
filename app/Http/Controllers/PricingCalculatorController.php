<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PricingCalculatorController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $settings = $request->user()->businessSettings()->firstOrCreate([], [
            'hourly_rate' => 0,
            'currency' => 'USD',
        ]);

        return Inertia::render('pricing-calculator', [
            'hourlyRate' => $settings->hourly_rate,
            'taxRate' => $settings->tax_rate,
            'currency' => $settings->currency ?? 'USD',
        ]);
    }
}
