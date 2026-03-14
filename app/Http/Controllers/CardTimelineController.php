<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CardTimelineController extends Controller
{
    public function show(Card $card, string $token): Response|RedirectResponse
    {
        if ($card->timeline_share_token === null || ! hash_equals($card->timeline_share_token, $token)) {
            abort(404);
        }

        $card->load(['customer', 'activities' => fn ($q) => $q->orderByDesc('occurred_at')]);
        $card->makeHidden('timeline_share_token');

        return Inertia::render('cards/timeline-public', [
            'card' => $card,
        ]);
    }

    public function rotateToken(Customer $customer, Card $card): RedirectResponse
    {
        $this->authorize('update', $card);

        $card->rotateTimelineShareToken();

        return redirect()->route('customers.cards.edit', [$customer, $card])
            ->with('success', 'Timeline link has been reset. Previous link no longer works.');
    }
}
