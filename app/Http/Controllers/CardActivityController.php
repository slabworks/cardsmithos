<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCardActivityRequest;
use App\Http\Requests\UpdateCardActivityRequest;
use App\Models\Card;
use App\Models\CardActivity;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;

class CardActivityController extends Controller
{
    public function store(StoreCardActivityRequest $request, Submission $submission, Card $card): RedirectResponse
    {
        $card->activities()->create($request->validated());

        return redirect()->route('submissions.cards.edit', [$submission, $card])
            ->with('success', 'Timeline entry added.');
    }

    public function update(UpdateCardActivityRequest $request, Submission $submission, Card $card, CardActivity $activity): RedirectResponse
    {
        $activity->update($request->validated());

        return redirect()->route('submissions.cards.edit', [$submission, $card])
            ->with('success', 'Timeline entry updated.');
    }

    public function destroy(Submission $submission, Card $card, CardActivity $activity): RedirectResponse
    {
        $this->authorize('update', $card);

        $activity->delete();

        return redirect()->route('submissions.cards.edit', [$submission, $card])
            ->with('success', 'Timeline entry removed.');
    }
}
