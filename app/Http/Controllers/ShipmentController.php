<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Shipment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;

class ShipmentController extends Controller
{
    public function store(StoreShipmentRequest $request, Submission $submission): RedirectResponse
    {
        $this->authorize('update', $submission);

        $submission->shipments()->create($request->validated());

        return to_route('submissions.show', $submission);
    }

    public function update(UpdateShipmentRequest $request, Submission $submission, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        $shipment->update($request->validated());

        return to_route('submissions.show', $submission);
    }

    public function destroy(Submission $submission, Shipment $shipment): RedirectResponse
    {
        $this->authorize('delete', $shipment);

        $shipment->delete();

        return to_route('submissions.show', $submission);
    }
}
