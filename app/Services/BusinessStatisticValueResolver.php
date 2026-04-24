<?php

namespace App\Services;

use App\Models\BusinessStatistic;
use App\Models\BusinessStatisticRecord;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Carbon;

class BusinessStatisticValueResolver
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function summariesFor(User $user): array
    {
        $statistics = $user->businessStatistics()
            ->with(['records' => fn ($query) => $query->latest('recorded_at')])
            ->orderBy('group_name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $statistics
            ->map(fn (BusinessStatistic $statistic) => $this->summary($statistic, $user))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function featuredFor(User $user, int $limit = 4): array
    {
        return array_slice($this->summariesFor($user), 0, $limit);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(BusinessStatistic $statistic, ?User $user = null): array
    {
        $user ??= $statistic->user;

        [$value, $recordedAt, $notes] = $statistic->input_method === 'derived'
            ? $this->resolveDerivedValue($statistic, $user)
            : $this->resolveManualValue($statistic);

        return [
            'id' => $statistic->id,
            'name' => $statistic->name,
            'slug' => $statistic->slug,
            'source' => $statistic->source,
            'system_key' => $statistic->system_key,
            'category' => $statistic->category,
            'group_name' => $statistic->group_name,
            'period' => $statistic->period,
            'value_type' => $statistic->value_type,
            'input_method' => $statistic->input_method,
            'description' => $statistic->description,
            'sort_order' => $statistic->sort_order,
            'current_value' => $value,
            'display_value' => $this->formatValue($value, $statistic->value_type),
            'recorded_at' => $recordedAt?->toIso8601String(),
            'notes' => $notes,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function history(BusinessStatistic $statistic, ?User $user = null): array
    {
        $user ??= $statistic->user;

        if ($statistic->input_method === 'derived') {
            [$value, $recordedAt, $notes] = $this->resolveDerivedValue($statistic, $user);

            return [[
                'id' => 'derived-current',
                'value' => $value,
                'display_value' => $this->formatValue($value, $statistic->value_type),
                'recorded_at' => $recordedAt?->toIso8601String(),
                'source' => 'snapshot',
                'period_start' => null,
                'period_end' => null,
                'notes' => $notes,
            ]];
        }

        $records = $statistic->relationLoaded('records')
            ? $statistic->records
            : $statistic->records()->latest('recorded_at')->get();

        return $records
            ->sortByDesc('recorded_at')
            ->values()
            ->map(fn (BusinessStatisticRecord $record) => [
                'id' => $record->id,
                'value' => (float) $record->value,
                'display_value' => $this->formatValue((float) $record->value, $statistic->value_type),
                'recorded_at' => $record->recorded_at?->toIso8601String(),
                'source' => $record->source,
                'period_start' => $record->period_start?->toDateString(),
                'period_end' => $record->period_end?->toDateString(),
                'notes' => $record->notes,
            ])
            ->all();
    }

    /**
     * @return array{0: float, 1: ?Carbon, 2: ?string}
     */
    private function resolveManualValue(BusinessStatistic $statistic): array
    {
        $record = $statistic->relationLoaded('records')
            ? $statistic->records->sortByDesc('recorded_at')->first()
            : $statistic->records()->latest('recorded_at')->first();

        if ($record === null) {
            return [0.0, null, null];
        }

        return [(float) $record->value, $record->recorded_at, $record->notes];
    }

    /**
     * @return array{0: float, 1: Carbon, 2: ?string}
     */
    private function resolveDerivedValue(BusinessStatistic $statistic, User $user): array
    {
        $value = match ($statistic->system_key) {
            'repeat_customers' => $this->repeatCustomers($user),
            'average_order_value' => $this->averageOrderValueThisMonth($user),
            'revenue_this_month' => $this->revenueThisMonth($user),
            default => 0.0,
        };

        $notes = match ($statistic->system_key) {
            'repeat_customers' => 'Calculated from customers with two or more recorded payments.',
            'average_order_value' => 'Calculated from payments recorded this month.',
            'revenue_this_month' => 'Calculated from this month\'s payments, shipment fees, and expenses.',
            default => null,
        };

        return [$value, now(), $notes];
    }

    private function repeatCustomers(User $user): float
    {
        return (float) Customer::query()
            ->where('user_id', $user->id)
            ->whereHas('payments')
            ->withCount('payments')
            ->get()
            ->filter(fn (Customer $customer) => $customer->payments_count > 1)
            ->count();
    }

    private function averageOrderValueThisMonth(User $user): float
    {
        $customerIds = $user->customers()->pluck('id');

        return (float) Payment::query()
            ->whereIn('customer_id', $customerIds)
            ->whereBetween('paid_at', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->avg('amount');
    }

    private function revenueThisMonth(User $user): float
    {
        $customerIds = $user->customers()->pluck('id');
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $payments = (float) Payment::query()
            ->whereIn('customer_id', $customerIds)
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        $shipments = (float) Shipment::query()
            ->whereIn('customer_id', $customerIds)
            ->whereBetween('shipped_at', [$start, $end])
            ->sum('amount');

        $expenses = (float) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount');

        return $payments - $shipments - $expenses;
    }

    private function formatValue(float $value, string $valueType): string
    {
        return match ($valueType) {
            'currency' => '$'.number_format($value, 2),
            'percentage' => number_format($value, 2).'%',
            default => fmod($value, 1.0) === 0.0
                ? number_format($value, 0)
                : number_format($value, 2),
        };
    }
}
