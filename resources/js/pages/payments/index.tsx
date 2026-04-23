import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type PaymentItem = {
    id: number;
    amount: string;
    paid_at: string;
    customer: { id: number; name: string };
};

export default function PaymentsIndex({ payments }: { payments: PaymentItem[] }) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Payments', href: PaymentController.index.url() }];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Payments" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between gap-4">
                    <Heading title="Payments" description="All customer payments" />
                    <Button asChild>
                        <Link href={PaymentController.create.url()}>
                            <Plus className="mr-1 size-4" />
                            Add payment
                        </Link>
                    </Button>
                </div>

                <div className="overflow-hidden rounded-lg border border-sidebar-border bg-card">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b border-sidebar-border bg-muted/40 text-muted-foreground">
                            <tr>
                                <th className="px-4 py-3">Customer</th>
                                <th className="px-4 py-3">Amount</th>
                                <th className="px-4 py-3">Date</th>
                                <th className="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody>
                            {payments.length === 0 ? (
                                <tr>
                                    <td className="px-4 py-6 text-muted-foreground" colSpan={4}>
                                        No payments yet.
                                    </td>
                                </tr>
                            ) : (
                                payments.map((payment) => (
                                    <tr key={payment.id} className="border-b border-sidebar-border last:border-0">
                                        <td className="px-4 py-3">
                                            <Link href={`/customers/${payment.customer.id}`} className="hover:underline">
                                                {payment.customer.name}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3 font-medium">${Number(payment.amount).toFixed(2)}</td>
                                        <td className="px-4 py-3">{payment.paid_at.slice(0, 10)}</td>
                                        <td className="px-4 py-3 text-right">
                                            <Badge variant="outline">Tracked</Badge>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
