<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateBusinessSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusinessSettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $settings = $request->user()->businessSettings()->firstOrCreate([], [
            'hourly_rate' => 0,
            'currency' => 'USD',
        ]);

        return Inertia::render('settings/business', [
            'businessSettings' => $settings,
        ]);
    }

    public function update(UpdateBusinessSettingsRequest $request): RedirectResponse
    {
        $settings = $request->user()->businessSettings()->firstOrCreate([], [
            'hourly_rate' => 0,
            'currency' => 'USD',
        ]);

        $this->authorize('update', $settings);

        $settings->update($request->validated());

        return to_route('business.edit');
    }
}
