import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';

export default function StatisticsIndex({
    statistics,
}: {
    statistics: Array<{
        id: number;
        name: string;
        group_name: string | null;
        category: string;
        display_value: string;
        recorded_at: string | null;
        input_method: string;
    }>;
}) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Statistics', href: '/statistics' }]}>
            <Head title="Statistics" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mb-4 flex items-center justify-between">
                    <Heading
                        title="Statistics"
                        description="Track manual KPIs and monitor calculated business metrics."
                    />
                    <Button asChild>
                        <Link href="/statistics/create">Add statistic</Link>
                    </Button>
                </div>
                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    {statistics.map((statistic) => (
                        <Link key={statistic.id} href={`/statistics/${statistic.id}`} className="rounded-lg border p-4 hover:bg-muted/50">
                            <p className="text-xs text-muted-foreground">{statistic.group_name ?? statistic.category}</p>
                            <p className="font-medium">{statistic.name}</p>
                            <p className="mt-2 text-2xl font-semibold">{statistic.display_value}</p>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {statistic.input_method === 'derived' ? 'Calculated automatically' : statistic.recorded_at ? `Updated ${new Date(statistic.recorded_at).toLocaleDateString()}` : 'No records yet'}
                            </p>
                        </Link>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
