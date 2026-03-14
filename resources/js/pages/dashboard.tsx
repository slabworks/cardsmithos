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
import { DollarSign, TrendingUp, User, Users } from 'lucide-react';
import { Bar } from 'react-chartjs-2';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
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

type DashboardProps = {
    totalPayments: number;
    totalCustomers: number;
    newestCustomer: {
        id: number;
        name: string;
        created_at: string;
    } | null;
    revenueByMonth: RevenueByMonth[];
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

export default function Dashboard({
    totalPayments,
    totalCustomers,
    newestCustomer,
    revenueByMonth,
}: DashboardProps) {
    const hasRevenue = revenueByMonth.some((r) => r.total > 0);
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
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total payments
                            </CardTitle>
                            <DollarSign className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {formatCurrency(totalPayments)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Sum of all payment amounts
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Total customers
                            </CardTitle>
                            <Users className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {totalCustomers}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Total customer count
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Newest customer
                            </CardTitle>
                            <User className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            {newestCustomer ? (
                                <>
                                    <div className="text-2xl font-bold">
                                        <Link
                                            href={CustomerController.show.url(
                                                newestCustomer,
                                            )}
                                            className="text-primary hover:underline"
                                        >
                                            {newestCustomer.name}
                                        </Link>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Added{' '}
                                        {new Date(
                                            newestCustomer.created_at,
                                        ).toLocaleDateString()}
                                    </p>
                                </>
                            ) : (
                                <>
                                    <div className="text-2xl font-bold">—</div>
                                    <p className="text-xs text-muted-foreground">
                                        No customers yet
                                    </p>
                                </>
                            )}
                        </CardContent>
                    </Card>
                </div>
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
