import { Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';

const statusBadgeVariant: Record<string, 'default' | 'secondary' | 'outline'> =
    {
        cold_lead: 'secondary',
        warm_lead: 'secondary',
        hot_lead: 'default',
        in_progress: 'default',
        good_client: 'default',
        inactive: 'outline',
    };

export default function CustomersShow({
    customer,
}: {
    customer: {
        id: number;
        name: string;
        status: string | null;
        email: string | null;
        phone: string | null;
        address: string | null;
        notes: string | null;
        referral_source: string | null;
    };
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Customers', href: index() },
        {
            title: customer.name,
            href: CustomerController.show.url(customer),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={customer.name} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold">
                            {customer.name}
                        </h1>
                        {customer.status && (
                            <Badge
                                variant={
                                    statusBadgeVariant[customer.status] ??
                                    'outline'
                                }
                                className="mt-1"
                            >
                                {customer.status.replace('_', ' ')}
                            </Badge>
                        )}
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={CustomerController.edit.url(customer)}>
                                <Pencil className="mr-1 size-4" />
                                Edit
                            </Link>
                        </Button>
                    </div>
                </div>

                {(customer.email || customer.phone || customer.address) && (
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <h2 className="mb-2 text-sm font-medium text-muted-foreground">
                            Contact
                        </h2>
                        {customer.email && (
                            <p>
                                <a
                                    href={`mailto:${customer.email}`}
                                    className="text-primary underline"
                                >
                                    {customer.email}
                                </a>
                            </p>
                        )}
                        {customer.phone && (
                            <p>
                                <a
                                    href={`tel:${customer.phone}`}
                                    className="text-primary underline"
                                >
                                    {customer.phone}
                                </a>
                            </p>
                        )}
                        {customer.address && (
                            <p className="text-sm">{customer.address}</p>
                        )}
                    </div>
                )}

                {customer.notes && (
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <h2 className="mb-2 text-sm font-medium text-muted-foreground">
                            Notes
                        </h2>
                        <p className="text-sm whitespace-pre-wrap">
                            {customer.notes}
                        </p>
                    </div>
                )}

                {customer.referral_source && (
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <h2 className="mb-2 text-sm font-medium text-muted-foreground">
                            Referral source
                        </h2>
                        <p className="text-sm">{customer.referral_source}</p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
