<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignWaiverRequest;
use App\Models\ServiceWaiver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaiverController extends Controller
{
    public function show(Request $request, ServiceWaiver $serviceWaiver): View|RedirectResponse
    {
        if ($serviceWaiver->isSigned()) {
            return view('waiver.already-signed', [
                'signerName' => $serviceWaiver->signer_name,
                'justSigned' => $request->session()->pull('success'),
            ]);
        }

        if ($serviceWaiver->isExpired()) {
            return view('waiver.expired');
        }

        $agreementText = config('cardsmithos.waiver.agreement_text', 'I agree to the terms for card repair services.');

        return view('waiver.show', [
            'waiver' => $serviceWaiver,
            'agreementText' => $agreementText,
        ]);
    }

    public function sign(SignWaiverRequest $request, ServiceWaiver $serviceWaiver): RedirectResponse
    {
        if ($serviceWaiver->isSigned() || $serviceWaiver->isExpired()) {
            return redirect()->back()->with('error', 'This waiver link is no longer valid.');
        }

        $agreementText = config('cardsmithos.waiver.agreement_text', 'I agree to the terms for card repair services.');

        $serviceWaiver->update([
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
