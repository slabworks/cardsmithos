<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $customerIds = $request->user()->customers()->pluck('id');

        $totalPayments = Payment::whereIn('customer_id', $customerIds)->sum('amount');
        $totalCustomers = $request->user()->customers()->count();
        $newestCustomer = $request->user()
            ->customers()
            ->latest()
            ->first(['id', 'name', 'created_at']);

        $revenueByMonth = $this->revenueByMonth($customerIds);

        return Inertia::render('dashboard', [
            'totalPayments' => (float) $totalPayments,
            'totalCustomers' => $totalCustomers,
            'newestCustomer' => $newestCustomer,
            'revenueByMonth' => $revenueByMonth,
        ]);
    }

    /**
     * @param  Collection<int, int>  $customerIds
     * @return array<int, array{month: string, total: float}>
     */
    private function revenueByMonth($customerIds): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i)->format('Y-m'));
        }

        $driver = DB::connection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', paid_at)"
            : "DATE_FORMAT(paid_at, '%Y-%m')";

        $totals = Payment::query()
            ->whereIn('customer_id', $customerIds)
            ->where('paid_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->selectRaw("{$monthExpression} as month, COALESCE(SUM(amount), 0) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($total) => (float) $total);

        return $months->map(fn (string $month) => [
            'month' => $month,
            'total' => $totals->get($month, 0.0),
        ])->values()->all();
    }
}
