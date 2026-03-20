import { Head, Link } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import ExpenseController from '@/actions/App/Http/Controllers/ExpenseController';
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
import { index } from '@/routes/expenses';
import type { BreadcrumbItem } from '@/types';

export default function ExpensesEdit({
    expense,
    categoryOptions,
}: {
    expense: {
        id: number;
        description: string;
        amount: string;
        category: string;
        occurred_at: string;
        notes: string | null;
    };
    categoryOptions: Array<{ value: string; label: string; color: string }>;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Expenses', href: index() },
        {
            title: expense.description,
            href: ExpenseController.show.url(expense),
        },
        {
            title: 'Edit',
            href: ExpenseController.edit.url(expense),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${expense.description}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Edit expense"
                    description="Update expense details"
                />
                <Form
                    {...ExpenseController.update.form(expense)}
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
                                    defaultValue={expense.description}
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
                                    defaultValue={expense.amount}
                                    required
                                />
                                <InputError message={errors.amount} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="category">Category</Label>
                                <select
                                    id="category"
                                    name="category"
                                    defaultValue={expense.category}
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
                                    defaultValue={expense.occurred_at.slice(0, 10)}
                                    required
                                />
                                <InputError message={errors.occurred_at} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="notes">Notes</Label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    defaultValue={expense.notes ?? ''}
                                    rows={3}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.notes} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save changes
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link
                                        href={ExpenseController.show.url(
                                            expense,
                                        )}
                                    >
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
                        title="Delete expense"
                        description="Permanently remove this expense"
                    />
                    <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                        <p className="text-sm text-red-600 dark:text-red-100">
                            Once deleted, this expense cannot be recovered.
                        </p>
                        <Dialog>
                            <DialogTrigger asChild>
                                <Button variant="destructive">
                                    Delete expense
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogTitle>
                                    Delete this expense?
                                </DialogTitle>
                                <DialogDescription>
                                    This will permanently delete this expense.
                                    This cannot be undone.
                                </DialogDescription>
                                <Form
                                    {...ExpenseController.destroy.form(expense)}
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
                                                Delete expense
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
