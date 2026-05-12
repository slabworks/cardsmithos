import { Form, Head, Link, router } from '@inertiajs/react';
import { FileDown, Pencil, Plus, Trash2 } from 'lucide-react';
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
        pending: 'secondary',
        in_progress: 'default',
        complete: 'default',
        cancelled: 'outline',
    };

type Submission = {
    id: number;
    status: string | null;
    notes: string | null;
    referral_source: string | null;
    lifetime_value: string | number | null;
    customer: {
        id: number;
        name: string;
        contact_detail: string | null;
        phone: string | null;
        address: string | null;
    };
    cards: Array<{
        id: number;
        name: string;
        status: string;
        estimated_fee: string | null;
    }>;
    payments: Payment[];
    shipments: Shipment[];
};

type Payment = {
    id: number;
    amount: string;
    paid_at: string;
    method: string | null;
    reference: string | null;
};

type Shipment = {
    id: number;
    amount: string;
    shipped_at: string;
    reference: string | null;
    tracking_number: string | null;
};

type Option = {
    value: string;
    label: string;
};

export default function SubmissionsShow({
    submission,
    paymentMethodOptions,
}: {
    submission: Submission;
    paymentMethodOptions: Option[];
}) {
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
    const [isShipmentModalOpen, setIsShipmentModalOpen] = useState(false);
    const [selectedPayment, setSelectedPayment] = useState<Payment | null>(
        null,
    );
    const [selectedShipment, setSelectedShipment] = useState<Shipment | null>(
        null,
    );
    const customer = submission.customer;
    const today = new Date().toISOString().slice(0, 10);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Submissions', href: index() },
        {
            title: customer.name,
            href: SubmissionController.show.url(submission),
        },
    ];

    const openPaymentModal = (payment: Payment | null = null) => {
        setSelectedPayment(payment);
        setIsPaymentModalOpen(true);
    };

    const openShipmentModal = (shipment: Shipment | null = null) => {
        setSelectedShipment(shipment);
        setIsShipmentModalOpen(true);
    };

    const closePaymentModal = () => {
        setIsPaymentModalOpen(false);
        setSelectedPayment(null);
    };

    const closeShipmentModal = () => {
        setIsShipmentModalOpen(false);
        setSelectedShipment(null);
    };

    const deletePayment = () => {
        if (!selectedPayment || !window.confirm('Delete this payment?')) {
            return;
        }

        router.delete(
            PaymentController.destroy.url({
                submission: submission.id,
                payment: selectedPayment.id,
            }),
            { onSuccess: closePaymentModal },
        );
    };

    const deleteShipment = () => {
        if (!selectedShipment || !window.confirm('Delete this shipment?')) {
            return;
        }

        router.delete(
            ShipmentController.destroy.url({
                submission: submission.id,
                shipment: selectedShipment.id,
            }),
            { onSuccess: closeShipmentModal },
        );
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

                {(customer.contact_detail ||
                    customer.phone ||
                    customer.address) && (
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <h2 className="mb-2 text-sm font-medium text-muted-foreground">
                            Customer
                        </h2>
                        {customer.contact_detail && (
                            <p className="text-sm">{customer.contact_detail}</p>
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
                        <Button size="sm" onClick={() => openPaymentModal()}>
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
                                            {payment.method && (
                                                <span className="ml-2 capitalize">
                                                    {payment.method.replace(
                                                        '_',
                                                        ' ',
                                                    )}
                                                </span>
                                            )}
                                        </p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            openPaymentModal(payment)
                                        }
                                    >
                                        <Pencil className="mr-1 size-4" />
                                        Edit
                                    </Button>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section className="rounded-lg border border-sidebar-border bg-card">
                    <div className="flex items-center justify-between border-b border-sidebar-border px-4 py-3">
                        <h2 className="font-medium">Shipments</h2>
                        <Button size="sm" onClick={() => openShipmentModal()}>
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
                                            {shipment.tracking_number && (
                                                <span className="ml-2">
                                                    {shipment.tracking_number}
                                                </span>
                                            )}
                                        </p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            openShipmentModal(shipment)
                                        }
                                    >
                                        <Pencil className="mr-1 size-4" />
                                        Edit
                                    </Button>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <Dialog
                    open={isPaymentModalOpen}
                    onOpenChange={(open) => {
                        if (open) {
                            setIsPaymentModalOpen(true);
                        } else {
                            closePaymentModal();
                        }
                    }}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>
                                {selectedPayment
                                    ? 'Edit payment'
                                    : 'Add payment'}
                            </DialogTitle>
                            <DialogDescription>
                                {selectedPayment
                                    ? `Update payment details for ${customer.name}.`
                                    : `Track a payment for ${customer.name}.`}
                            </DialogDescription>
                        </DialogHeader>
                        <Form
                            key={selectedPayment?.id ?? 'new-payment'}
                            {...(selectedPayment
                                ? PaymentController.update.form({
                                      submission: submission.id,
                                      payment: selectedPayment.id,
                                  })
                                : PaymentController.store.form({
                                      submission: submission.id,
                                  }))}
                            resetOnSuccess={!selectedPayment}
                            onSuccess={closePaymentModal}
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
                                            defaultValue={
                                                selectedPayment?.amount ?? ''
                                            }
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
                                            defaultValue={
                                                selectedPayment?.paid_at?.slice(
                                                    0,
                                                    10,
                                                ) ?? today
                                            }
                                            required
                                        />
                                        <InputError message={errors.paid_at} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="payment_method">
                                            Method
                                        </Label>
                                        <select
                                            id="payment_method"
                                            name="method"
                                            defaultValue={
                                                selectedPayment?.method ?? ''
                                            }
                                            className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm ring-offset-background transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="">No method</option>
                                            {paymentMethodOptions.map(
                                                (option) => (
                                                    <option
                                                        key={option.value}
                                                        value={option.value}
                                                    >
                                                        {option.label}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                        <InputError message={errors.method} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="payment_reference">
                                            Reference
                                        </Label>
                                        <Input
                                            id="payment_reference"
                                            name="reference"
                                            defaultValue={
                                                selectedPayment?.reference ?? ''
                                            }
                                        />
                                        <InputError
                                            message={errors.reference}
                                        />
                                    </div>
                                    <DialogFooter>
                                        {selectedPayment && (
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                onClick={deletePayment}
                                            >
                                                <Trash2 className="mr-1 size-4" />
                                                Delete
                                            </Button>
                                        )}
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={closePaymentModal}
                                        >
                                            Cancel
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {selectedPayment
                                                ? 'Save payment'
                                                : 'Add payment'}
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>

                <Dialog
                    open={isShipmentModalOpen}
                    onOpenChange={(open) => {
                        if (open) {
                            setIsShipmentModalOpen(true);
                        } else {
                            closeShipmentModal();
                        }
                    }}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>
                                {selectedShipment
                                    ? 'Edit shipment'
                                    : 'Add shipment'}
                            </DialogTitle>
                            <DialogDescription>
                                {selectedShipment
                                    ? `Update shipment details for ${customer.name}.`
                                    : `Track return shipping fees for ${customer.name}.`}
                            </DialogDescription>
                        </DialogHeader>
                        <Form
                            key={selectedShipment?.id ?? 'new-shipment'}
                            {...(selectedShipment
                                ? ShipmentController.update.form({
                                      submission: submission.id,
                                      shipment: selectedShipment.id,
                                  })
                                : ShipmentController.store.form({
                                      submission: submission.id,
                                  }))}
                            resetOnSuccess={!selectedShipment}
                            onSuccess={closeShipmentModal}
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
                                            defaultValue={
                                                selectedShipment?.amount ?? ''
                                            }
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
                                            defaultValue={
                                                selectedShipment?.shipped_at?.slice(
                                                    0,
                                                    10,
                                                ) ?? today
                                            }
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
                                            defaultValue={
                                                selectedShipment?.tracking_number ??
                                                ''
                                            }
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
                                            defaultValue={
                                                selectedShipment?.reference ??
                                                ''
                                            }
                                        />
                                        <InputError
                                            message={errors.reference}
                                        />
                                    </div>
                                    <DialogFooter>
                                        {selectedShipment && (
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                onClick={deleteShipment}
                                            >
                                                <Trash2 className="mr-1 size-4" />
                                                Delete
                                            </Button>
                                        )}
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={closeShipmentModal}
                                        >
                                            Cancel
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {selectedShipment
                                                ? 'Save shipment'
                                                : 'Add shipment'}
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
