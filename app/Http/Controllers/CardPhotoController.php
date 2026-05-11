<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCardPhotoRequest;
use App\Models\Card;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CardPhotoController extends Controller
{
    public function store(StoreCardPhotoRequest $request, Submission $submission, Card $card): RedirectResponse
    {
        foreach ($request->file('photos') as $file) {
            $card->addMedia($file)->toMediaCollection('photos');
        }

        return back();
    }

    public function show(Submission $submission, Card $card, int $media): StreamedResponse
    {
        $this->authorize('view', $card);

        $mediaItem = $card->getMedia('photos')->firstWhere('id', $media);

        abort_unless($mediaItem, 404);

        return $mediaItem->toInlineResponse(request());
    }

    public function destroy(Submission $submission, Card $card, int $media): RedirectResponse
    {
        $this->authorize('update', $card);

        $mediaItem = $card->getMedia('photos')->firstWhere('id', $media);

        abort_unless($mediaItem, 404);

        $mediaItem->delete();

        return back();
    }
}
