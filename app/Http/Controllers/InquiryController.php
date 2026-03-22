<?php

namespace App\Http\Controllers;

use App\Enums\CommunicationMethod;
use App\Http\Requests\StoreInquiryRequest;
use App\Http\Requests\UpdateInquiryRequest;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Inquiry::class);

        $inquiries = $request->user()
            ->inquiries()
            ->with('customer:id,name')
            ->latest('inquired_at')
            ->get();

        return Inertia::render('inquiries/index', [
            'inquiries' => $inquiries,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Inquiry::class);

        return Inertia::render('inquiries/create', [
            'communicationMethodOptions' => array_map(
                fn (CommunicationMethod $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                CommunicationMethod::cases()
            ),
            'customerOptions' => $request->user()
                ->customers()
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->toArray(),
        ]);
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $createCustomer = filter_var($data['create_customer'] ?? false, FILTER_VALIDATE_BOOLEAN);
        unset($data['create_customer']);

        $data['converted'] = filter_var($data['converted'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($createCustomer) {
            $customerData = ['name' => $data['inquiry_name']];

            if (filter_var($data['contact_detail'], FILTER_VALIDATE_EMAIL)) {
                $customerData['email'] = $data['contact_detail'];
            }

            $customer = $request->user()->customers()->create($customerData);

            $customer->serviceWaiver()->create([
                'expires_at' => now()->addDays(config('cardsmithos.waiver.expiration_days', 30)),
            ]);

            $data['customer_id'] = $customer->id;
            $data['converted'] = true;
        }

        $request->user()->inquiries()->create($data);

        return to_route('inquiries.index');
    }

    public function show(Inquiry $inquiry): Response
    {
        $this->authorize('view', $inquiry);

        $inquiry->load('customer:id,name');

        return Inertia::render('inquiries/show', [
            'inquiry' => $inquiry,
        ]);
    }

    public function edit(Request $request, Inquiry $inquiry): Response
    {
        $this->authorize('update', $inquiry);

        $inquiry->load('customer:id,name');

        return Inertia::render('inquiries/edit', [
            'inquiry' => $inquiry,
            'communicationMethodOptions' => array_map(
                fn (CommunicationMethod $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                CommunicationMethod::cases()
            ),
            'customerOptions' => $request->user()
                ->customers()
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->toArray(),
        ]);
    }

    public function update(UpdateInquiryRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validated();
        $data['converted'] = filter_var($data['converted'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $inquiry->update($data);

        return to_route('inquiries.show', $inquiry);
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('delete', $inquiry);

        $inquiry->delete();

        return to_route('inquiries.index');
    }
}
