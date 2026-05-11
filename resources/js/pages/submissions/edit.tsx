import { Form, Head, Link } from '@inertiajs/react';
import SubmissionController from '@/actions/App/Http/Controllers/SubmissionController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/submissions';
import type { BreadcrumbItem } from '@/types';

type Submission = {
    id: number;
    customer_id: number;
    status: string | null;
    notes: string | null;
    referral_source: string | null;
    customer: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
    };
};

export default function SubmissionsEdit({
    submission,
    customers,
    statusOptions,
}: {
    submission: Submission;
    customers: Array<{ id: number; name: string; email: string | null }>;
    statusOptions: Array<{ value: string; label: string; color: string }>;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Submissions', href: index() },
        {
            title: submission.customer.name,
            href: SubmissionController.show.url(submission),
        },
        { title: 'Edit', href: SubmissionController.edit.url(submission) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${submission.customer.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Edit submission"
                    description={submission.customer.name}
                />
                <Form
                    {...SubmissionController.update.form(submission)}
                    className="max-w-xl space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="customer_id">Customer</Label>
                                <select
                                    id="customer_id"
                                    name="customer_id"
                                    defaultValue={submission.customer_id}
                                    required
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {customers.map((customer) => (
                                        <option
                                            key={customer.id}
                                            value={customer.id}
                                        >
                                            {customer.name}
                                            {customer.email
                                                ? ` (${customer.email})`
                                                : ''}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.customer_id} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Customer name *</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={submission.customer.name}
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    defaultValue={
                                        submission.customer.email ?? ''
                                    }
                                />
                                <InputError message={errors.email} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    defaultValue={
                                        submission.customer.phone ?? ''
                                    }
                                />
                                <InputError message={errors.phone} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="address">Address</Label>
                                <textarea
                                    id="address"
                                    name="address"
                                    rows={2}
                                    defaultValue={
                                        submission.customer.address ?? ''
                                    }
                                    className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.address} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="status">
                                    Submission status
                                </Label>
                                <select
                                    id="status"
                                    name="status"
                                    defaultValue={submission.status ?? ''}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="">None</option>
                                    {statusOptions.map((option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.status} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="notes">Submission notes</Label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    defaultValue={submission.notes ?? ''}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
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
                                        submission.referral_source ?? ''
                                    }
                                />
                                <InputError message={errors.referral_source} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save changes
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link
                                        href={SubmissionController.show.url(
                                            submission,
                                        )}
                                    >
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
