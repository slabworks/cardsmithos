<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function create(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        return Inertia::render('payments/create', [
            'customer' => $customer,
            'methodOptions' => array_map(
                fn (PaymentMethod $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                PaymentMethod::cases()
            ),
        ]);
    }

    public function store(StorePaymentRequest $request, Customer $customer): RedirectResponse
    {
        $customer->payments()->create($request->validated());

        return to_route('customers.show', $customer);
    }

    public function edit(Customer $customer, Payment $payment): Response
    {
        $this->authorize('update', $payment);

        return Inertia::render('payments/edit', [
            'customer' => $customer,
            'payment' => $payment,
            'methodOptions' => array_map(
                fn (PaymentMethod $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                PaymentMethod::cases()
            ),
        ]);
    }

    public function update(UpdatePaymentRequest $request, Customer $customer, Payment $payment): RedirectResponse
    {
        $payment->update($request->validated());

        return to_route('customers.show', $customer);
    }

    public function destroy(Customer $customer, Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $payment->delete();

        return to_route('customers.show', $customer);
    }
}
