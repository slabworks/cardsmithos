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

export default function CardsEdit({
    customer,
    card,
    statusOptions,
    conditionOptions,
}: {
    customer: { id: number; name: string };
    card: {
        id: number;
        name: string;
        work_done: string | null;
        status: string;
        condition_before: string | null;
        condition_after: string | null;
        restoration_hours: string | null;
    };
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
            title: card.name,
            href: CardController.edit.url({
                customer: customer.id,
                card: card.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${card.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading title="Edit card" description={card.name} />
                <Form
                    {...CardController.update.form({
                        customer: customer.id,
                        card: card.id,
                    })}
                    className="max-w-xl space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name *</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={card.name}
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select
                                    id="status"
                                    name="status"
                                    defaultValue={card.status}
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
                                    defaultValue={card.condition_before ?? ''}
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
                                <Label htmlFor="condition_after">
                                    Condition (after)
                                </Label>
                                <select
                                    id="condition_after"
                                    name="condition_after"
                                    defaultValue={card.condition_after ?? ''}
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
                                <InputError message={errors.condition_after} />
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
                                    defaultValue={card.restoration_hours ?? ''}
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
                                    defaultValue={card.work_done ?? ''}
                                    rows={3}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.work_done} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save changes
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
