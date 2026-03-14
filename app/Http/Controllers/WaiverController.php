<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignWaiverRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaiverController extends Controller
{
    public function show(Request $request, Customer $customer): View|RedirectResponse
    {
        $waiver = $customer->serviceWaiver;

        if ($waiver === null) {
            $waiver = $customer->getOrCreateServiceWaiver();
        }

        if ($waiver->isSigned()) {
            return view('waiver.already-signed', [
                'customerName' => $customer->name,
                'justSigned' => $request->session()->pull('success'),
            ]);
        }

        if ($waiver->isExpired()) {
            return view('waiver.expired', [
                'customerName' => $customer->name,
            ]);
        }

        $agreementText = config('cardsmithos.waiver.agreement_text', 'I agree to the terms for card repair services.');

        return view('waiver.show', [
            'customer' => $customer,
            'agreementText' => $agreementText,
        ]);
    }

    public function sign(SignWaiverRequest $request, Customer $customer): RedirectResponse
    {
        $waiver = $customer->serviceWaiver;

        if ($waiver === null || $waiver->isSigned() || $waiver->isExpired()) {
            return redirect()->back()->with('error', 'This waiver link is no longer valid.');
        }

        $agreementText = config('cardsmithos.waiver.agreement_text', 'I agree to the terms for card repair services.');

        $waiver->update([
            'signed_at' => now(),
            'signer_name' => $request->validated('signer_name'),
            'signer_email' => $request->validated('signer_email'),
            'signer_ip' => $request->ip(),
            'signer_user_agent' => $request->userAgent(),
            'agreement_text' => $agreementText,
        ]);

        $customer->update([
            'waiver_agreed' => true,
            'waiver_agreed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Thank you. Your waiver has been recorded.');
    }
}
