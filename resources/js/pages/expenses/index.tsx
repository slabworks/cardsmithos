import { Head, Link } from '@inertiajs/react';
import { Plus, Receipt, Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import ExpenseController from '@/actions/App/Http/Controllers/ExpenseController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { create, index } from '@/routes/expenses';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Expenses', href: index() }];

const categoryBadgeVariant: Record<string, 'default' | 'secondary' | 'outline'> =
    {
        supplies: 'default',
        shipping: 'secondary',
        equipment: 'default',
        software: 'secondary',
        marketing: 'secondary',
        other: 'outline',
    };

export default function ExpensesIndex({
    expenses,
}: {
    expenses: Array<{
        id: number;
        description: string;
        amount: string;
        category: string;
        occurred_at: string;
    }>;
}) {
    const [search, setSearch] = useState('');
    const filtered = useMemo(() => {
        const q = search.toLowerCase();
        if (!q) return expenses;
        return expenses.filter(
            (e) =>
                e.description.toLowerCase().includes(q) ||
                e.category?.replace('_', ' ').toLowerCase().includes(q),
        );
    }, [expenses, search]);

    const total = useMemo(
        () => filtered.reduce((sum, e) => sum + parseFloat(e.amount), 0),
        [filtered],
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Expenses" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">Expenses</h1>
                        {expenses.length > 0 && (
                            <p className="text-sm text-muted-foreground">
                                Total: ${total.toFixed(2)}
                            </p>
                        )}
                    </div>
                    <Button asChild>
                        <Link href={create()}>
                            <Plus className="mr-2 size-4" />
                            Add expense
                        </Link>
                    </Button>
                </div>
                {expenses.length > 0 && (
                    <div className="relative">
                        <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Search expenses..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                )}
                <div className="rounded-lg border border-sidebar-border bg-card">
                    {expenses.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <Receipt className="size-10 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">
                                No expenses yet
                            </p>
                            <Button asChild variant="outline">
                                <Link href={create()}>
                                    Add your first expense
                                </Link>
                            </Button>
                        </div>
                    ) : filtered.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <p className="text-sm text-muted-foreground">
                                No expenses match your search
                            </p>
                        </div>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {filtered.map((expense) => (
                                <li key={expense.id}>
                                    <Link
                                        href={ExpenseController.show.url(
                                            expense,
                                        )}
                                        className="flex items-center justify-between px-4 py-3 hover:bg-muted/50"
                                    >
                                        <div className="flex flex-col gap-0.5">
                                            <span className="font-medium">
                                                {expense.description}
                                            </span>
                                            <span className="text-sm text-muted-foreground">
                                                {new Date(
                                                    expense.occurred_at,
                                                ).toLocaleDateString(
                                                    undefined,
                                                    {
                                                        dateStyle: 'medium',
                                                    },
                                                )}
                                            </span>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <Badge
                                                variant={
                                                    categoryBadgeVariant[
                                                        expense.category
                                                    ] ?? 'outline'
                                                }
                                            >
                                                {expense.category.replace(
                                                    '_',
                                                    ' ',
                                                )}
                                            </Badge>
                                            <span className="font-medium tabular-nums">
                                                ${parseFloat(expense.amount).toFixed(2)}
                                            </span>
                                        </div>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
