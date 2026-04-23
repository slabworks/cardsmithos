import { Head, Link } from '@inertiajs/react';
import {
    Chart as ChartJS,
    BarElement,
    CategoryScale,
    Legend,
    LinearScale,
    Title,
    Tooltip,
} from 'chart.js';
import {
    DollarSign,
    Kanban,
    Package,
    Receipt,
    TrendingUp,
    UserCheck,
} from 'lucide-react';
import { Bar } from 'react-chartjs-2';
import CardController from '@/actions/App/Http/Controllers/CardController';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

type RevenueByMonth = { month: string; total: number };

type KanbanCard = {
    id: number;
    name: string;
    status: string;
    customer: { id: number; name: string };
};

type DashboardProps = {
    totalPayments: number;
    totalShipmentFees: number;
    totalExpenses: number;
    totalCustomers: number;
    convertedCustomers: number;
    revenueByMonth: RevenueByMonth[];
    cardsByStatus: {
        backlog: KanbanCard[];
        pending: KanbanCard[];
        in_progress: KanbanCard[];
    };
};

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
}

function formatMonthLabel(ym: string): string {
    const [year, month] = ym.split('-');
    const date = new Date(parseInt(year, 10), parseInt(month, 10) - 1);

    return date.toLocaleDateString('en-US', {
        month: 'short',
        year: '2-digit',
    });
}

const columns: {
    key: keyof DashboardProps['cardsByStatus'];
    label: string;
    color: string;
    dotColor: string;
}[] = [
    {
        key: 'backlog',
        label: 'Backlog',
        color: 'border-slate-400',
        dotColor: 'bg-slate-400',
    },
    {
        key: 'pending',
        label: 'Pending',
        color: 'border-amber-400',
        dotColor: 'bg-amber-400',
    },
    {
        key: 'in_progress',
        label: 'In Progress',
        color: 'border-blue-400',
        dotColor: 'bg-blue-400',
    },
];

export default function Dashboard({
    totalPayments,
    totalShipmentFees,
    totalExpenses,
    totalCustomers,
    convertedCustomers,
    revenueByMonth,
    cardsByStatus,
}: DashboardProps) {
    const hasRevenue = revenueByMonth.some((r) => r.total > 0);
    const conversionRate =
        totalCustomers > 0
            ? Math.round((convertedCustomers / totalCustomers) * 100)
            : 0;
    const chartData = {
        labels: revenueByMonth.map((r) => formatMonthLabel(r.month)),
        datasets: [
            {
                label: 'Revenue',
                data: revenueByMonth.map((r) => r.total),
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
            },
        ],
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Revenue (last 12 months)',
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (value: string | number) =>
                        formatCurrency(Number(value)),
                },
            },
        },
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-4">
                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Revenue (less shipping)
                            </CardTitle>
                            <DollarSign className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatCurrency(totalPayments)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Sum of all tracked payments
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Shipment costs
                            </CardTitle>
                            <Package className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatCurrency(totalShipmentFees)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Sum of all shipment fees
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total expenses
                            </CardTitle>
                            <Receipt className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatCurrency(totalExpenses)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Sum of all tracked expenses
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Customer conversion
                            </CardTitle>
                            <UserCheck className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {conversionRate}%
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {convertedCustomers} of {totalCustomers}{' '}
                                customers converted
                            </p>
                        </CardContent>
                    </Card>
                </div>
                <Card className="flex max-h-[80vh] flex-col border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader className="shrink-0">
                        <CardTitle className="flex items-center gap-2">
                            <Kanban className="size-5" />
                            Work board
                        </CardTitle>
                        <CardDescription>
                            All cards across customers by status
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="flex min-h-0 flex-1 flex-col overflow-hidden">
                        <div className="grid min-h-0 min-w-[640px] flex-1 grid-cols-3 gap-4">
                            {columns.map((col) => {
                                const cards = cardsByStatus[col.key];

                                return (
                                    <div
                                        key={col.key}
                                        className={`flex min-h-0 flex-col overflow-hidden rounded-lg border-t-2 ${col.color} bg-muted/40 p-3`}
                                    >
                                        <div className="mb-3 flex shrink-0 items-center gap-2">
                                            <span
                                                className={`size-2 rounded-full ${col.dotColor}`}
                                            />
                                            <span className="text-sm font-medium">
                                                {col.label}
                                            </span>
                                            <span className="ml-auto text-xs text-muted-foreground">
                                                {cards.length}
                                            </span>
                                        </div>
                                        {cards.length === 0 ? (
                                            <p className="py-4 text-center text-xs text-muted-foreground">
                                                No cards
                                            </p>
                                        ) : (
                                            <div className="flex min-h-0 flex-1 flex-col gap-2 overflow-y-auto">
                                                {cards.map((card) => (
                                                    <Link
                                                        key={card.id}
                                                        href={CardController.edit.url(
                                                            {
                                                                customer:
                                                                    card
                                                                        .customer
                                                                        .id,
                                                                card: card.id,
                                                            },
                                                        )}
                                                        className="rounded-md border border-sidebar-border bg-card p-3 shadow-sm transition-shadow hover:shadow-md"
                                                    >
                                                        <p className="text-sm font-medium">
                                                            {card.name}
                                                        </p>
                                                        <p className="mt-0.5 text-xs text-muted-foreground">
                                                            {card.customer.name}
                                                        </p>
                                                    </Link>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>
                <Card className="flex-1 border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <TrendingUp className="size-5" />
                            Revenue
                        </CardTitle>
                        <CardDescription>
                            Monthly revenue for the last 12 months
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {hasRevenue ? (
                            <div className="h-[300px] w-full">
                                <Bar data={chartData} options={chartOptions} />
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center gap-2 py-12 text-center text-muted-foreground">
                                <TrendingUp className="size-10" />
                                <p className="text-sm">
                                    No payment data yet for the last 12 months
                                </p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
