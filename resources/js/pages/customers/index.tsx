import { Head, Link } from '@inertiajs/react';
import { Plus, Search, Users } from 'lucide-react';
import { useMemo, useState } from 'react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { create, index } from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Customers', href: index() }];

const formatPlatform = (platform: string) =>
    platform === 'x_twitter'
        ? 'X / Twitter'
        : platform
              .split('_')
              .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
              .join(' ');

type Customer = {
    id: number;
    name: string;
    contact_detail: string | null;
    platform: string | null;
    phone: string | null;
    address: string | null;
    submissions_count: number;
};

export default function CustomersIndex({
    customers,
    filters,
}: {
    customers: Customer[];
    filters: { search: string };
}) {
    const [search, setSearch] = useState(filters.search);
    const filtered = useMemo(() => {
        const query = search.toLowerCase();

        if (!query) {
            return customers;
        }

        return customers.filter(
            (customer) =>
                customer.name.toLowerCase().includes(query) ||
                customer.contact_detail?.toLowerCase().includes(query) ||
                customer.platform?.replace('_', ' ').includes(query) ||
                customer.phone?.toLowerCase().includes(query) ||
                customer.address?.toLowerCase().includes(query),
        );
    }, [customers, search]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Customers" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold">Customers</h1>
                        <p className="text-sm text-muted-foreground">
                            Search and update reusable customer contact records.
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={create()}>
                            <Plus className="mr-2 size-4" />
                            Add customer
                        </Link>
                    </Button>
                </div>

                {customers.length > 0 && (
                    <div className="relative">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Search customers..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="pl-9"
                        />
                    </div>
                )}

                <div className="rounded-lg border border-sidebar-border bg-card">
                    {customers.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <Users className="size-10 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">
                                No customers yet
                            </p>
                            <Button asChild variant="outline">
                                <Link href={create()}>
                                    Add your first customer
                                </Link>
                            </Button>
                        </div>
                    ) : filtered.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                            <p className="text-sm text-muted-foreground">
                                No customers match your search
                            </p>
                        </div>
                    ) : (
                        <ul className="divide-y divide-sidebar-border">
                            {filtered.map((customer) => (
                                <li key={customer.id}>
                                    <Link
                                        href={CustomerController.edit.url(
                                            customer,
                                        )}
                                        className="flex items-center justify-between gap-4 px-4 py-3 hover:bg-muted/50"
                                    >
                                        <div className="flex min-w-0 flex-col gap-0.5">
                                            <span className="truncate font-medium">
                                                {customer.name}
                                            </span>
                                            <span className="truncate text-sm text-muted-foreground">
                                                {customer.contact_detail ??
                                                    customer.phone ??
                                                    'No contact details'}
                                            </span>
                                        </div>
                                        <div className="flex shrink-0 items-center gap-2">
                                            {customer.platform && (
                                                <Badge variant="secondary">
                                                    {formatPlatform(
                                                        customer.platform,
                                                    )}
                                                </Badge>
                                            )}
                                            <Badge
                                                variant={
                                                    customer.submissions_count >
                                                    0
                                                        ? 'default'
                                                        : 'outline'
                                                }
                                            >
                                                {customer.submissions_count}{' '}
                                                {customer.submissions_count ===
                                                1
                                                    ? 'submission'
                                                    : 'submissions'}
                                            </Badge>
                                        </div>
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
