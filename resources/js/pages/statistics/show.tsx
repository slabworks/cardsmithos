import { Form, Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';

type Statistic = {
    id: number;
    name: string;
    source: string;
    category: string;
    group_name: string | null;
    period: string;
    value_type: string;
    input_method: string;
    description: string | null;
    display_value: string;
    recorded_at: string | null;
    notes: string | null;
};

type RecordItem = {
    id: number | string;
    display_value: string;
    recorded_at: string | null;
    source: string;
    period_start: string | null;
    period_end: string | null;
    notes: string | null;
};

export default function StatisticsShow({
    statistic,
    records,
}: {
    statistic: Statistic;
    records: RecordItem[];
}) {
    const isManual = statistic.input_method === 'manual';

    return (
        <AppLayout breadcrumbs={[{ title: 'Statistics', href: '/statistics' }, { title: statistic.name, href: `/statistics/${statistic.id}` }]}>
            <Head title={statistic.name} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title={statistic.name}
                        description={statistic.description ?? 'Track this KPI over time from one place.'}
                    />
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/statistics/${statistic.id}/edit`}>Edit</Link>
                        </Button>
                        <Form action={`/statistics/${statistic.id}`} method="delete">
                            {({ processing }) => (
                                <Button variant="destructive" disabled={processing}>
                                    Delete statistic
                                </Button>
                            )}
                        </Form>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <div className="rounded-lg border bg-card p-4">
                        <p className="text-xs uppercase text-muted-foreground">Current value</p>
                        <p className="mt-2 text-2xl font-semibold">{statistic.display_value}</p>
                    </div>
                    <div className="rounded-lg border bg-card p-4">
                        <p className="text-xs uppercase text-muted-foreground">Type</p>
                        <p className="mt-2 font-medium capitalize">{statistic.input_method}</p>
                    </div>
                    <div className="rounded-lg border bg-card p-4">
                        <p className="text-xs uppercase text-muted-foreground">Category</p>
                        <p className="mt-2 font-medium">{statistic.group_name ?? statistic.category}</p>
                    </div>
                    <div className="rounded-lg border bg-card p-4">
                        <p className="text-xs uppercase text-muted-foreground">Period</p>
                        <p className="mt-2 font-medium capitalize">{statistic.period}</p>
                    </div>
                </div>

                {isManual ? (
                    <section className="rounded-lg border border-sidebar-border bg-card p-4">
                        <h2 className="mb-4 text-sm font-medium text-muted-foreground">Add record</h2>
                        <Form action={`/statistics/${statistic.id}/records`} method="post" className="grid gap-4 md:grid-cols-2">
                            {({ errors, processing }) => (
                                <>
                                    <div className="grid gap-2">
                                        <label htmlFor="value" className="text-sm font-medium">Value *</label>
                                        <input id="value" name="value" type="number" step="0.01" required className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none" />
                                        <InputError message={errors.value} />
                                    </div>
                                    <div className="grid gap-2">
                                        <label htmlFor="recorded_at" className="text-sm font-medium">Recorded at *</label>
                                        <input id="recorded_at" name="recorded_at" type="date" defaultValue={new Date().toISOString().slice(0, 10)} required className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none" />
                                        <InputError message={errors.recorded_at} />
                                    </div>
                                    <div className="grid gap-2">
                                        <label htmlFor="period_start" className="text-sm font-medium">Period start</label>
                                        <input id="period_start" name="period_start" type="date" className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none" />
                                        <InputError message={errors.period_start} />
                                    </div>
                                    <div className="grid gap-2">
                                        <label htmlFor="period_end" className="text-sm font-medium">Period end</label>
                                        <input id="period_end" name="period_end" type="date" className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none" />
                                        <InputError message={errors.period_end} />
                                    </div>
                                    <input type="hidden" name="source" value="manual" />
                                    <div className="md:col-span-2 grid gap-2">
                                        <label htmlFor="notes" className="text-sm font-medium">Notes</label>
                                        <textarea id="notes" name="notes" rows={3} className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none" />
                                        <InputError message={errors.notes} />
                                    </div>
                                    <div className="md:col-span-2">
                                        <Button type="submit" disabled={processing}>Save record</Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </section>
                ) : (
                    <section className="rounded-lg border border-sidebar-border bg-card p-4">
                        <h2 className="mb-2 text-sm font-medium text-muted-foreground">Calculated statistic</h2>
                        <p className="text-sm text-muted-foreground">
                            This KPI is derived from your existing business data and updates automatically.
                        </p>
                    </section>
                )}

                <section className="rounded-lg border border-sidebar-border bg-card">
                    <div className="border-b border-sidebar-border px-4 py-3">
                        <h2 className="font-medium">History</h2>
                    </div>
                    {records.length === 0 ? (
                        <p className="px-4 py-6 text-sm text-muted-foreground">No records yet.</p>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {records.map((record) => (
                                <li key={record.id} className="px-4 py-3">
                                    <div className="flex flex-wrap items-start justify-between gap-2">
                                        <div>
                                            <p className="font-medium">{record.display_value}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {record.recorded_at
                                                    ? new Date(record.recorded_at).toLocaleDateString()
                                                    : 'No date'}
                                            </p>
                                            {(record.period_start || record.period_end) && (
                                                <p className="text-xs text-muted-foreground">
                                                    {record.period_start ?? '...'} to {record.period_end ?? '...'}
                                                </p>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <p className="text-xs uppercase text-muted-foreground">{record.source}</p>
                                            {isManual && typeof record.id === 'number' && (
                                                <Form action={`/statistics/${statistic.id}/records/${record.id}`} method="delete">
                                                    {({ processing }) => (
                                                        <Button
                                                            type="submit"
                                                            variant="ghost"
                                                            size="sm"
                                                            disabled={processing}
                                                            className="h-auto px-2 py-1 text-destructive"
                                                        >
                                                            Delete
                                                        </Button>
                                                    )}
                                                </Form>
                                            )}
                                        </div>
                                    </div>
                                    {record.notes && (
                                        <p className="mt-2 text-sm text-muted-foreground">{record.notes}</p>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
