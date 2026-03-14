<?php

return [
    'name' => 'Cardsmith OS',
    'url' => 'https://cardsmithos.test',
    'description' => 'Open-source CRM for trading card repair shops. Track submissions, manage jobs, and keep your shop organized—without lock-in or subscription fees.',

    'waiver' => [
        'expiration_days' => (int) env('WAIVER_EXPIRATION_DAYS', 30),
        'agreement_text' => env('WAIVER_AGREEMENT_TEXT', <<<TEXT
"""
        
        SERVICE WAIVER AGREEMENT

By signing this waiver, you acknowledge and agree to the following terms regarding the trading card repair and restoration services we provide:

1. SERVICE DESCRIPTION
   We provide professional trading card repair and restoration services, including but not limited to: cleaning, crease reduction, surface improvement, and pre-grading preparation. These services are performed with the goal of improving the visual appearance and condition of trading cards.

2. RISK ACKNOWLEDGMENT
   You understand and acknowledge that:
   - Trading card repair and restoration involves physical manipulation and treatment of your cards
   - While we use professional techniques and exercise care, there are inherent risks in any restoration process
   - Some damage may be irreversible or may not respond to restoration attempts
   - The restoration process may not result in grade improvements with grading companies (e.g. PSA, BGS, CGC)

3. LIMITATION OF LIABILITY
   We and our operators shall not be liable for:
   - Any damage that may occur during the restoration process, except in cases of gross negligence
   - Failure to achieve desired results or grade improvements
   - Any indirect, incidental, or consequential damages
   - Loss of value that may occur as a result of the restoration process

4. AGREEMENT TO TERMS
   By signing this waiver, you confirm that:
   - You have read and understood all terms and conditions
   - You are the legal owner of the cards or have authorization to consent to restoration
   - You agree to release us from liability
   - You understand that restoration results cannot be guaranteed

5. DISPUTE RESOLUTION
   Any disputes arising from this agreement shall be resolved through good faith negotiation. If resolution cannot be reached, disputes shall be subject to the laws of the jurisdiction in which we operate.

6. ELECTRONIC SIGNATURE AND ELECTRONIC RECORDS
   You agree to transact with us electronically and to use electronic records and electronic signatures. By checking the acceptance box and submitting this waiver (including by any similar “I agree”, “Accept”, or “Sign” action on the waiver page), you are signing this agreement electronically and adopting your electronic signature with the intent to be legally bound. You agree that this electronic signature has the same legal effect as a handwritten signature, and that we may rely on the electronic record of your acceptance (including the date/time, IP address, and user-agent information) as proof of your signature and consent. This agreement is deemed signed and effective upon your electronic acceptance.

By signing below, you acknowledge that you have read, understood, and agree to be bound by all terms and conditions set forth in this Service Waiver Agreement.
TEXT),
    ],
];
