<?php

namespace App\Http\Controllers;

use App\Enums\CardActivityType;
use App\Enums\CardCondition;
use App\Enums\CardStatus;
use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\Card;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class CardController extends Controller
{
    public function create(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        return Inertia::render('cards/create', [
            'customer' => $customer,
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
                    'color' => $case->color(),
                ],
                CardCondition::cases()
            ),
        ]);
    }

    public function store(StoreCardRequest $request, Customer $customer): RedirectResponse
    {
        $customer->cards()->create($request->validated());

        return to_route('customers.show', $customer);
    }

    public function edit(Customer $customer, Card $card): Response
    {
        $this->authorize('update', $card);

        $card->load('activities');
        $settings = auth()->user()?->businessSettings;
        $hourlyRate = $settings?->hourly_rate;
        $taxRate = $settings?->tax_rate;
        $timelineShareToken = $card->ensureTimelineShareToken();
        $timelineShareUrl = URL::route('card.timeline.show', ['card' => $card, 'token' => $timelineShareToken]);

        return Inertia::render('cards/edit', [
            'customer' => $customer,
            'card' => $card,
            'hourlyRate' => $hourlyRate !== null ? (float) $hourlyRate : null,
            'taxRate' => $taxRate !== null ? (float) $taxRate : null,
            'timelineShareUrl' => $timelineShareUrl,
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
                    'color' => $case->color(),
                ],
                CardCondition::cases()
            ),
            'activityTypeOptions' => array_map(
                fn (CardActivityType $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ],
                CardActivityType::cases()
            ),
        ]);
    }

    public function update(UpdateCardRequest $request, Customer $customer, Card $card): RedirectResponse
    {
        $card->update($request->validated());

        return to_route('customers.show', $customer);
    }

    public function destroy(Customer $customer, Card $card): RedirectResponse
    {
        $this->authorize('delete', $card);

        $card->delete();

        return to_route('customers.show', $customer);
    }
}
