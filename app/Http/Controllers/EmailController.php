<?php

namespace App\Http\Controllers;

use App\Enums\CustomerStatus;
use App\Jobs\SyncGmailAccount;
use App\Models\Customer;
use App\Models\GmailContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailController extends Controller
{
    public function index(Request $request): Response
    {
        $account = $request->user()->gmailAccount;

        return Inertia::render('email/index', [
            'gmailAccount' => $account ? [
                'email' => $account->email,
                'lastSyncedAt' => $account->last_synced_at,
            ] : null,
            'contacts' => $account ? $account->contacts()
                ->with('customer:id,name,email')
                ->latest('last_message_at')
                ->paginate(25)
                ->withQueryString()
                ->through(fn (GmailContact $contact): array => [
                    'email' => $contact->email,
                    'name' => $contact->name,
                    'latestMessage' => [
                        'subject' => $contact->latest_subject,
                        'snippet' => $contact->latest_snippet,
                        'sentAt' => $contact->last_message_at,
                    ],
                    'customer' => $contact->customer ? [
                        'id' => $contact->customer->id,
                        'name' => $contact->customer->name,
                        'email' => $contact->customer->email,
                    ] : null,
                ]) : [],
        ]);
    }

    public function sync(Request $request): RedirectResponse
    {
        $account = $request->user()->gmailAccount;
        abort_unless($account !== null, 409, 'Connect Gmail first.');

        SyncGmailAccount::dispatch($account->id, $account->last_synced_at === null);

        return back();
    }

    public function convert(Request $request): RedirectResponse
    {
        $account = $request->user()->gmailAccount;
        abort_unless($account !== null, 409, 'Connect Gmail first.');

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = mb_strtolower((string) $validated['email']);

        $contact = GmailContact::query()
            ->where('gmail_account_id', $account->id)
            ->where('email', $email)
            ->firstOrFail();

        $customer = Customer::query()
            ->where('user_id', $request->user()->id)
            ->where('email', $email)
            ->first();

        if ($customer === null) {
            $customer = $request->user()->customers()->create([
                'name' => $contact->name ?: $this->nameFromEmail($email),
                'email' => $email,
                'status' => CustomerStatus::WarmLead,
                'referral_source' => 'Gmail',
                'notes' => trim('Converted from Gmail inbox.'.PHP_EOL.PHP_EOL.'Latest message: '.($contact->latest_subject ?: '(No subject)').PHP_EOL.($contact->latest_snippet ?: '')),
            ]);

            $customer->serviceWaiver()->create([
                'expires_at' => now()->addDays(config('cardsmithos.waiver.expiration_days', 30)),
            ]);
        }

        $contact->update(['customer_id' => $customer->id]);

        return to_route('customers.show', $customer);
    }

    private function nameFromEmail(string $email): string
    {
        return str((string) str($email)->before('@'))
            ->replace(['.', '_', '-'], ' ')
            ->title()
            ->toString();
    }
}
