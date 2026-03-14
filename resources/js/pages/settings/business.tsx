import { Transition } from '@headlessui/react';
import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit, update } from '@/routes/business';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Business settings',
        href: edit(),
    },
];

export default function Business({
    businessSettings,
    waiverAgreementText,
}: {
    businessSettings: {
        hourly_rate: string | null;
        default_fixed_rate: string | null;
        currency: string | null;
        company_name: string | null;
        tax_rate: string | null;
    };
    waiverAgreementText: string;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Business settings" />

            <h1 className="sr-only">Business settings</h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title="Business settings"
                        description="Hourly rate, fixed rate, currency, and other defaults"
                    />

                    <Form
                        {...update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, recentlySuccessful, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="hourly_rate">
                                        Hourly rate
                                    </Label>
                                    <Input
                                        id="hourly_rate"
                                        name="hourly_rate"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="e.g. 100"
                                        defaultValue={
                                            businessSettings.hourly_rate ?? ''
                                        }
                                    />
                                    <p className="text-sm text-muted-foreground">
                                        Used to estimate card fees from
                                        restoration hours. Leave empty to use
                                        app default.
                                    </p>
                                    <InputError message={errors.hourly_rate} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="default_fixed_rate">
                                        Default fixed rate
                                    </Label>
                                    <Input
                                        id="default_fixed_rate"
                                        name="default_fixed_rate"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="e.g. 50"
                                        defaultValue={
                                            businessSettings.default_fixed_rate ??
                                            ''
                                        }
                                    />
                                    <InputError
                                        message={errors.default_fixed_rate}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="currency">Currency</Label>
                                    <Input
                                        id="currency"
                                        name="currency"
                                        maxLength={3}
                                        placeholder="USD"
                                        defaultValue={
                                            businessSettings.currency ?? 'USD'
                                        }
                                    />
                                    <InputError message={errors.currency} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="company_name">
                                        Company name
                                    </Label>
                                    <Input
                                        id="company_name"
                                        name="company_name"
                                        placeholder="Your business name"
                                        defaultValue={
                                            businessSettings.company_name ?? ''
                                        }
                                    />
                                    <InputError message={errors.company_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="tax_rate">
                                        Tax rate (%)
                                    </Label>
                                    <Input
                                        id="tax_rate"
                                        name="tax_rate"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        placeholder="e.g. 8.5"
                                        defaultValue={
                                            businessSettings.tax_rate ?? ''
                                        }
                                    />
                                    <InputError message={errors.tax_rate} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button type="submit" disabled={processing}>
                                        Save
                                    </Button>
                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">
                                            Saved
                                        </p>
                                    </Transition>
                                </div>
                            </>
                        )}
                    </Form>

                    <div className="border-t pt-6">
                        <Heading
                            variant="small"
                            title="Service waiver"
                            description="View the waiver text customers sign when they use the waiver link"
                        />
                        <Dialog>
                            <DialogTrigger asChild>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="mt-2"
                                >
                                    Read service waiver
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-h-[85vh] max-w-2xl overflow-y-auto">
                                <DialogHeader>
                                    <DialogTitle>Service waiver</DialogTitle>
                                    <DialogDescription>
                                        This is the agreement customers sign
                                        when they use the waiver link.
                                    </DialogDescription>
                                </DialogHeader>
                                <p className="text-sm leading-relaxed whitespace-pre-wrap text-foreground">
                                    {waiverAgreementText}
                                </p>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
