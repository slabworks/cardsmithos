<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessStatisticRecordRequest;
use App\Models\BusinessStatistic;
use App\Models\BusinessStatisticRecord;
use Illuminate\Http\RedirectResponse;

class BusinessStatisticRecordController extends Controller
{
    public function store(StoreBusinessStatisticRecordRequest $request, BusinessStatistic $businessStatistic): RedirectResponse
    {
        $this->authorize('update', $businessStatistic);

        abort_if($businessStatistic->input_method !== 'manual', 422, 'Only manual statistics can accept records.');

        $businessStatistic->records()->create($request->validated());

        return to_route('statistics.show', $businessStatistic);
    }

    public function destroy(BusinessStatistic $businessStatistic, BusinessStatisticRecord $businessStatisticRecord): RedirectResponse
    {
        $this->authorize('update', $businessStatistic);

        abort_if($businessStatistic->input_method !== 'manual', 422, 'Only manual statistics can delete records.');
        abort_unless($businessStatisticRecord->business_statistic_id === $businessStatistic->id, 404);

        $businessStatisticRecord->delete();

        return to_route('statistics.show', $businessStatistic);
    }
}
