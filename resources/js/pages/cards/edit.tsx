import { Head, Link } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import CardController from '@/actions/App/Http/Controllers/CardController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
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

                <div className="max-w-xl space-y-4 border-t pt-8">
                    <Heading
                        variant="small"
                        title="Delete card"
                        description="Permanently remove this card and its data"
                    />
                    <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                        <p className="text-sm text-red-600 dark:text-red-100">
                            Once deleted, this card cannot be recovered.
                        </p>
                        <Dialog>
                            <DialogTrigger asChild>
                                <Button variant="destructive">
                                    Delete card
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogTitle>Delete {card.name}?</DialogTitle>
                                <DialogDescription>
                                    This will permanently delete this card and
                                    its data. This cannot be undone.
                                </DialogDescription>
                                <Form
                                    {...CardController.destroy.form({
                                        customer: customer.id,
                                        card: card.id,
                                    })}
                                    className="mt-4"
                                >
                                    {({ processing }) => (
                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button
                                                    type="button"
                                                    variant="secondary"
                                                >
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                disabled={processing}
                                            >
                                                Delete card
                                            </Button>
                                        </DialogFooter>
                                    )}
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
