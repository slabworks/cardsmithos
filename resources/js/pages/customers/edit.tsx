import { Head, Link } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';

export default function CustomersEdit({
    customer,
    statusOptions,
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
        waiver_agreed: boolean | null;
        waiver_agreed_at: string | null;
    };
    statusOptions: Array<{ value: string; label: string; color: string }>;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Customers', href: index() },
        {
            title: customer.name,
            href: CustomerController.show.url(customer),
        },
        {
            title: 'Edit',
            href: CustomerController.edit.url(customer),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${customer.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Edit customer"
                    description="Update customer details"
                />
                <Form
                    {...CustomerController.update.form(customer)}
                    className="max-w-xl space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name *</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={customer.name}
                                    required
                                    autoComplete="name"
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select
                                    id="status"
                                    name="status"
                                    defaultValue={customer.status ?? ''}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="">None</option>
                                    {statusOptions.map((opt) => (
                                        <option
                                            key={opt.value}
                                            value={opt.value}
                                        >
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.status} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    defaultValue={customer.email ?? ''}
                                    autoComplete="email"
                                />
                                <InputError message={errors.email} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    defaultValue={customer.phone ?? ''}
                                />
                                <InputError message={errors.phone} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="address">Address</Label>
                                <textarea
                                    id="address"
                                    name="address"
                                    defaultValue={customer.address ?? ''}
                                    rows={2}
                                    className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.address} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="notes">Notes</Label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    defaultValue={customer.notes ?? ''}
                                    rows={3}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.notes} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="referral_source">
                                    Referral source
                                </Label>
                                <Input
                                    id="referral_source"
                                    name="referral_source"
                                    defaultValue={
                                        customer.referral_source ?? ''
                                    }
                                />
                                <InputError message={errors.referral_source} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Waiver</Label>
                                <p className="text-sm text-muted-foreground">
                                    {customer.waiver_agreed && customer.waiver_agreed_at
                                        ? `Signed on ${new Date(customer.waiver_agreed_at).toLocaleDateString(undefined, {
                                              dateStyle: 'medium',
                                          })} at ${new Date(customer.waiver_agreed_at).toLocaleTimeString(undefined, {
                                              timeStyle: 'short',
                                          })}`
                                        : 'Not signed. Waiver status is updated only when the customer signs via the waiver link.'}
                                </p>
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save changes
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link
                                        href={CustomerController.show.url(
                                            customer,
                                        )}
                                    >
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="max-w-xl space-y-4 border-t pt-8">
                    <Heading
                        variant="small"
                        title="Delete customer"
                        description="Permanently remove this customer and their data"
                    />
                    <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                        <p className="text-sm text-red-600 dark:text-red-100">
                            Once deleted, this customer cannot be recovered.
                        </p>
                        <Dialog>
                            <DialogTrigger asChild>
                                <Button variant="destructive">
                                    Delete customer
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogTitle>
                                    Delete {customer.name}?
                                </DialogTitle>
                                <DialogDescription>
                                    This will permanently delete this customer
                                    and any associated data. This cannot be
                                    undone.
                                </DialogDescription>
                                <Form
                                    {...CustomerController.destroy.form(
                                        customer,
                                    )}
                                    className="mt-4"
                                >
                                    {({ processing }) => (
                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button
                                                    type="button"
                                                    variant="secondary"
                                                >
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                disabled={processing}
                                            >
                                                Delete customer
                                            </Button>
                                        </DialogFooter>
                                    )}
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
