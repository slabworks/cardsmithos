<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Submission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InvoiceController extends Controller
{
    public function create(Submission $submission): Response
    {
        $this->authorize('view', $submission);

        $submission->load('customer');
        $submission->load(['cards' => function ($query): void {
            $query->select('id', 'submission_id', 'name', 'restoration_hours', 'estimated_fee');
        }]);

        $settings = auth()->user()->businessSettings;

        $downloadUrl = URL::temporarySignedRoute(
            'submissions.invoices.download',
            now()->addHour(),
            ['submission' => $submission],
            absolute: false
        );

        return Inertia::render('invoices/create', [
            'submission' => [
                'id' => $submission->id,
                'customer' => $submission->customer->only('id', 'name'),
                'cards' => $submission->cards,
            ],
            'downloadUrl' => url($downloadUrl),
            'businessSettings' => [
                'hourly_rate' => (float) ($settings?->hourly_rate ?? 0),
                'default_fixed_rate' => (float) ($settings?->default_fixed_rate ?? 0),
                'tax_rate' => (float) ($settings?->tax_rate ?? 0),
                'currency' => $settings?->currency ?? 'USD',
                'company_name' => $settings?->company_name ?? '',
            ],
        ]);
    }

    public function download(StoreInvoiceRequest $request, Submission $submission): HttpResponse
    {
        $submission->load('customer');
        $settings = auth()->user()->businessSettings;
        $hourlyRate = (float) ($settings?->hourly_rate ?? 0);
        $defaultFixedRate = (float) ($settings?->default_fixed_rate ?? 0);
        $taxRate = (float) ($settings?->tax_rate ?? 0);
        $currency = $settings?->currency ?? 'USD';
        $companyName = $settings?->company_name ?? '';

        $cards = $submission->cards()->whereIn('id', $request->validated('card_ids'))->get();

        $lineItems = $cards->map(function ($card) use ($hourlyRate, $defaultFixedRate) {
            if ($card->restoration_hours !== null) {
                return [
                    'name' => $card->name,
                    'rate_type' => 'hourly',
                    'hours' => (float) $card->restoration_hours,
                    'unit_rate' => $hourlyRate,
                    'total' => (float) $card->restoration_hours * $hourlyRate,
                ];
            }

            return [
                'name' => $card->name,
                'rate_type' => 'fixed',
                'hours' => null,
                'unit_rate' => $defaultFixedRate,
                'total' => $defaultFixedRate,
            ];
        });

        $subtotal = $lineItems->sum('total');
        $shipping = (float) ($request->validated('shipping') ?? 0);
        $packaging = (float) ($request->validated('packaging') ?? 0);
        $handling = (float) ($request->validated('handling') ?? 0);
        $extras = $shipping + $packaging + $handling;
        $taxableAmount = $subtotal + $extras;
        $tax = round($taxableAmount * ($taxRate / 100), 2);
        $grandTotal = $taxableAmount + $tax;

        $pdf = Pdf::loadView('invoices.pdf', [
            'companyName' => $companyName,
            'currency' => $currency,
            'customer' => $submission->customer,
            'lineItems' => $lineItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'packaging' => $packaging,
            'handling' => $handling,
            'taxRate' => $taxRate,
            'tax' => $tax,
            'grandTotal' => $grandTotal,
            'date' => now()->format('F j, Y'),
        ]);

        $filename = 'invoice-'.str($submission->customer->name)->slug().'-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }
}
