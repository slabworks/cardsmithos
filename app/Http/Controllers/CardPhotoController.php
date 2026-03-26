<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CardPhotoController extends Controller
{
    public function store(Request $request, Customer $customer, Card $card): RedirectResponse
    {
        $this->authorize('update', $card);

        $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:10'],
            'photos.*' => ['required', 'image', 'max:10240'],
        ]);

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

    public function destroy(Customer $customer, Card $card, int $media): RedirectResponse
    {
        $this->authorize('update', $card);

        $mediaItem = $card->getMedia('photos')->firstWhere('id', $media);

        abort_unless($mediaItem, 404);

        $mediaItem->delete();

        return back();
    }
}
