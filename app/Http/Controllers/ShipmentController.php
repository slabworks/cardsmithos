<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Customer;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShipmentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Shipment::class);

        $customerIds = $request->user()->customers()->pluck('id');

        $shipments = Shipment::query()
            ->with('customer:id,name')
            ->whereIn('customer_id', $customerIds)
            ->latest('shipped_at')
            ->get();

        return Inertia::render('shipments/index', [
            'shipments' => $shipments,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Shipment::class);

        return Inertia::render('shipments/create', [
            'customers' => $request->user()
                ->customers()
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->toArray(),
        ]);
    }

    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $customer = Customer::query()->findOrFail($request->validated()['customer_id']);

        $customer->shipments()->create($request->validated());

        return to_route('shipments.index');
    }

    public function edit(Request $request, Shipment $shipment): Response
    {
        $this->authorize('update', $shipment);

        return Inertia::render('shipments/edit', [
            'shipment' => $shipment,
            'customers' => $request->user()
                ->customers()
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->toArray(),
        ]);
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        $shipment->update($request->validated());

        return to_route('shipments.index');
    }

    public function destroy(Shipment $shipment): RedirectResponse
    {
        $this->authorize('delete', $shipment);

        $shipment->delete();

        return to_route('shipments.index');
    }
}
