<?php

namespace App\Http\Controllers;

use App\Enums\CardStatus;
use App\Models\Card;
use App\Models\Expense;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Shipment;
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
        $totalShipmentFees = Shipment::whereIn('customer_id', $customerIds)->sum('amount');
        $totalExpenses = (float) Expense::where('user_id', $request->user()->id)->sum('amount');

        $totalInquiries = Inquiry::where('user_id', $request->user()->id)->count();
        $convertedInquiries = Inquiry::where('user_id', $request->user()->id)->where('converted', true)->count();

        $revenueByMonth = $this->revenueByMonth($customerIds);

        $kanbanValues = collect(CardStatus::kanbanStatuses())->map(fn (CardStatus $s) => $s->value);

        $cards = Card::whereIn('customer_id', $customerIds)
            ->whereIn('status', $kanbanValues)
            ->with('customer:id,name')
            ->select('id', 'customer_id', 'name', 'status', 'condition_before', 'estimated_fee')
            ->get()
            ->groupBy(fn (Card $card) => $card->status->value);

        return Inertia::render('dashboard', [
            'totalPayments' => (float) $totalPayments - (float) $totalShipmentFees,
            'totalShipmentFees' => (float) $totalShipmentFees,
            'totalExpenses' => $totalExpenses,
            'totalInquiries' => $totalInquiries,
            'convertedInquiries' => $convertedInquiries,
            'revenueByMonth' => $revenueByMonth,
            'cardsByStatus' => [
                'backlog' => $cards->get('backlog', collect())->values(),
                'pending' => $cards->get('pending', collect())->values(),
                'in_progress' => $cards->get('in_progress', collect())->values(),
            ],
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
        $paymentMonthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', paid_at)"
            : "DATE_FORMAT(paid_at, '%Y-%m')";
        $shipmentMonthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', shipped_at)"
            : "DATE_FORMAT(shipped_at, '%Y-%m')";

        $paymentTotals = Payment::query()
            ->whereIn('customer_id', $customerIds)
            ->where('paid_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->selectRaw("{$paymentMonthExpression} as month, COALESCE(SUM(amount), 0) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($total) => (float) $total);

        $shipmentTotals = Shipment::query()
            ->whereIn('customer_id', $customerIds)
            ->where('shipped_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->selectRaw("{$shipmentMonthExpression} as month, COALESCE(SUM(amount), 0) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($total) => (float) $total);

        return $months->map(fn (string $month) => [
            'month' => $month,
            'total' => $paymentTotals->get($month, 0.0) - $shipmentTotals->get($month, 0.0),
        ])->values()->all();
    }
}
