<?php

namespace App\Http\Controllers;

use App\Enums\CardCondition;
use App\Enums\CardStatus;
use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\Card;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CardController extends Controller
{
    public function create(Submission $submission): Response
    {
        $this->authorize('update', $submission);

        $submission->load('customer:id,name');

        return Inertia::render('cards/create', [
            'submission' => $submission,
            'statusOptions' => array_map(
                fn (CardStatus $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                CardStatus::cases()
            ),
            'conditionOptions' => array_map(
                fn (CardCondition $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ],
                CardCondition::cases()
            ),
        ]);
    }

    public function store(StoreCardRequest $request, Submission $submission): RedirectResponse
    {
        $submission->cards()->create($request->validated());

        return to_route('submissions.show', $submission);
    }

    public function edit(Submission $submission, Card $card): Response
    {
        $this->authorize('update', $card);

        $submission->load('customer:id,name');
        $settings = auth()->user()?->businessSettings;
        $hourlyRate = $settings?->hourly_rate;
        $taxRate = $settings?->tax_rate;

        $photos = $card->getMedia('photos')->map(fn ($media) => [
            'id' => $media->id,
            'url' => route('submissions.cards.photos.show', [
                'submission' => $submission,
                'card' => $card,
                'media' => $media->id,
            ]),
            'name' => $media->file_name,
        ])->values()->all();

        return Inertia::render('cards/edit', [
            'submission' => $submission,
            'card' => $card,
            'photos' => $photos,
            'hourlyRate' => $hourlyRate !== null ? (float) $hourlyRate : null,
            'taxRate' => $taxRate !== null ? (float) $taxRate : null,
            'statusOptions' => array_map(
                fn (CardStatus $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                CardStatus::cases()
            ),
            'conditionOptions' => array_map(
                fn (CardCondition $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ],
                CardCondition::cases()
            ),
        ]);
    }

    public function update(UpdateCardRequest $request, Submission $submission, Card $card): RedirectResponse
    {
        $card->update($request->validated());

        return to_route('submissions.show', $submission);
    }

    public function destroy(Submission $submission, Card $card): RedirectResponse
    {
        $this->authorize('delete', $card);

        $card->delete();

        return to_route('submissions.show', $submission);
    }
}
