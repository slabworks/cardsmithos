import { Form, Head, Link } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import SubmissionController from '@/actions/App/Http/Controllers/SubmissionController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';

type Customer = {
    id: number;
    name: string;
    contact_detail: string | null;
    platform: string | null;
    phone: string | null;
    address: string | null;
    submissions: Array<{
        id: number;
        status: string | null;
        created_at: string;
    }>;
};

export default function CustomersEdit({
    customer,
    platformOptions,
}: {
    customer: Customer;
    platformOptions: Array<{ value: string; label: string }>;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Customers', href: index() },
        { title: customer.name, href: CustomerController.edit.url(customer) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${customer.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Edit customer"
                    description="Update reusable contact details for this customer."
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
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="contact_detail">
                                    Contact detail
                                </Label>
                                <Input
                                    id="contact_detail"
                                    name="contact_detail"
                                    defaultValue={customer.contact_detail ?? ''}
                                    placeholder="Email or social media handle"
                                />
                                <InputError message={errors.contact_detail} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="platform">Platform</Label>
                                <select
                                    id="platform"
                                    name="platform"
                                    defaultValue={customer.platform ?? ''}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="">Unknown</option>
                                    {platformOptions.map((option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.platform} />
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
                                    rows={3}
                                    defaultValue={customer.address ?? ''}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.address} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save changes
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href={index()}>Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="max-w-xl space-y-3 border-t pt-6">
                    <div className="flex items-start justify-between gap-4">
                        <Heading
                            variant="small"
                            title="Submissions"
                            description="Jobs tied to this customer."
                        />
                        <Button size="sm" asChild>
                            <Link
                                href={SubmissionController.create.url({
                                    query: { customer_id: customer.id },
                                })}
                            >
                                Create submission
                            </Link>
                        </Button>
                    </div>
                    {customer.submissions.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            This customer has no submissions yet.
                        </p>
                    ) : (
                        <ul className="divide-y rounded-lg border border-sidebar-border bg-card">
                            {customer.submissions.map((submission) => (
                                <li key={submission.id}>
                                    <Link
                                        href={SubmissionController.show.url(
                                            submission,
                                        )}
                                        className="flex items-center justify-between px-4 py-3 hover:bg-muted/50"
                                    >
                                        <span className="text-sm font-medium">
                                            {new Date(
                                                submission.created_at,
                                            ).toLocaleDateString(undefined, {
                                                dateStyle: 'medium',
                                            })}
                                        </span>
                                        {submission.status && (
                                            <Badge variant="outline">
                                                {submission.status.replace(
                                                    '_',
                                                    ' ',
                                                )}
                                            </Badge>
                                        )}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
