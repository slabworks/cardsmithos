import { Form, Head, Link } from '@inertiajs/react';
import ShipmentController from '@/actions/App/Http/Controllers/ShipmentController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

export default function ShipmentsCreate({ customers }: { customers: Array<{ id: number; name: string }> }) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Shipments', href: ShipmentController.index.url() },
        { title: 'Add shipment', href: ShipmentController.create.url() },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add shipment" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading title="Add shipment" description="Track a new shipment" />
                <Form {...ShipmentController.store.form()} className="max-w-xl space-y-6">
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="customer_id">Customer *</Label>
                                <select id="customer_id" name="customer_id" required className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none">
                                    <option value="">Select customer</option>
                                    {customers.map((customer) => (
                                        <option key={customer.id} value={customer.id}>{customer.name}</option>
                                    ))}
                                </select>
                                <InputError message={errors.customer_id} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="amount">Amount *</Label>
                                <Input id="amount" name="amount" type="number" step="0.01" min="0" required />
                                <InputError message={errors.amount} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="shipped_at">Date *</Label>
                                <Input id="shipped_at" name="shipped_at" type="date" defaultValue={new Date().toISOString().slice(0, 10)} required />
                                <InputError message={errors.shipped_at} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="tracking_number">Tracking number</Label>
                                <Input id="tracking_number" name="tracking_number" />
                                <InputError message={errors.tracking_number} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="reference">Reference</Label>
                                <Input id="reference" name="reference" />
                                <InputError message={errors.reference} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>Create shipment</Button>
                                <Button type="button" variant="outline" asChild><Link href={ShipmentController.index.url()}>Cancel</Link></Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
