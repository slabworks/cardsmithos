<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Submission $submission): RedirectResponse
    {
        $this->authorize('update', $submission);

        $submission->payments()->create($request->validated());

        return to_route('submissions.show', $submission);
    }

    public function update(UpdatePaymentRequest $request, Submission $submission, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $payment->update($request->validated());

        return to_route('submissions.show', $submission);
    }

    public function destroy(Submission $submission, Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $payment->delete();

        return to_route('submissions.show', $submission);
    }
}
