<?php

namespace App\Http\Controllers;

use App\Models\ServiceWaiver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceWaiverController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ServiceWaiver::class);

        $waivers = $request->user()
            ->serviceWaivers()
            ->latest()
            ->get()
            ->map(fn (ServiceWaiver $waiver) => [
                'id' => $waiver->id,
                'expires_at' => $waiver->expires_at,
                'signed_at' => $waiver->signed_at,
                'signer_name' => $waiver->signer_name,
                'signer_email' => $waiver->signer_email,
                'signed_url' => $waiver->signedUrl(),
            ]);

        return Inertia::render('waivers/index', [
            'waivers' => $waivers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ServiceWaiver::class);

        $request->user()->serviceWaivers()->create([
            'expires_at' => now()->addDays(config('cardsmithos.waiver.expiration_days', 30)),
        ]);

        return to_route('waivers.index');
    }

    public function destroy(ServiceWaiver $waiver): RedirectResponse
    {
        $this->authorize('delete', $waiver);

        $waiver->delete();

        return to_route('waivers.index');
    }
}
