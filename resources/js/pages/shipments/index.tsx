import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import ShipmentController from '@/actions/App/Http/Controllers/ShipmentController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type ShipmentItem = {
    id: number;
    amount: string;
    shipped_at: string;
    tracking_number: string | null;
    reference: string | null;
    customer: { id: number; name: string };
};

export default function ShipmentsIndex({ shipments }: { shipments: ShipmentItem[] }) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Shipments', href: ShipmentController.index.url() }];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Shipments" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between gap-4">
                    <Heading title="Shipments" description="All customer shipments" />
                    <Button asChild>
                        <Link href={ShipmentController.create.url()}>
                            <Plus className="mr-1 size-4" />
                            Add shipment
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
                                <th className="px-4 py-3">Tracking</th>
                                <th className="px-4 py-3">Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            {shipments.length === 0 ? (
                                <tr>
                                    <td className="px-4 py-6 text-muted-foreground" colSpan={5}>
                                        No shipments yet.
                                    </td>
                                </tr>
                            ) : (
                                shipments.map((shipment) => (
                                    <tr key={shipment.id} className="border-b border-sidebar-border last:border-0">
                                        <td className="px-4 py-3">
                                            <Link href={`/customers/${shipment.customer.id}`} className="hover:underline">
                                                {shipment.customer.name}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3 font-medium">${Number(shipment.amount).toFixed(2)}</td>
                                        <td className="px-4 py-3">{shipment.shipped_at.slice(0, 10)}</td>
                                        <td className="px-4 py-3">{shipment.tracking_number ?? ' - '}</td>
                                        <td className="px-4 py-3">{shipment.reference ?? ' - '}</td>
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
