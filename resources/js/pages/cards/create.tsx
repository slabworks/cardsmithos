import { Head, Link } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import CardController from '@/actions/App/Http/Controllers/CardController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';

export default function CardsCreate({
    customer,
    statusOptions,
    conditionOptions,
}: {
    customer: { id: number; name: string };
    statusOptions: Array<{ value: string; label: string; color: string }>;
    conditionOptions: Array<{ value: string; label: string; color: string }>;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Customers', href: index() },
        {
            title: customer.name,
            href: `/customers/${customer.id}`,
        },
        {
            title: 'Add card',
            href: CardController.create.url({ customer: customer.id }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add card" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Add card"
                    description={`New card for ${customer.name}`}
                />
                <Form
                    {...CardController.store.form({ customer: customer.id })}
                    className="max-w-xl space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name *</Label>
                                <Input id="name" name="name" required />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select
                                    id="status"
                                    name="status"
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {statusOptions.map((opt) => (
                                        <option
                                            key={opt.value}
                                            value={opt.value}
                                        >
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.status} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="condition_before">
                                    Condition (before)
                                </Label>
                                <select
                                    id="condition_before"
                                    name="condition_before"
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="">None</option>
                                    {conditionOptions.map((opt) => (
                                        <option
                                            key={opt.value}
                                            value={opt.value}
                                        >
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.condition_before} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="restoration_hours">
                                    Restoration hours
                                </Label>
                                <Input
                                    id="restoration_hours"
                                    name="restoration_hours"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                />
                                <InputError
                                    message={errors.restoration_hours}
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="work_done">Work done</Label>
                                <textarea
                                    id="work_done"
                                    name="work_done"
                                    rows={3}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.work_done} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Create card
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href={`/customers/${customer.id}`}>
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
