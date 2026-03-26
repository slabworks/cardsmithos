<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCardPhotoRequest;
use App\Models\Card;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CardPhotoController extends Controller
{
    public function store(StoreCardPhotoRequest $request, Customer $customer, Card $card): RedirectResponse
    {
        foreach ($request->file('photos') as $file) {
            $card->addMedia($file)->toMediaCollection('photos');
        }

        return back();
    }

    public function show(Customer $customer, Card $card, int $media): StreamedResponse
    {
        $this->authorize('view', $card);

        $mediaItem = $card->getMedia('photos')->firstWhere('id', $media);

        abort_unless($mediaItem, 404);

        return $mediaItem->toInlineResponse(request());
    }

    public function toggleTimeline(Customer $customer, Card $card, int $media): RedirectResponse
    {
        $this->authorize('update', $card);

        $mediaItem = $card->getMedia('photos')->firstWhere('id', $media);

        abort_unless($mediaItem, 404);

        $mediaItem->setCustomProperty(
            'show_on_timeline',
            ! $mediaItem->getCustomProperty('show_on_timeline', false)
        );
        $mediaItem->save();

        return back();
    }

    public function destroy(Customer $customer, Card $card, int $media): RedirectResponse
    {
        $this->authorize('update', $card);

        $mediaItem = $card->getMedia('photos')->firstWhere('id', $media);

        abort_unless($mediaItem, 404);

        $mediaItem->delete();

        return back();
    }
}
