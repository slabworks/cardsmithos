import { Form, Head, Link } from '@inertiajs/react';
import { Copy, FileDown, Pencil, Plus } from 'lucide-react';
import { useState } from 'react';
import CardController from '@/actions/App/Http/Controllers/CardController';
import InvoiceController from '@/actions/App/Http/Controllers/InvoiceController';
import PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import ShipmentController from '@/actions/App/Http/Controllers/ShipmentController';
import SubmissionController from '@/actions/App/Http/Controllers/SubmissionController';
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
import { index } from '@/routes/submissions';
import type { BreadcrumbItem } from '@/types';

const statusBadgeVariant: Record<string, 'default' | 'secondary' | 'outline'> =
    {
        cold_lead: 'secondary',
        warm_lead: 'secondary',
        hot_lead: 'default',
        in_progress: 'default',
        inactive: 'outline',
    };

type Submission = {
    id: number;
    status: string | null;
    notes: string | null;
    referral_source: string | null;
    lifetime_value: string | number | null;
    service_waiver: { signed_at: string | null } | null;
    customer: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
    };
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
};

export default function SubmissionsShow({
    submission,
    emailContacts,
    waiverUrl,
}: {
    submission: Submission;
    emailContacts: Array<{
        id: number;
        email: string;
        name: string | null;
        latest_subject: string | null;
        latest_snippet: string | null;
        last_message_at: string | null;
    }>;
    waiverUrl: string | null;
}) {
    const [copied, setCopied] = useState(false);
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
    const [isShipmentModalOpen, setIsShipmentModalOpen] = useState(false);
    const customer = submission.customer;
    const waiverSigned = submission.service_waiver?.signed_at != null;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Submissions', href: index() },
        {
            title: customer.name,
            href: SubmissionController.show.url(submission),
        },
    ];

    const copyWaiverUrl = () => {
        if (!waiverUrl) {
            return;
        }

        void navigator.clipboard.writeText(waiverUrl).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

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
                            {submission.status && (
                                <Badge
                                    variant={
                                        statusBadgeVariant[submission.status] ??
                                        'outline'
                                    }
                                >
                                    {submission.status.replace('_', ' ')}
                                </Badge>
                            )}
                            <Badge
                                variant={waiverSigned ? 'default' : 'outline'}
                            >
                                {waiverSigned
                                    ? 'Waiver signed'
                                    : 'Waiver not signed'}
                            </Badge>
                            {submission.lifetime_value != null && (
                                <span className="text-sm font-medium text-muted-foreground">
                                    Paid: $
                                    {Number(
                                        submission.lifetime_value,
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
                                    submission: submission.id,
                                })}
                            >
                                <FileDown className="mr-1 size-4" />
                                Generate invoice
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" asChild>
                            <Link
                                href={SubmissionController.edit.url(submission)}
                            >
                                <Pencil className="mr-1 size-4" />
                                Edit
                            </Link>
                        </Button>
                    </div>
                </div>

                {(customer.email || customer.phone || customer.address) && (
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <h2 className="mb-2 text-sm font-medium text-muted-foreground">
                            Customer
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
                            <p className="text-sm whitespace-pre-wrap">
                                {customer.address}
                            </p>
                        )}
                    </div>
                )}

                {submission.notes && (
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <h2 className="mb-2 text-sm font-medium text-muted-foreground">
                            Submission notes
                        </h2>
                        <p className="text-sm whitespace-pre-wrap">
                            {submission.notes}
                        </p>
                    </div>
                )}

                {submission.referral_source && (
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <h2 className="mb-2 text-sm font-medium text-muted-foreground">
                            Referral source
                        </h2>
                        <p className="text-sm">{submission.referral_source}</p>
                    </div>
                )}

                <div className="rounded-lg border border-sidebar-border bg-card p-4">
                    <div className="mb-3 flex items-center justify-between gap-2">
                        <h2 className="text-sm font-medium text-muted-foreground">
                            Gmail contact
                        </h2>
                        {customer.email && (
                            <Button size="sm" variant="outline" asChild>
                                <Link href="/email">Open email</Link>
                            </Button>
                        )}
                    </div>
                    {emailContacts.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No synced Gmail contact linked to this customer yet.
                        </p>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {emailContacts.map((contact) => (
                                <li
                                    key={contact.id}
                                    className="py-3 first:pt-0 last:pb-0"
                                >
                                    <Link
                                        href="/email"
                                        className="block hover:underline"
                                    >
                                        <span className="text-sm font-medium">
                                            {contact.latest_subject ||
                                                '(No subject)'}
                                        </span>
                                    </Link>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {contact.name
                                            ? `${contact.name} <${contact.email}>`
                                            : contact.email}
                                    </p>
                                    {contact.latest_snippet && (
                                        <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                            {contact.latest_snippet}
                                        </p>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <div className="rounded-lg border border-sidebar-border bg-card p-4">
                    <h2 className="mb-2 text-sm font-medium text-muted-foreground">
                        Waiver
                    </h2>
                    {waiverUrl ? (
                        <>
                            <p className="mb-2 text-sm text-muted-foreground">
                                Share this link with the customer to collect
                                their waiver for this submission.
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

                <section className="rounded-lg border border-sidebar-border bg-card">
                    <div className="flex items-center justify-between border-b border-sidebar-border px-4 py-3">
                        <h2 className="font-medium">Cards</h2>
                        <Button size="sm" asChild>
                            <Link
                                href={CardController.create.url({
                                    submission: submission.id,
                                })}
                            >
                                <Plus className="mr-1 size-4" />
                                Add card
                            </Link>
                        </Button>
                    </div>
                    {submission.cards.length === 0 ? (
                        <p className="px-4 py-6 text-sm text-muted-foreground">
                            No cards yet.
                        </p>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {submission.cards.map((card) => (
                                <li
                                    key={card.id}
                                    className="flex items-center justify-between px-4 py-3"
                                >
                                    <Link
                                        href={CardController.edit.url({
                                            submission: submission.id,
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
                        <Button
                            size="sm"
                            onClick={() => setIsPaymentModalOpen(true)}
                        >
                            <Plus className="mr-1 size-4" />
                            Add payment
                        </Button>
                    </div>
                    {submission.payments.length === 0 ? (
                        <p className="px-4 py-6 text-sm text-muted-foreground">
                            No payments yet.
                        </p>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {submission.payments.map((payment) => (
                                <li
                                    key={payment.id}
                                    className="flex items-center justify-between px-4 py-3"
                                >
                                    <div>
                                        <p className="font-medium">
                                            ${payment.amount}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {payment.paid_at?.slice(0, 10)}
                                            {payment.reference && (
                                                <span className="ml-2">
                                                    {payment.reference}
                                                </span>
                                            )}
                                        </p>
                                    </div>
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
                    {submission.shipments.length === 0 ? (
                        <p className="px-4 py-6 text-sm text-muted-foreground">
                            No shipments yet.
                        </p>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {submission.shipments.map((shipment) => (
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
                    open={isPaymentModalOpen}
                    onOpenChange={setIsPaymentModalOpen}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Add payment</DialogTitle>
                            <DialogDescription>
                                Track a payment for {customer.name}.
                            </DialogDescription>
                        </DialogHeader>
                        <Form
                            {...PaymentController.store.form({
                                submission: submission.id,
                            })}
                            resetOnSuccess
                            onSuccess={() => setIsPaymentModalOpen(false)}
                            className="space-y-4"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="payment_amount">
                                            Amount *
                                        </Label>
                                        <Input
                                            id="payment_amount"
                                            name="amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                        />
                                        <InputError message={errors.amount} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="payment_paid_at">
                                            Date *
                                        </Label>
                                        <Input
                                            id="payment_paid_at"
                                            name="paid_at"
                                            type="date"
                                            defaultValue={new Date()
                                                .toISOString()
                                                .slice(0, 10)}
                                            required
                                        />
                                        <InputError message={errors.paid_at} />
                                    </div>
                                    <DialogFooter>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() =>
                                                setIsPaymentModalOpen(false)
                                            }
                                        >
                                            Cancel
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Add payment
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>

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
                                submission: submission.id,
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
                                            Add shipment
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
