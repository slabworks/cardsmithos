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

const statusBadgeVariant: Record<string, 'default' | 'secondary' | 'outline'> =
    {
        cold_lead: 'secondary',
        warm_lead: 'secondary',
        hot_lead: 'default',
        in_progress: 'default',
        good_client: 'default',
        inactive: 'outline',
    };

export default function CustomersIndex({
    customers,
}: {
    customers: Array<{
        id: number;
        name: string;
        status: string | null;
        email: string | null;
    }>;
}) {
    const [search, setSearch] = useState('');
    const filtered = useMemo(() => {
        const q = search.toLowerCase();

        if (!q) {
            return customers;
        }

        return customers.filter(
            (c) =>
                c.name.toLowerCase().includes(q) ||
                c.email?.toLowerCase().includes(q) ||
                c.status?.replace('_', ' ').toLowerCase().includes(q),
        );
    }, [customers, search]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Customers" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Customers</h1>
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
                            onChange={(e) => setSearch(e.target.value)}
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
                                        href={CustomerController.show.url(
                                            customer,
                                        )}
                                        className="flex items-center justify-between px-4 py-3 hover:bg-muted/50"
                                    >
                                        <div className="flex flex-col gap-0.5">
                                            <span className="font-medium">
                                                {customer.name}
                                            </span>
                                            {customer.email && (
                                                <span className="text-sm text-muted-foreground">
                                                    {customer.email}
                                                </span>
                                            )}
                                        </div>
                                        {customer.status && (
                                            <Badge
                                                variant={
                                                    statusBadgeVariant[
                                                        customer.status
                                                    ] ?? 'outline'
                                                }
                                            >
                                                {customer.status.replace(
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
