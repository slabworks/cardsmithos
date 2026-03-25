import { Head } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/pricing-calculator';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pricing calculator',
        href: index(),
    },
];

export default function PricingCalculator({
    hourlyRate,
    taxRate,
    currency,
}: {
    hourlyRate: string | null;
    taxRate: string | null;
    currency: string;
}) {
    const [calcRate, setCalcRate] = useState(hourlyRate ?? '');
    const [calcTaxRate, setCalcTaxRate] = useState(taxRate ?? '');
    const [cards, setCards] = useState([{ id: 1, min: '', max: '' }]);
    const [nextId, setNextId] = useState(2);

    const rate = parseFloat(calcRate as string);
    const tax = parseFloat(calcTaxRate as string);
    const taxMultiplier = !isNaN(tax) && tax > 0 ? 1 + tax / 100 : 1;

    const totals = cards.reduce(
        (acc, card) => {
            const min = parseFloat(card.min);
            const max = parseFloat(card.max);

            if (!isNaN(min)) {
                acc.min += min;
            }

            if (!isNaN(max)) {
                acc.max += max;
            }

            acc.hasMin = acc.hasMin || !isNaN(min);
            acc.hasMax = acc.hasMax || !isNaN(max);

            return acc;
        },
        { min: 0, max: 0, hasMin: false, hasMax: false },
    );

    const subtotalMin =
        !isNaN(rate) && totals.hasMin ? (rate / 60) * totals.min : null;
    const subtotalMax =
        !isNaN(rate) && totals.hasMax ? (rate / 60) * totals.max : null;
    const totalMin = subtotalMin !== null ? subtotalMin * taxMultiplier : null;
    const totalMax = subtotalMax !== null ? subtotalMax * taxMultiplier : null;
    const hasTax = !isNaN(tax) && tax > 0;

    const addCard = () => {
        setCards((prev) => [...prev, { id: nextId, min: '', max: '' }]);
        setNextId((prev) => prev + 1);
    };

    const removeCard = (id: number) => {
        setCards((prev) => prev.filter((c) => c.id !== id));
    };

    const updateCard = (id: number, field: 'min' | 'max', value: string) => {
        setCards((prev) =>
            prev.map((c) => (c.id === id ? { ...c, [field]: value } : c)),
        );
    };

    const fmt = (n: number) => n.toFixed(2);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pricing calculator" />

            <div className="mx-auto w-full max-w-2xl space-y-6 p-4 sm:p-6">
                <Heading
                    title="Pricing calculator"
                    description="Estimate the total cost for a job based on minutes per card"
                />

                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="calc_rate">
                            Hourly rate ({currency})
                        </Label>
                        <Input
                            id="calc_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="e.g. 100"
                            value={calcRate}
                            onChange={(e) => setCalcRate(e.target.value)}
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="calc_tax_rate">Tax rate (%)</Label>
                        <Input
                            id="calc_tax_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            placeholder="e.g. 8.5"
                            value={calcTaxRate}
                            onChange={(e) => setCalcTaxRate(e.target.value)}
                        />
                    </div>
                </div>

                <div className="space-y-3">
                    <Label>Cards</Label>
                    {cards.map((card, index) => (
                        <div key={card.id} className="flex items-end gap-3">
                            <div className="grid flex-1 gap-2">
                                {index === 0 && (
                                    <span className="text-sm text-muted-foreground">
                                        Min minutes
                                    </span>
                                )}
                                <Input
                                    type="number"
                                    step="1"
                                    min="0"
                                    placeholder="e.g. 5"
                                    value={card.min}
                                    onChange={(e) =>
                                        updateCard(
                                            card.id,
                                            'min',
                                            e.target.value,
                                        )
                                    }
                                />
                            </div>
                            <div className="grid flex-1 gap-2">
                                {index === 0 && (
                                    <span className="text-sm text-muted-foreground">
                                        Max minutes
                                    </span>
                                )}
                                <Input
                                    type="number"
                                    step="1"
                                    min="0"
                                    placeholder="e.g. 30"
                                    value={card.max}
                                    onChange={(e) =>
                                        updateCard(
                                            card.id,
                                            'max',
                                            e.target.value,
                                        )
                                    }
                                />
                            </div>
                            {cards.length > 1 && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => removeCard(card.id)}
                                >
                                    Remove
                                </Button>
                            )}
                        </div>
                    ))}
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={addCard}
                    >
                        Add card
                    </Button>
                </div>

                {subtotalMin !== null && subtotalMax !== null && (
                    <div className="rounded-lg border bg-muted/50 p-4">
                        {hasTax && (
                            <p className="text-sm text-muted-foreground">
                                Subtotal: {currency} {fmt(subtotalMin)} &ndash;{' '}
                                {currency} {fmt(subtotalMax)}
                            </p>
                        )}
                        {hasTax && totalMin !== null && totalMax !== null && (
                            <p className="text-sm text-muted-foreground">
                                Tax ({calcTaxRate}%): {currency}{' '}
                                {fmt(totalMin - subtotalMin)} &ndash; {currency}{' '}
                                {fmt(totalMax - subtotalMax)}
                            </p>
                        )}
                        <p
                            className={
                                hasTax
                                    ? 'mt-2 text-lg font-semibold'
                                    : 'text-lg font-semibold'
                            }
                        >
                            {hasTax ? 'Total' : 'Estimated range'}: {currency}{' '}
                            {fmt(totalMin ?? subtotalMin)} &ndash; {currency}{' '}
                            {fmt(totalMax ?? subtotalMax)}
                        </p>
                    </div>
                )}
                {subtotalMin !== null && subtotalMax === null && (
                    <div className="rounded-lg border bg-muted/50 p-4">
                        <p className="text-lg font-semibold">
                            From {currency} {fmt(totalMin ?? subtotalMin)}
                        </p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
