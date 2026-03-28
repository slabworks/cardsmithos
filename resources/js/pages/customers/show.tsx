import { Form, Head, Link } from '@inertiajs/react';
import { Copy, FileDown, Mail, Pencil, Plus } from 'lucide-react';
import { useState } from 'react';
import CardController from '@/actions/App/Http/Controllers/CardController';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import ShipmentController from '@/actions/App/Http/Controllers/ShipmentController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/customers';
import { index as emailsIndex } from '@/routes/emails';
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

type EmailItem = {
    id: number;
    gmail_thread_id: string;
    direction: 'inbound' | 'outbound';
    from_address: string;
    from_name: string | null;
    subject: string | null;
    snippet: string | null;
    is_read: boolean;
    received_at: string;
};

export default function CustomersShow({
    customer,
    waiverUrl,
    recentEmails,
}: {
    recentEmails: EmailItem[];
    customer: {
        id: number;
        name: string;
        status: string | null;
        email: string | null;
        phone: string | null;
        address: string | null;
        notes: string | null;
        referral_source: string | null;
        waiver_agreed: boolean | null;
        cards: Array<{
            id: number;
            name: string;
            status: string;
            estimated_fee: string | null;
        }>;
        payments: Array<{
            id: number;
            amount: string;
            paid_at: string;
            method: string;
            reference: string | null;
        }>;
        shipments: Array<{
            id: number;
            amount: string;
            shipped_at: string;
            reference: string | null;
            tracking_number: string | null;
        }>;
        lifetime_value: string | number | null;
    };
    waiverUrl: string | null;
}) {
    const [copied, setCopied] = useState(false);
    const [isShipmentModalOpen, setIsShipmentModalOpen] = useState(false);

    const copyWaiverUrl = () => {
        if (!waiverUrl) {
            return;
        }

        void navigator.clipboard.writeText(waiverUrl).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };
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
                        <div className="mt-1 flex flex-wrap items-center gap-2">
                            {customer.status && (
                                <Badge
                                    variant={
                                        statusBadgeVariant[customer.status] ??
                                        'outline'
                                    }
                                >
                                    {customer.status.replace('_', ' ')}
                                </Badge>
                            )}
                            <Badge
                                variant={
                                    customer.waiver_agreed
                                        ? 'default'
                                        : 'outline'
                                }
                            >
                                {customer.waiver_agreed
                                    ? 'Waiver signed'
                                    : 'Waiver not signed'}
                            </Badge>
                            {customer.lifetime_value != null && (
                                <span className="text-sm font-medium text-muted-foreground">
                                    Lifetime value: $
                                    {Number(
                                        customer.lifetime_value,
                                    ).toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    })}
                                </span>
                            )}
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm" asChild>
                            <Link
                                href={InvoiceController.create.url({
                                    customer: customer.id,
                                })}
                            >
                                <FileDown className="mr-1 size-4" />
                                Generate invoice
                            </Link>
                        </Button>
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

                <div className="rounded-lg border border-sidebar-border bg-card p-4">
                    <h2 className="mb-2 text-sm font-medium text-muted-foreground">
                        Waiver
                    </h2>
                    {waiverUrl ? (
                        <>
                            <p className="mb-2 text-sm text-muted-foreground">
                                Share this link with the customer to collect
                                their waiver for card repair services.
                            </p>
                            <div className="flex flex-wrap items-center gap-2">
                                <Input
                                    readOnly
                                    value={waiverUrl}
                                    className="font-mono text-sm"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={copyWaiverUrl}
                                >
                                    <Copy className="mr-1 size-4" />
                                    {copied ? 'Copied' : 'Copy'}
                                </Button>
                            </div>
                        </>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            Waiver signed.
                        </p>
                    )}
                </div>

                {recentEmails.length > 0 && (
                    <section className="rounded-lg border border-sidebar-border bg-card">
                        <div className="flex items-center justify-between border-b border-sidebar-border px-4 py-3">
                            <h2 className="font-medium">Recent emails</h2>
                            <Button size="sm" variant="outline" asChild>
                                <Link
                                    href={emailsIndex.url({
                                        query: {
                                            customer_id: customer.id.toString(),
                                        },
                                    })}
                                >
                                    <Mail className="mr-1 size-4" />
                                    View all
                                </Link>
                            </Button>
                        </div>
                        <ul className="divide-y divide-sidebar-border">
                            {recentEmails.map((email) => (
                                <li key={email.id}>
                                    <Link
                                        href={emailsIndex.url({
                                            query: {
                                                thread_id:
                                                    email.gmail_thread_id,
                                                customer_id:
                                                    customer.id.toString(),
                                            },
                                        })}
                                        className={`flex items-center justify-between px-4 py-3 hover:bg-muted/50 ${!email.is_read ? 'font-semibold' : ''}`}
                                    >
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm">
                                                {email.from_name ||
                                                    email.from_address}
                                            </p>
                                            <p className="truncate text-sm text-muted-foreground">
                                                {email.subject ||
                                                    '(no subject)'}
                                            </p>
                                        </div>
                                        <span className="shrink-0 text-xs text-muted-foreground">
                                            {new Date(
                                                email.received_at,
                                            ).toLocaleDateString(undefined, {
                                                dateStyle: 'medium',
                                            })}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                <section className="rounded-lg border border-sidebar-border bg-card">
                    <div className="flex items-center justify-between border-b border-sidebar-border px-4 py-3">
                        <h2 className="font-medium">Cards</h2>
                        <Button size="sm" asChild>
                            <Link
                                href={CardController.create.url({
                                    customer: customer.id,
                                })}
                            >
                                <Plus className="mr-1 size-4" />
                                Add card
                            </Link>
                        </Button>
                    </div>
                    {customer.cards.length === 0 ? (
                        <p className="px-4 py-6 text-sm text-muted-foreground">
                            No cards yet.
                        </p>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {customer.cards.map((card) => (
                                <li
                                    key={card.id}
                                    className="flex items-center justify-between px-4 py-3"
                                >
                                    <Link
                                        href={CardController.edit.url({
                                            customer: customer.id,
                                            card: card.id,
                                        })}
                                        className="hover:underline"
                                    >
                                        {card.name}
                                    </Link>
                                    <Badge variant="outline">
                                        {card.status.replace('_', ' ')}
                                    </Badge>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section className="rounded-lg border border-sidebar-border bg-card">
                    <div className="flex items-center justify-between border-b border-sidebar-border px-4 py-3">
                        <h2 className="font-medium">Payments</h2>
                        <Button size="sm" asChild>
                            <Link
                                href={PaymentController.create.url({
                                    customer: customer.id,
                                })}
                            >
                                <Plus className="mr-1 size-4" />
                                Add payment
                            </Link>
                        </Button>
                    </div>
                    {customer.payments.length === 0 ? (
                        <p className="px-4 py-6 text-sm text-muted-foreground">
                            No payments yet.
                        </p>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {customer.payments.map((payment) => (
                                <li
                                    key={payment.id}
                                    className="flex items-center justify-between px-4 py-3"
                                >
                                    <Link
                                        href={PaymentController.edit.url({
                                            customer: customer.id,
                                            payment: payment.id,
                                        })}
                                        className="hover:underline"
                                    >
                                        ${payment.amount}
                                        {payment.paid_at && (
                                            <span className="ml-2 text-muted-foreground">
                                                {payment.paid_at.slice(0, 10)}
                                            </span>
                                        )}
                                        {payment.reference && (
                                            <span className="ml-2 text-muted-foreground">
                                                {payment.reference}
                                            </span>
                                        )}
                                    </Link>
                                    <span className="text-sm text-muted-foreground capitalize">
                                        {payment.method?.replace('_', ' ')}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
                <section className="rounded-lg border border-sidebar-border bg-card">
                    <div className="flex items-center justify-between border-b border-sidebar-border px-4 py-3">
                        <h2 className="font-medium">Shipments</h2>
                        <Button
                            size="sm"
                            onClick={() => setIsShipmentModalOpen(true)}
                        >
                            <Plus className="mr-1 size-4" />
                            Add shipment
                        </Button>
                    </div>
                    {customer.shipments.length === 0 ? (
                        <p className="px-4 py-6 text-sm text-muted-foreground">
                            No shipments yet.
                        </p>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {customer.shipments.map((shipment) => (
                                <li
                                    key={shipment.id}
                                    className="flex items-center justify-between px-4 py-3"
                                >
                                    <div>
                                        <p className="font-medium">
                                            ${shipment.amount}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {shipment.shipped_at?.slice(0, 10)}
                                            {shipment.reference && (
                                                <span className="ml-2">
                                                    {shipment.reference}
                                                </span>
                                            )}
                                        </p>
                                    </div>
                                    {shipment.tracking_number && (
                                        <span className="text-sm text-muted-foreground">
                                            {shipment.tracking_number}
                                        </span>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <Dialog
                    open={isShipmentModalOpen}
                    onOpenChange={setIsShipmentModalOpen}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Add shipment</DialogTitle>
                            <DialogDescription>
                                Track return shipping fees for {customer.name}.
                            </DialogDescription>
                        </DialogHeader>
                        <Form
                            {...ShipmentController.store.form({
                                customer: customer.id,
                            })}
                            resetOnSuccess
                            onSuccess={() => setIsShipmentModalOpen(false)}
                            className="space-y-4"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="shipment_amount">
                                            Amount *
                                        </Label>
                                        <Input
                                            id="shipment_amount"
                                            name="amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                        />
                                        <InputError message={errors.amount} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="shipment_shipped_at">
                                            Shipment date *
                                        </Label>
                                        <Input
                                            id="shipment_shipped_at"
                                            name="shipped_at"
                                            type="date"
                                            defaultValue={new Date()
                                                .toISOString()
                                                .slice(0, 10)}
                                            required
                                        />
                                        <InputError
                                            message={errors.shipped_at}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="shipment_tracking_number">
                                            Tracking number
                                        </Label>
                                        <Input
                                            id="shipment_tracking_number"
                                            name="tracking_number"
                                        />
                                        <InputError
                                            message={errors.tracking_number}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="shipment_reference">
                                            Reference
                                        </Label>
                                        <Input
                                            id="shipment_reference"
                                            name="reference"
                                        />
                                        <InputError
                                            message={errors.reference}
                                        />
                                    </div>
                                    <DialogFooter>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() =>
                                                setIsShipmentModalOpen(false)
                                            }
                                        >
                                            Cancel
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Save shipment
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
