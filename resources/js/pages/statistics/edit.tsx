import { Form, Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';

type StatisticOptions = {
    categories: string[];
    periods: string[];
    valueTypes: string[];
};

type Statistic = {
    id: number;
    name: string;
    source: string;
    input_method: string;
    category: string;
    group_name: string | null;
    period: string;
    value_type: string;
    description: string | null;
    sort_order: number;
};

export default function StatisticsEdit({
    statistic,
    options,
}: {
    statistic: Statistic;
    options: StatisticOptions;
}) {
    const isManual = statistic.input_method === 'manual';

    return (
        <AppLayout breadcrumbs={[{ title: 'Statistics', href: '/statistics' }, { title: statistic.name, href: `/statistics/${statistic.id}` }, { title: 'Edit', href: `/statistics/${statistic.id}/edit` }]}>
            <Head title={`Edit ${statistic.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Edit statistic"
                    description={isManual ? 'Update the KPI definition.' : 'Adjust labels and grouping for this calculated KPI.'}
                />
                <Form action={`/statistics/${statistic.id}`} method="put" className="max-w-xl space-y-6">
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name *</Label>
                                <Input id="name" name="name" defaultValue={statistic.name} required />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="category">Category *</Label>
                                <select id="category" name="category" defaultValue={statistic.category} required className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none">
                                    {options.categories.map((category) => (
                                        <option key={category} value={category}>
                                            {category}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.category} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="group_name">Group</Label>
                                <Input id="group_name" name="group_name" defaultValue={statistic.group_name ?? ''} />
                                <InputError message={errors.group_name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="period">Period *</Label>
                                <select id="period" name="period" defaultValue={statistic.period} required className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none">
                                    {options.periods.map((period) => (
                                        <option key={period} value={period}>
                                            {period}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.period} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="value_type">Value type *</Label>
                                {!isManual && (
                                    <input type="hidden" name="value_type" value={statistic.value_type} />
                                )}
                                <select id="value_type" name="value_type" defaultValue={statistic.value_type} required disabled={!isManual} className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:opacity-60">
                                    {options.valueTypes.map((valueType) => (
                                        <option key={valueType} value={valueType}>
                                            {valueType}
                                        </option>
                                    ))}
                                </select>
                                {!isManual && (
                                    <p className="text-xs text-muted-foreground">
                                        Value type is locked for calculated statistics.
                                    </p>
                                )}
                                <InputError message={errors.value_type} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="sort_order">Sort order</Label>
                                <Input id="sort_order" name="sort_order" type="number" min="0" defaultValue={statistic.sort_order} />
                                <InputError message={errors.sort_order} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="description">Description</Label>
                                <textarea id="description" name="description" defaultValue={statistic.description ?? ''} rows={3} className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none" />
                                <InputError message={errors.description} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save changes
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href={`/statistics/${statistic.id}`}>Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="max-w-xl space-y-4 border-t pt-8">
                    <Heading
                        variant="small"
                        title="Delete statistic"
                        description="Permanently remove this statistic and all of its records."
                    />
                    <Form action={`/statistics/${statistic.id}`} method="delete">
                        {({ processing }) => (
                            <Button variant="destructive" disabled={processing}>
                                Delete statistic
                            </Button>
                        )}
                    </Form>
                </div>
            </div>
        </AppLayout>
    );
}
