<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignWaiverRequest;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaiverController extends Controller
{
    public function show(Request $request, Submission $submission): View|RedirectResponse
    {
        $submission->load('customer');
        $waiver = $submission->serviceWaiver;

        if ($waiver === null) {
            $waiver = $submission->getOrCreateServiceWaiver();
        }

        if ($waiver->isSigned()) {
            return view('waiver.already-signed', [
                'customerName' => $submission->customer->name,
                'justSigned' => $request->session()->pull('success'),
            ]);
        }

        if ($waiver->isExpired()) {
            return view('waiver.expired', [
                'customerName' => $submission->customer->name,
            ]);
        }

        $agreementText = config('cardsmithos.waiver.agreement_text', 'I agree to the terms for card repair services.');

        return view('waiver.show', [
            'customer' => $submission->customer,
            'agreementText' => $agreementText,
        ]);
    }

    public function sign(SignWaiverRequest $request, Submission $submission): RedirectResponse
    {
        $waiver = $submission->serviceWaiver;

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

        return redirect()->back()->with('success', 'Thank you. Your waiver has been recorded.');
    }
}
