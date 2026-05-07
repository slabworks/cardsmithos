import { Head, Link } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type PaymentShowProps = {
    payment: {
        id: number;
        amount: string;
        paid_at: string;
        method: string;
        reference: string | null;
        customer: { id: number; name: string };
    };
};

export default function PaymentsShow({ payment }: PaymentShowProps) {
    const paidAt = payment.paid_at.slice(0, 10);
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Payments', href: PaymentController.index.url() },
        { title: `Payment #${payment.id}`, href: PaymentController.show.url({ payment: payment.id }) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Payment #${payment.id}`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <Heading title={`$${Number(payment.amount).toFixed(2)}`} description={`Paid on ${paidAt}`} />
                    <Button asChild>
                        <Link href={PaymentController.edit.url({ payment: payment.id })}>Edit payment</Link>
                    </Button>
                </div>

                <div className="max-w-xl rounded-lg border border-sidebar-border bg-card p-4">
                    <dl className="grid gap-4 text-sm">
                        <div>
                            <dt className="text-muted-foreground">Customer</dt>
                            <dd className="font-medium">
                                <Link href={CustomerController.show.url(payment.customer)} className="hover:underline">
                                    {payment.customer.name}
                                </Link>
                            </dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground">Method</dt>
                            <dd>
                                <Badge variant="outline" className="capitalize">
                                    {payment.method.replace('_', ' ')}
                                </Badge>
                            </dd>
                        </div>
                        {payment.reference && (
                            <div>
                                <dt className="text-muted-foreground">Reference</dt>
                                <dd className="font-medium">{payment.reference}</dd>
                            </div>
                        )}
                    </dl>
                </div>
            </div>
        </AppLayout>
    );
}
