import { Head, Link } from '@inertiajs/react';
import axios from 'axios';
import { FileDown } from 'lucide-react';
import { useState } from 'react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';

type Card = {
    id: number;
    name: string;
    restoration_hours: string | null;
    estimated_fee: string | null;
};

export default function InvoicesCreate({
    customer,
    downloadUrl,
    businessSettings,
}: {
    customer: {
        id: number;
        name: string;
        cards: Card[];
    };
    downloadUrl: string;
    businessSettings: {
        hourly_rate: number;
        default_fixed_rate: number;
        tax_rate: number;
        currency: string;
        company_name: string;
    };
}) {
    const [selectedCardIds, setSelectedCardIds] = useState<Set<number>>(
        new Set(),
    );
    const [shipping, setShipping] = useState('');
    const [packaging, setPackaging] = useState('');
    const [handling, setHandling] = useState('');
    const [downloading, setDownloading] = useState(false);

    const toggleCard = (id: number) => {
        setSelectedCardIds((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    };

    const cardFee = (card: Card): number => {
        if (card.restoration_hours !== null) {
            return (
                parseFloat(card.restoration_hours) *
                businessSettings.hourly_rate
            );
        }
        return businessSettings.default_fixed_rate;
    };

    const rateType = (card: Card): string =>
        card.restoration_hours !== null ? 'Hourly' : 'Fixed';

    const selectedCards = customer.cards.filter((c) =>
        selectedCardIds.has(c.id),
    );
    const subtotal = selectedCards.reduce((sum, c) => sum + cardFee(c), 0);
    const shippingVal = parseFloat(shipping) || 0;
    const packagingVal = parseFloat(packaging) || 0;
    const handlingVal = parseFloat(handling) || 0;
    const extras = shippingVal + packagingVal + handlingVal;
    const taxableAmount = subtotal + extras;
    const tax = taxableAmount * (businessSettings.tax_rate / 100);
    const grandTotal = taxableAmount + tax;

    const fmt = (n: number) =>
        n.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

    const handleDownload = async () => {
        if (selectedCardIds.size === 0) return;
        setDownloading(true);

        try {
            const response = await axios.post(
                downloadUrl,
                {
                    card_ids: Array.from(selectedCardIds),
                    shipping: shippingVal || null,
                    packaging: packagingVal || null,
                    handling: handlingVal || null,
                },
                { responseType: 'blob' },
            );

            const blob = new Blob([response.data], {
                type: 'application/pdf',
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download =
                response.headers['content-disposition']
                    ?.split('filename=')[1]
                    ?.replace(/"/g, '') ??
                'invoice.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        } finally {
            setDownloading(false);
        }
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Customers', href: index() },
        {
            title: customer.name,
            href: CustomerController.show.url(customer),
        },
        { title: 'Generate invoice', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Generate invoice" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Generate invoice"
                    description={`Create an invoice for ${customer.name}`}
                />

                <div className="max-w-2xl space-y-6">
                    <section>
                        <h2 className="mb-3 text-sm font-medium text-muted-foreground">
                            Select cards to include
                        </h2>
                        {customer.cards.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No cards for this customer.
                            </p>
                        ) : (
                            <ul className="divide-y rounded-lg border">
                                {customer.cards.map((card) => (
                                    <li
                                        key={card.id}
                                        className="flex items-center gap-3 px-4 py-3"
                                    >
                                        <Checkbox
                                            checked={selectedCardIds.has(
                                                card.id,
                                            )}
                                            onCheckedChange={() =>
                                                toggleCard(card.id)
                                            }
                                        />
                                        <div className="flex-1">
                                            <span className="font-medium">
                                                {card.name}
                                            </span>
                                            <span className="ml-2 text-sm text-muted-foreground">
                                                {rateType(card)}
                                            </span>
                                        </div>
                                        <span className="text-sm font-medium">
                                            ${fmt(cardFee(card))}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>

                    <section className="space-y-4">
                        <h2 className="text-sm font-medium text-muted-foreground">
                            Additional costs
                        </h2>
                        <div className="grid grid-cols-3 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="shipping">Shipping</Label>
                                <Input
                                    id="shipping"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    value={shipping}
                                    onChange={(e) =>
                                        setShipping(e.target.value)
                                    }
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="packaging">Packaging</Label>
                                <Input
                                    id="packaging"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    value={packaging}
                                    onChange={(e) =>
                                        setPackaging(e.target.value)
                                    }
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="handling">Handling</Label>
                                <Input
                                    id="handling"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    value={handling}
                                    onChange={(e) =>
                                        setHandling(e.target.value)
                                    }
                                />
                            </div>
                        </div>
                    </section>

                    <section className="rounded-lg border p-4">
                        <h2 className="mb-3 text-sm font-medium text-muted-foreground">
                            Invoice summary
                        </h2>
                        <div className="space-y-1 text-sm">
                            <div className="flex justify-between">
                                <span>
                                    Subtotal ({selectedCards.length} card
                                    {selectedCards.length !== 1 ? 's' : ''})
                                </span>
                                <span>${fmt(subtotal)}</span>
                            </div>
                            {shippingVal > 0 && (
                                <div className="flex justify-between">
                                    <span>Shipping</span>
                                    <span>${fmt(shippingVal)}</span>
                                </div>
                            )}
                            {packagingVal > 0 && (
                                <div className="flex justify-between">
                                    <span>Packaging</span>
                                    <span>${fmt(packagingVal)}</span>
                                </div>
                            )}
                            {handlingVal > 0 && (
                                <div className="flex justify-between">
                                    <span>Handling</span>
                                    <span>${fmt(handlingVal)}</span>
                                </div>
                            )}
                            <div className="flex justify-between">
                                <span>
                                    Tax ({businessSettings.tax_rate}%)
                                </span>
                                <span>${fmt(tax)}</span>
                            </div>
                            <div className="flex justify-between border-t pt-2 text-base font-semibold">
                                <span>Total</span>
                                <span>${fmt(grandTotal)}</span>
                            </div>
                        </div>
                    </section>

                    <div className="flex gap-2">
                        <Button
                            onClick={handleDownload}
                            disabled={
                                selectedCardIds.size === 0 || downloading
                            }
                        >
                            <FileDown className="mr-1 size-4" />
                            {downloading
                                ? 'Generating...'
                                : 'Download invoice'}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link
                                href={CustomerController.show.url(customer)}
                            >
                                Cancel
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
