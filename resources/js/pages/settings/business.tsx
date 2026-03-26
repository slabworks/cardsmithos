import { Transition } from '@headlessui/react';
import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { COUNTRIES, countryToFlag } from '@/lib/countries';
import { edit, update } from '@/routes/business';
import { show as storefrontShow } from '@/routes/storefront';
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
        company_name: string | null;
        bio: string | null;
        store_slug: string | null;
        country: string | null;
        location_name: string | null;
        instagram_handle: string | null;
        tiktok_handle: string | null;
        hourly_rate: string | null;
        default_fixed_rate: string | null;
        currency: string | null;
        tax_rate: string | null;
        hide_pricing: boolean;
        is_listed_in_directory: boolean;
    };
    waiverAgreementText: string;
}) {
    const [country, setCountry] = useState(businessSettings.country ?? '');
    const [isListedInDirectory, setIsListedInDirectory] = useState(
        businessSettings.is_listed_in_directory,
    );
    const [hidePricing, setHidePricing] = useState(
        businessSettings.hide_pricing,
    );

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
                                    <Label htmlFor="bio">Bio</Label>
                                    <textarea
                                        id="bio"
                                        name="bio"
                                        maxLength={1000}
                                        rows={4}
                                        placeholder="Tell customers about your shop and services..."
                                        defaultValue={
                                            businessSettings.bio ?? ''
                                        }
                                        className="flex w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 md:text-sm dark:aria-invalid:ring-destructive/40"
                                    />
                                    <p className="text-sm text-muted-foreground">
                                        A short public biography shown on your
                                        storefront. Max 1000 characters.
                                    </p>
                                    <InputError message={errors.bio} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="store_slug">
                                        Store slug
                                    </Label>
                                    <Input
                                        id="store_slug"
                                        name="store_slug"
                                        maxLength={63}
                                        placeholder="my-card-shop"
                                        defaultValue={
                                            businessSettings.store_slug ?? ''
                                        }
                                    />
                                    <p className="text-sm text-muted-foreground">
                                        {businessSettings.store_slug ? (
                                            <>
                                                Your public storefront:{' '}
                                                <a
                                                    href={storefrontShow.url(
                                                        businessSettings.store_slug,
                                                    )}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="underline underline-offset-4 hover:no-underline"
                                                >
                                                    {`${window.location.origin}${storefrontShow.url(businessSettings.store_slug)}`}
                                                </a>
                                            </>
                                        ) : (
                                            'Lowercase letters, numbers, and hyphens only. Sets up a public storefront page.'
                                        )}
                                    </p>
                                    <InputError message={errors.store_slug} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="country">Location</Label>
                                    <input
                                        type="hidden"
                                        name="country"
                                        value={country}
                                    />
                                    <Select
                                        value={country}
                                        onValueChange={(value) => {
                                            setCountry(value);
                                        }}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue placeholder="Select a country" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {COUNTRIES.map((c) => (
                                                <SelectItem
                                                    key={c.code}
                                                    value={c.code}
                                                >
                                                    {countryToFlag(c.code)}{' '}
                                                    {c.name}
                                                </SelectItem>
                                            ))}
                                            <SelectItem value="OT">
                                                Other
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p className="text-sm text-muted-foreground">
                                        Shown on your public storefront.
                                    </p>
                                    <InputError message={errors.country} />
                                </div>

                                {country === 'OT' && (
                                    <div className="grid gap-2">
                                        <Label htmlFor="location_name">
                                            Location name
                                        </Label>
                                        <Input
                                            id="location_name"
                                            name="location_name"
                                            maxLength={100}
                                            placeholder="e.g. Singapore"
                                            defaultValue={
                                                businessSettings.location_name ??
                                                ''
                                            }
                                        />
                                        <InputError
                                            message={errors.location_name}
                                        />
                                    </div>
                                )}

                                <div className="grid gap-2">
                                    <Label htmlFor="instagram_handle">
                                        Instagram
                                    </Label>
                                    <Input
                                        id="instagram_handle"
                                        name="instagram_handle"
                                        maxLength={30}
                                        placeholder="yourusername"
                                        defaultValue={
                                            businessSettings.instagram_handle ??
                                            ''
                                        }
                                    />
                                    <p className="text-sm text-muted-foreground">
                                        Your Instagram username without the @.
                                    </p>
                                    <InputError
                                        message={errors.instagram_handle}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="tiktok_handle">
                                        TikTok
                                    </Label>
                                    <Input
                                        id="tiktok_handle"
                                        name="tiktok_handle"
                                        maxLength={24}
                                        placeholder="yourusername"
                                        defaultValue={
                                            businessSettings.tiktok_handle ?? ''
                                        }
                                    />
                                    <p className="text-sm text-muted-foreground">
                                        Your TikTok username without the @.
                                    </p>
                                    <InputError
                                        message={errors.tiktok_handle}
                                    />
                                </div>

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

                                <div className="flex items-center gap-2">
                                    <input
                                        type="hidden"
                                        name="hide_pricing"
                                        value={hidePricing ? '1' : '0'}
                                    />
                                    <Checkbox
                                        id="hide_pricing"
                                        checked={hidePricing}
                                        onCheckedChange={(checked) => {
                                            setHidePricing(
                                                checked === true,
                                            );
                                        }}
                                    />
                                    <Label
                                        htmlFor="hide_pricing"
                                        className="cursor-pointer"
                                    >
                                        Hide pricing on storefront
                                    </Label>
                                </div>

                                <div className="flex items-center gap-2">
                                    <input
                                        type="hidden"
                                        name="is_listed_in_directory"
                                        value={isListedInDirectory ? '1' : '0'}
                                    />
                                    <Checkbox
                                        id="is_listed_in_directory"
                                        checked={isListedInDirectory}
                                        onCheckedChange={(checked) => {
                                            setIsListedInDirectory(
                                                checked === true,
                                            );
                                        }}
                                    />
                                    <Label
                                        htmlFor="is_listed_in_directory"
                                        className="cursor-pointer"
                                    >
                                        List my shop in the public directory
                                    </Label>
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
