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

export default function StatisticsCreate({
    options,
}: {
    options: StatisticOptions;
}) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Statistics', href: '/statistics' }, { title: 'Add statistic', href: '/statistics/create' }]}>
            <Head title="Create statistic" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Add statistic"
                    description="Create a custom KPI you can update over time."
                />
                <Form action="/statistics" method="post" className="max-w-xl space-y-6">
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name *</Label>
                                <Input id="name" name="name" required />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="category">Category *</Label>
                                <select id="category" name="category" required className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none">
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
                                <Input id="group_name" name="group_name" />
                                <InputError message={errors.group_name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="period">Period *</Label>
                                <select id="period" name="period" required className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none">
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
                                <select id="value_type" name="value_type" required className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none">
                                    {options.valueTypes.map((valueType) => (
                                        <option key={valueType} value={valueType}>
                                            {valueType}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.value_type} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="sort_order">Sort order</Label>
                                <Input id="sort_order" name="sort_order" type="number" min="0" defaultValue="0" />
                                <InputError message={errors.sort_order} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="description">Description</Label>
                                <textarea id="description" name="description" rows={3} className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none" />
                                <InputError message={errors.description} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Create statistic
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href="/statistics">Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
