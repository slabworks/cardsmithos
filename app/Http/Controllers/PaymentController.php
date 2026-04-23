<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payment::class);

        $customerIds = $request->user()->customers()->pluck('id');

        $payments = Payment::query()
            ->with('customer:id,name')
            ->whereIn('customer_id', $customerIds)
            ->latest('paid_at')
            ->get();

        return Inertia::render('payments/index', [
            'payments' => $payments,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Payment::class);

        return Inertia::render('payments/create', [
            'customers' => $request->user()
                ->customers()
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->toArray(),
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($request->validated()['customer_id']);

        $customer->payments()->create($request->validated());

        return to_route('payments.index');
    }

    public function edit(Request $request, Payment $payment): Response
    {
        $this->authorize('update', $payment);

        return Inertia::render('payments/edit', [
            'payment' => $payment,
            'customers' => $request->user()
                ->customers()
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->toArray(),
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $payment->update($request->validated());

        return to_route('payments.index');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $payment->delete();

        return to_route('payments.index');
    }
}
