import { Head, Link } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { create, index, store } from '@/routes/conversations';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Conversations', href: index() },
    { title: 'New conversation', href: create() },
];

type CustomerOption = { id: number; name: string; email: string };

export default function ConversationsCreate({
    customerOptions,
}: {
    customerOptions: CustomerOption[];
}) {
    const [customerSearch, setCustomerSearch] = useState('');
    const [selectedCustomerId, setSelectedCustomerId] = useState<number | ''>('');
    const [showDropdown, setShowDropdown] = useState(false);

    const filteredCustomers = useMemo(() => {
        if (!customerSearch) return customerOptions;
        const q = customerSearch.toLowerCase();
        return customerOptions.filter(
            (c) =>
                c.name.toLowerCase().includes(q) ||
                c.email.toLowerCase().includes(q),
        );
    }, [customerOptions, customerSearch]);

    const selectedCustomer = customerOptions.find(
        (c) => c.id === selectedCustomerId,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New conversation" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="New conversation"
                    description="Start a conversation with a customer"
                />
                <Form
                    {...store.form()}
                    className="max-w-xl space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="customer_search">
                                    Customer *
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
                                                            onMouseDown={(e) => {
                                                                e.preventDefault();
                                                                setSelectedCustomerId(c.id);
                                                                setCustomerSearch('');
                                                                setShowDropdown(false);
                                                            }}
                                                        >
                                                            <span className="font-medium">
                                                                {c.name}
                                                            </span>
                                                            <span className="ml-2 text-muted-foreground">
                                                                {c.email}
                                                            </span>
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
                            <div className="grid gap-2">
                                <Label htmlFor="subject">Subject</Label>
                                <Input
                                    id="subject"
                                    name="subject"
                                    placeholder="Optional subject line"
                                />
                                <InputError message={errors.subject} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="body">Message *</Label>
                                <textarea
                                    id="body"
                                    name="body"
                                    rows={4}
                                    required
                                    placeholder="Write your message..."
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.body} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Send message
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
