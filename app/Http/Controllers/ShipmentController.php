<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class ShipmentController extends Controller
{
    public function store(StoreShipmentRequest $request, Customer $customer): RedirectResponse
    {
        $customer->shipments()->create($request->validated());

        return to_route('customers.show', $customer);
    }
}
