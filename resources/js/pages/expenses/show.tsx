import { Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import ExpenseController from '@/actions/App/Http/Controllers/ExpenseController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/expenses';
import type { BreadcrumbItem } from '@/types';

const categoryBadgeVariant: Record<string, 'default' | 'secondary' | 'outline'> =
    {
        supplies: 'default',
        shipping: 'secondary',
        equipment: 'default',
        software: 'secondary',
        marketing: 'secondary',
        other: 'outline',
    };

export default function ExpensesShow({
    expense,
}: {
    expense: {
        id: number;
        description: string;
        amount: string;
        category: string;
        occurred_at: string;
        notes: string | null;
    };
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Expenses', href: index() },
        {
            title: expense.description,
            href: ExpenseController.show.url(expense),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={expense.description} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">
                            {expense.description}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {new Date(expense.occurred_at).toLocaleDateString(
                                undefined,
                                { dateStyle: 'long' },
                            )}
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={ExpenseController.edit.url(expense)}>
                            <Pencil className="mr-2 size-4" />
                            Edit
                        </Link>
                    </Button>
                </div>

                <div className="max-w-xl space-y-4">
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <dl className="grid gap-4">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Amount
                                </dt>
                                <dd className="text-lg font-semibold tabular-nums">
                                    $
                                    {parseFloat(expense.amount).toFixed(2)}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Category
                                </dt>
                                <dd>
                                    <Badge
                                        variant={
                                            categoryBadgeVariant[
                                                expense.category
                                            ] ?? 'outline'
                                        }
                                    >
                                        {expense.category.replace('_', ' ')}
                                    </Badge>
                                </dd>
                            </div>
                            {expense.notes && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">
                                        Notes
                                    </dt>
                                    <dd className="whitespace-pre-wrap text-sm">
                                        {expense.notes}
                                    </dd>
                                </div>
                            )}
                        </dl>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
