import { Head, Link } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import ShipmentController from '@/actions/App/Http/Controllers/ShipmentController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type ShipmentShowProps = {
    shipment: {
        id: number;
        amount: string;
        shipped_at: string;
        tracking_number: string | null;
        reference: string | null;
        customer: { id: number; name: string };
    };
};

export default function ShipmentsShow({ shipment }: ShipmentShowProps) {
    const shippedAt = shipment.shipped_at.slice(0, 10);
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Shipments', href: ShipmentController.index.url() },
        { title: `Shipment #${shipment.id}`, href: ShipmentController.show.url({ shipment: shipment.id }) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Shipment #${shipment.id}`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <Heading title={`$${Number(shipment.amount).toFixed(2)}`} description={`Shipped on ${shippedAt}`} />
                    <Button asChild>
                        <Link href={ShipmentController.edit.url({ shipment: shipment.id })}>Edit shipment</Link>
                    </Button>
                </div>

                <div className="max-w-xl rounded-lg border border-sidebar-border bg-card p-4">
                    <dl className="grid gap-4 text-sm">
                        <div>
                            <dt className="text-muted-foreground">Customer</dt>
                            <dd className="font-medium">
                                <Link href={CustomerController.show.url(shipment.customer)} className="hover:underline">
                                    {shipment.customer.name}
                                </Link>
                            </dd>
                        </div>
                        {shipment.tracking_number && (
                            <div>
                                <dt className="text-muted-foreground">Tracking number</dt>
                                <dd className="font-medium">{shipment.tracking_number}</dd>
                            </div>
                        )}
                        {shipment.reference && (
                            <div>
                                <dt className="text-muted-foreground">Reference</dt>
                                <dd className="font-medium">{shipment.reference}</dd>
                            </div>
                        )}
                    </dl>
                </div>
            </div>
        </AppLayout>
    );
}
