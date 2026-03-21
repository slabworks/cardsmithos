import { Head, Link } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import InquiryController from '@/actions/App/Http/Controllers/InquiryController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/inquiries';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inquiries', href: index() },
    { title: 'Add inquiry', href: InquiryController.create.url() },
];

type CustomerOption = { id: number; name: string };

export default function InquiriesCreate({
    communicationMethodOptions,
    customerOptions,
}: {
    communicationMethodOptions: Array<{
        value: string;
        label: string;
        color: string;
    }>;
    customerOptions: CustomerOption[];
}) {
    const [customerSearch, setCustomerSearch] = useState('');
    const [selectedCustomerId, setSelectedCustomerId] = useState<number | ''>(
        '',
    );
    const [showDropdown, setShowDropdown] = useState(false);

    const filteredCustomers = useMemo(() => {
        if (!customerSearch) {
            return customerOptions;
        }

        const q = customerSearch.toLowerCase();

        return customerOptions.filter((c) => c.name.toLowerCase().includes(q));
    }, [customerOptions, customerSearch]);

    const selectedCustomer = customerOptions.find(
        (c) => c.id === selectedCustomerId,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add inquiry" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Add inquiry"
                    description="Record a new lead inquiry"
                />
                <Form
                    action={InquiryController.store.url()}
                    method="post"
                    className="max-w-xl space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="inquiry_name">
                                    Inquiry name *
                                </Label>
                                <Input
                                    id="inquiry_name"
                                    name="inquiry_name"
                                    required
                                />
                                <InputError message={errors.inquiry_name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="contact_detail">
                                    Contact detail *
                                </Label>
                                <Input
                                    id="contact_detail"
                                    name="contact_detail"
                                    required
                                />
                                <InputError message={errors.contact_detail} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="communication_method">
                                    Communication method
                                </Label>
                                <select
                                    id="communication_method"
                                    name="communication_method"
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {communicationMethodOptions.map((opt) => (
                                        <option
                                            key={opt.value}
                                            value={opt.value}
                                        >
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.communication_method}
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="inquired_at">Date *</Label>
                                <Input
                                    id="inquired_at"
                                    name="inquired_at"
                                    type="date"
                                    defaultValue={new Date()
                                        .toISOString()
                                        .slice(0, 10)}
                                    required
                                />
                                <InputError message={errors.inquired_at} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="customer_search">
                                    Customer
                                </Label>
                                <input
                                    type="hidden"
                                    name="customer_id"
                                    value={selectedCustomerId}
                                />
                                <div className="relative">
                                    <Input
                                        id="customer_search"
                                        value={
                                            selectedCustomer
                                                ? selectedCustomer.name
                                                : customerSearch
                                        }
                                        onChange={(e) => {
                                            setCustomerSearch(e.target.value);
                                            setSelectedCustomerId('');
                                            setShowDropdown(true);
                                        }}
                                        onFocus={() => setShowDropdown(true)}
                                        onBlur={() =>
                                            setTimeout(
                                                () => setShowDropdown(false),
                                                200,
                                            )
                                        }
                                        placeholder="Search customers..."
                                        autoComplete="off"
                                    />
                                    {showDropdown &&
                                        filteredCustomers.length > 0 &&
                                        !selectedCustomer && (
                                            <ul className="absolute z-10 mt-1 max-h-48 w-full overflow-auto rounded-md border border-input bg-popover shadow-md">
                                                {filteredCustomers.map((c) => (
                                                    <li key={c.id}>
                                                        <button
                                                            type="button"
                                                            className="w-full px-3 py-2 text-left text-sm hover:bg-muted"
                                                            onMouseDown={(
                                                                e,
                                                            ) => {
                                                                e.preventDefault();
                                                                setSelectedCustomerId(
                                                                    c.id,
                                                                );
                                                                setCustomerSearch(
                                                                    '',
                                                                );
                                                                setShowDropdown(
                                                                    false,
                                                                );
                                                            }}
                                                        >
                                                            {c.name}
                                                        </button>
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                </div>
                                {selectedCustomer && (
                                    <button
                                        type="button"
                                        className="text-xs text-muted-foreground hover:text-foreground"
                                        onClick={() => {
                                            setSelectedCustomerId('');
                                            setCustomerSearch('');
                                        }}
                                    >
                                        Clear selection
                                    </button>
                                )}
                                <InputError message={errors.customer_id} />
                            </div>
                            <div className="flex items-center gap-2">
                                <input
                                    id="converted"
                                    name="converted"
                                    type="checkbox"
                                    value="1"
                                    className="size-4 rounded border-input"
                                />
                                <Label htmlFor="converted">
                                    Converted to customer
                                </Label>
                                <InputError message={errors.converted} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="notes">Notes</Label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows={3}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.notes} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Create inquiry
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href={index()}>Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
