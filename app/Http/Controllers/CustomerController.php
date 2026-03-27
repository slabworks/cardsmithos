<?php

namespace App\Http\Controllers;

use App\Enums\CustomerStatus;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Customer::class);

        $customers = $request->user()
            ->customers()
            ->latest()
            ->get();

        return Inertia::render('customers/index', [
            'customers' => $customers,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Customer::class);

        return Inertia::render('customers/create', [
            'statusOptions' => array_map(
                fn (CustomerStatus $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                CustomerStatus::cases()
            ),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = $request->user()->customers()->create($request->validated());

        $customer->serviceWaiver()->create([
            'expires_at' => now()->addDays(config('cardsmithos.waiver.expiration_days', 30)),
        ]);

        return to_route('customers.index');
    }

    /**
     * Generate the absolute waiver URL for a customer (only when not yet signed).
     * Creates a waiver for the customer if none exists (e.g. for customers created before waivers were added).
     */
    public static function waiverUrl(Customer $customer): ?string
    {
        $waiver = $customer->getOrCreateServiceWaiver();

        if ($waiver->isSigned()) {
            return null;
        }

        $relativeUrl = URL::temporarySignedRoute(
            'waiver.show',
            $waiver->expires_at,
            ['customer' => $customer],
            absolute: false
        );

        return url($relativeUrl);
    }

    public function show(Customer $customer): Response
    {
        $this->authorize('view', $customer);

        $customer->load('cards', 'payments', 'shipments', 'serviceWaiver');
        $customer->loadSum('payments as lifetime_value', 'amount');

        $waiverUrl = self::waiverUrl($customer);

        $recentEmails = $customer->emailMessages()
            ->select('id', 'customer_id', 'gmail_thread_id', 'direction', 'from_address', 'from_name', 'subject', 'snippet', 'is_read', 'received_at')
            ->latest('received_at')
            ->limit(5)
            ->get();

        return Inertia::render('customers/show', [
            'customer' => $customer,
            'waiverUrl' => $waiverUrl,
            'recentEmails' => $recentEmails,
        ]);
    }

    public function edit(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        return Inertia::render('customers/edit', [
            'customer' => $customer,
            'statusOptions' => array_map(
                fn (CustomerStatus $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                CustomerStatus::cases()
            ),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return to_route('customers.show', $customer);
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return to_route('customers.index');
    }
}
