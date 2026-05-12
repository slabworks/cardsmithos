import { Form, Head, Link } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { useState } from 'react';
import SubmissionController from '@/actions/App/Http/Controllers/SubmissionController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { create as createCustomer } from '@/routes/customers';
import { index } from '@/routes/submissions';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Submissions', href: index() },
    { title: 'Add submission', href: SubmissionController.create.url() },
];

const formatPlatform = (platform: string) =>
    platform === 'x_twitter'
        ? 'X / Twitter'
        : platform
              .split('_')
              .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
              .join(' ');

export default function SubmissionsCreate({
    customers,
    selectedCustomerId,
    statusOptions,
    referralSourceOptions,
}: {
    customers: Array<{
        id: number;
        name: string;
        contact_detail: string | null;
        platform: string | null;
    }>;
    selectedCustomerId: number | null;
    statusOptions: Array<{ value: string; label: string; color: string }>;
    referralSourceOptions: Array<{ value: string; label: string }>;
}) {
    const initialSelectedCustomer = customers.find(
        (customer) => customer.id === selectedCustomerId,
    );
    const [selectedCustomerIdValue, setSelectedCustomerIdValue] = useState(
        initialSelectedCustomer ? String(initialSelectedCustomer.id) : '',
    );
    const [customerSearch, setCustomerSearch] = useState(
        initialSelectedCustomer
            ? initialSelectedCustomer.contact_detail
                ? `${initialSelectedCustomer.name} (${initialSelectedCustomer.contact_detail})`
                : initialSelectedCustomer.name
            : '',
    );
    const [isCustomerSearchOpen, setIsCustomerSearchOpen] = useState(false);
    const selectedCustomer = customers.find(
        (customer) => String(customer.id) === selectedCustomerIdValue,
    );
    const filteredCustomers = customers.filter((customer) => {
        const query = customerSearch.trim().toLowerCase();

        if (query === '') {
            return true;
        }

        return (
            customer.name.toLowerCase().includes(query) ||
            customer.contact_detail?.toLowerCase().includes(query) ||
            customer.platform?.replace('_', ' ').includes(query)
        );
    });

    const selectCustomer = (customer: (typeof customers)[number]) => {
        setSelectedCustomerIdValue(String(customer.id));
        setCustomerSearch(
            customer.contact_detail
                ? `${customer.name} (${customer.contact_detail})`
                : customer.name,
        );
        setIsCustomerSearchOpen(false);
    };

    const clearSelectedCustomer = () => {
        setSelectedCustomerIdValue('');
        setCustomerSearch('');
        setIsCustomerSearchOpen(false);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add submission" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Add submission"
                    description="Create a new card repair submission for an existing customer."
                />
                {customers.length === 0 ? (
                    <div className="max-w-xl rounded-lg border border-sidebar-border bg-card p-4">
                        <p className="text-sm text-muted-foreground">
                            Add a customer before creating a submission.
                        </p>
                        <Button asChild className="mt-4">
                            <Link href={createCustomer()}>Add customer</Link>
                        </Button>
                    </div>
                ) : (
                    <Form
                        {...SubmissionController.store.form()}
                        className="max-w-xl space-y-6"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="customer_id">
                                        Customer *
                                    </Label>
                                    <input
                                        type="hidden"
                                        name="customer_id"
                                        value={selectedCustomerIdValue}
                                    />
                                    <div className="relative">
                                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                        <Input
                                            id="customer_id"
                                            value={customerSearch}
                                            onChange={(event) => {
                                                setCustomerSearch(
                                                    event.target.value,
                                                );
                                                setSelectedCustomerIdValue('');
                                                setIsCustomerSearchOpen(true);
                                            }}
                                            onFocus={() =>
                                                setIsCustomerSearchOpen(true)
                                            }
                                            placeholder="Search customers by name or contact detail..."
                                            className="pr-10 pl-9"
                                            autoComplete="off"
                                        />
                                        {(customerSearch ||
                                            selectedCustomer) && (
                                            <button
                                                type="button"
                                                onClick={clearSelectedCustomer}
                                                className="absolute top-1/2 right-2 rounded-sm p-1 text-muted-foreground hover:text-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                                aria-label="Clear selected customer"
                                            >
                                                <X className="size-4" />
                                            </button>
                                        )}
                                        {isCustomerSearchOpen && (
                                            <div className="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-md border border-input bg-popover p-1 shadow-md">
                                                {filteredCustomers.length ===
                                                0 ? (
                                                    <p className="px-3 py-2 text-sm text-muted-foreground">
                                                        No matching customers
                                                    </p>
                                                ) : (
                                                    filteredCustomers.map(
                                                        (customer) => (
                                                            <button
                                                                key={
                                                                    customer.id
                                                                }
                                                                type="button"
                                                                onClick={() =>
                                                                    selectCustomer(
                                                                        customer,
                                                                    )
                                                                }
                                                                className="w-full rounded-sm px-3 py-2 text-left text-sm hover:bg-muted focus-visible:bg-muted focus-visible:outline-none"
                                                            >
                                                                <span className="font-medium">
                                                                    {
                                                                        customer.name
                                                                    }
                                                                </span>
                                                                {customer.contact_detail && (
                                                                    <span className="block text-xs text-muted-foreground">
                                                                        {
                                                                            customer.contact_detail
                                                                        }
                                                                    </span>
                                                                )}
                                                                {customer.platform && (
                                                                    <span className="block text-xs text-muted-foreground">
                                                                        {formatPlatform(
                                                                            customer.platform,
                                                                        )}
                                                                    </span>
                                                                )}
                                                            </button>
                                                        ),
                                                    )
                                                )}
                                            </div>
                                        )}
                                    </div>
                                    <InputError message={errors.customer_id} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="status">
                                        Submission status
                                    </Label>
                                    <select
                                        id="status"
                                        name="status"
                                        defaultValue="pending"
                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    >
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
                                    <Label htmlFor="notes">
                                        Submission notes
                                    </Label>
                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows={3}
                                        className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    />
                                    <InputError message={errors.notes} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="referral_source">
                                        Referral source
                                    </Label>
                                    <select
                                        id="referral_source"
                                        name="referral_source"
                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        <option value="">None</option>
                                        {referralSourceOptions.map((option) => (
                                            <option
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.referral_source}
                                    />
                                </div>
                                <div className="flex gap-2">
                                    <Button type="submit" disabled={processing}>
                                        Create submission
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        asChild
                                    >
                                        <Link href={index()}>Cancel</Link>
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                )}
            </div>
        </AppLayout>
    );
}
