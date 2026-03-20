import { Head, Link } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import ExpenseController from '@/actions/App/Http/Controllers/ExpenseController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/expenses';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Expenses', href: index() },
    { title: 'Add expense', href: ExpenseController.create.url() },
];

export default function ExpensesCreate({
    categoryOptions,
}: {
    categoryOptions: Array<{ value: string; label: string; color: string }>;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add expense" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Add expense"
                    description="Record a new expense"
                />
                <Form
                    {...ExpenseController.store.form()}
                    className="max-w-xl space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="description">
                                    Description *
                                </Label>
                                <Input
                                    id="description"
                                    name="description"
                                    required
                                />
                                <InputError message={errors.description} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="amount">Amount *</Label>
                                <Input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                />
                                <InputError message={errors.amount} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="category">Category</Label>
                                <select
                                    id="category"
                                    name="category"
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {categoryOptions.map((opt) => (
                                        <option
                                            key={opt.value}
                                            value={opt.value}
                                        >
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.category} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="occurred_at">Date *</Label>
                                <Input
                                    id="occurred_at"
                                    name="occurred_at"
                                    type="date"
                                    defaultValue={new Date().toISOString().slice(0, 10)}
                                    required
                                />
                                <InputError message={errors.occurred_at} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="notes">Notes</Label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.notes} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Create expense
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href={index()}>Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
