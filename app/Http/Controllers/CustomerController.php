<?php

namespace App\Http\Controllers;

use App\Enums\CustomerPlatform;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Customer::class);

        $search = trim((string) $request->query('search', ''));

        $customers = $request->user()
            ->customers()
            ->select('id', 'name', 'contact_detail', 'platform', 'phone', 'address', 'created_at')
            ->withCount('submissions')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_detail', 'like', "%{$search}%")
                        ->orWhere('platform', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return Inertia::render('customers/index', [
            'customers' => $customers,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Customer::class);

        return Inertia::render('customers/create', [
            'platformOptions' => $this->platformOptions(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = $request->user()->customers()->create($request->validated());

        return to_route('customers.edit', $customer);
    }

    public function edit(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        $customer->load([
            'submissions' => fn ($query) => $query
                ->select('id', 'customer_id', 'status', 'created_at')
                ->latest(),
        ]);

        return Inertia::render('customers/edit', [
            'customer' => $customer,
            'platformOptions' => $this->platformOptions(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return to_route('customers.edit', $customer);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function platformOptions(): array
    {
        return array_map(
            fn (CustomerPlatform $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            CustomerPlatform::cases()
        );
    }
}
