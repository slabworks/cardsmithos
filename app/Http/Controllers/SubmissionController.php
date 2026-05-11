<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionStatus;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Requests\UpdateSubmissionRequest;
use App\Models\Customer;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Submission::class);

        $submissions = $request->user()
            ->submissions()
            ->with('customer:id,name,email')
            ->latest()
            ->get();

        return Inertia::render('submissions/index', [
            'submissions' => $submissions,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Submission::class);

        return Inertia::render('submissions/create', [
            'customers' => $request->user()
                ->customers()
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreSubmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $customer = $this->resolveCustomer($request, $validated);

        $submission = $request->user()->submissions()->create([
            'customer_id' => $customer->id,
            'status' => $validated['status'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'referral_source' => $validated['referral_source'] ?? null,
        ]);

        $submission->serviceWaiver()->create([
            'expires_at' => now()->addDays(config('cardsmithos.waiver.expiration_days', 30)),
        ]);

        return to_route('submissions.show', $submission);
    }

    /**
     * Generate the absolute waiver URL for a submission when not yet signed.
     */
    public static function waiverUrl(Submission $submission): ?string
    {
        $waiver = $submission->getOrCreateServiceWaiver();

        if ($waiver->isSigned()) {
            return null;
        }

        $relativeUrl = URL::temporarySignedRoute(
            'waiver.show',
            $waiver->expires_at,
            ['submission' => $submission],
            absolute: false
        );

        return url($relativeUrl);
    }

    public function show(Submission $submission): Response
    {
        $this->authorize('view', $submission);

        $submission->load('customer', 'cards', 'payments', 'shipments', 'serviceWaiver');
        $submission->loadSum('payments as lifetime_value', 'amount');

        return Inertia::render('submissions/show', [
            'submission' => $submission,
            'emailContacts' => $submission->customer->gmailContacts()
                ->latest('last_message_at')
                ->limit(10)
                ->get(['id', 'email', 'name', 'latest_subject', 'latest_snippet', 'last_message_at']),
            'waiverUrl' => self::waiverUrl($submission),
        ]);
    }

    public function edit(Request $request, Submission $submission): Response
    {
        $this->authorize('update', $submission);

        $submission->load('customer');

        return Inertia::render('submissions/edit', [
            'submission' => $submission,
            'customers' => $request->user()
                ->customers()
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(UpdateSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validated();
        $customer = Customer::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($validated['customer_id']);

        $customer->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        $submission->update([
            'customer_id' => $customer->id,
            'status' => $validated['status'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'referral_source' => $validated['referral_source'] ?? null,
        ]);

        return to_route('submissions.show', $submission);
    }

    public function destroy(Submission $submission): RedirectResponse
    {
        $this->authorize('delete', $submission);

        $submission->delete();

        return to_route('submissions.index');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveCustomer(StoreSubmissionRequest $request, array $validated): Customer
    {
        if (! empty($validated['customer_id'])) {
            return Customer::query()
                ->where('user_id', $request->user()->id)
                ->findOrFail($validated['customer_id']);
        }

        return $request->user()->customers()->create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);
    }

    /**
     * @return array<int, array{value: string, label: string, color: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (SubmissionStatus $case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'color' => $case->color(),
            ],
            SubmissionStatus::cases()
        );
    }
}
