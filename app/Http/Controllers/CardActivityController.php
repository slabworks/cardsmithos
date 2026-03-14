<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCardActivityRequest;
use App\Http\Requests\UpdateCardActivityRequest;
use App\Models\Card;
use App\Models\CardActivity;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class CardActivityController extends Controller
{
    public function store(StoreCardActivityRequest $request, Customer $customer, Card $card): RedirectResponse
    {
        $card->activities()->create($request->validated());

        return redirect()->route('customers.cards.edit', [$customer, $card])
            ->with('success', 'Timeline entry added.');
    }

    public function update(UpdateCardActivityRequest $request, Customer $customer, Card $card, CardActivity $activity): RedirectResponse
    {
        $activity->update($request->validated());

        return redirect()->route('customers.cards.edit', [$customer, $card])
            ->with('success', 'Timeline entry updated.');
    }

    public function destroy(Customer $customer, Card $card, CardActivity $activity): RedirectResponse
    {
        $this->authorize('update', $card);

        $activity->delete();

        return redirect()->route('customers.cards.edit', [$customer, $card])
            ->with('success', 'Timeline entry removed.');
    }
}
