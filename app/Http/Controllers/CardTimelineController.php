<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CardTimelineController extends Controller
{
    public function show(Card $card, string $token): Response|RedirectResponse
    {
        if ($card->timeline_share_token === null || ! hash_equals($card->timeline_share_token, $token)) {
            abort(404);
        }

        $card->load(['customer', 'activities' => fn ($q) => $q->orderByDesc('occurred_at')]);
        $card->makeHidden('timeline_share_token');

        $photos = $card->getMedia('photos')
            ->filter(fn ($media) => $media->getCustomProperty('show_on_timeline', false))
            ->map(fn ($media) => [
                'id' => $media->id,
                'url' => route('card.timeline.photo', ['card' => $card, 'token' => $token, 'media' => $media->id]),
                'name' => $media->file_name,
            ])->values()->all();

        return Inertia::render('cards/timeline-public', [
            'card' => $card,
            'photos' => $photos,
        ]);
    }

    public function photo(Card $card, string $token, int $media): StreamedResponse
    {
        if ($card->timeline_share_token === null || ! hash_equals($card->timeline_share_token, $token)) {
            abort(404);
        }

        $mediaItem = $card->getMedia('photos')->first(
            fn ($m) => $m->id === $media && $m->getCustomProperty('show_on_timeline', false)
        );

        abort_unless($mediaItem, 404);

        return $mediaItem->toInlineResponse(request());
    }

    public function rotateToken(Customer $customer, Card $card): RedirectResponse
    {
        $this->authorize('update', $card);

        $card->rotateTimelineShareToken();

        return redirect()->route('customers.cards.edit', [$customer, $card])
            ->with('success', 'Timeline link has been reset. Previous link no longer works.');
    }
}
