import { Head, Link, router } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import { ImagePlus, X } from 'lucide-react';
import { useRef, useState } from 'react';
import CardController from '@/actions/App/Http/Controllers/CardController';
import CardPhotoController from '@/actions/App/Http/Controllers/CardPhotoController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/submissions';
import type { BreadcrumbItem } from '@/types';

type Photo = {
    id: number;
    url: string;
    name: string;
};

export default function CardsEdit({
    submission,
    card,
    hourlyRate,
    taxRate,
    photosEnabled = false,
    photos = [],
    statusOptions,
    conditionOptions,
}: {
    submission: { id: number; customer: { name: string } };
    card: {
        id: number;
        name: string;
        work_done?: string | null;
        status: string;
        condition?: string | null;
        restoration_hours?: string | null;
    };
    hourlyRate: number | null;
    taxRate: number | null;
    photosEnabled?: boolean;
    photos?: Photo[];
    statusOptions: Array<{ value: string; label: string; color: string }>;
    conditionOptions: Array<{ value: string; label: string }>;
}) {
    const [uploading, setUploading] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handlePhotoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;

        if (!files || files.length === 0) {
            return;
        }

        const formData = new FormData();
        Array.from(files).forEach((file) => formData.append('photos[]', file));

        router.post(
            CardPhotoController.store({ submission, card }),
            formData as never,
            {
                forceFormData: true,
                onStart: () => setUploading(true),
                onFinish: () => {
                    setUploading(false);

                    if (fileInputRef.current) {
                        fileInputRef.current.value = '';
                    }
                },
            },
        );
    };

    const handlePhotoDelete = (mediaId: number) => {
        router.delete(
            CardPhotoController.destroy({ submission, card, media: mediaId }),
        );
    };

    const hours = card.restoration_hours
        ? Number(card.restoration_hours)
        : null;
    const subtotal =
        hourlyRate != null && hours != null && hours > 0
            ? hours * hourlyRate
            : null;
    const estimatedFee =
        subtotal != null && taxRate != null && taxRate > 0
            ? subtotal * (1 + taxRate / 100)
            : subtotal;
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Submissions', href: index() },
        {
            title: submission.customer.name,
            href: `/submissions/${submission.id}`,
        },
        {
            title: card.name,
            href: CardController.edit.url({
                submission: submission.id,
                card: card.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${card.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading title="Edit card" description={card.name} />
                <Form
                    {...CardController.update.form({
                        submission: submission.id,
                        card: card.id,
                    })}
                    className="max-w-xl space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name *</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={card.name}
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select
                                    id="status"
                                    name="status"
                                    defaultValue={card.status}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    {statusOptions.map((opt) => (
                                        <option
                                            key={opt.value}
                                            value={opt.value}
                                        >
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.status} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="condition">Condition</Label>
                                <select
                                    id="condition"
                                    name="condition"
                                    defaultValue={card.condition ?? ''}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="">None</option>
                                    {conditionOptions.map((opt) => (
                                        <option
                                            key={opt.value}
                                            value={opt.value}
                                        >
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.condition} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="restoration_hours">
                                    Restoration hours
                                </Label>
                                <Input
                                    id="restoration_hours"
                                    name="restoration_hours"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    defaultValue={card.restoration_hours ?? ''}
                                />
                                <InputError
                                    message={errors.restoration_hours}
                                />
                                {hourlyRate != null && (
                                    <p className="text-sm text-muted-foreground">
                                        {estimatedFee != null ? (
                                            <>
                                                Estimated fee: $
                                                {estimatedFee.toLocaleString(
                                                    'en-US',
                                                    {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2,
                                                    },
                                                )}{' '}
                                                ({card.restoration_hours} hours
                                                × ${hourlyRate}/hr
                                                {taxRate != null &&
                                                taxRate > 0 ? (
                                                    <> + {taxRate}% tax</>
                                                ) : null}
                                                )
                                            </>
                                        ) : (
                                            <>
                                                Estimated fee: — (enter
                                                restoration hours above)
                                            </>
                                        )}
                                    </p>
                                )}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="work_done">Work done</Label>
                                <textarea
                                    id="work_done"
                                    name="work_done"
                                    defaultValue={card.work_done ?? ''}
                                    rows={3}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                />
                                <InputError message={errors.work_done} />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save changes
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link
                                        href={`/submissions/${submission.id}`}
                                    >
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                {photosEnabled && (
                    <section className="max-w-xl space-y-4 border-t pt-8">
                        <Heading
                            variant="small"
                            title="Photos"
                            description="Upload photos of this card for documentation."
                        />
                        <div className="rounded-lg border border-sidebar-border bg-card p-4">
                            <div className="flex items-center gap-2">
                                <input
                                    ref={fileInputRef}
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    onChange={handlePhotoUpload}
                                    className="hidden"
                                    id="photo-upload"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    disabled={uploading}
                                    onClick={() => fileInputRef.current?.click()}
                                >
                                    <ImagePlus className="mr-1 size-4" />
                                    {uploading ? 'Uploading...' : 'Add photos'}
                                </Button>
                                <span className="text-xs text-muted-foreground">
                                    Max 10MB per image
                                </span>
                            </div>
                            {photos.length > 0 && (
                                <div className="mt-4 grid grid-cols-3 gap-3">
                                    {photos.map((photo) => (
                                        <div
                                            key={photo.id}
                                            className="group relative overflow-hidden rounded-lg border"
                                        >
                                            <img
                                                src={photo.url}
                                                alt={photo.name}
                                                className="aspect-square w-full object-cover"
                                            />
                                            <div className="absolute top-1 right-1 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        handlePhotoDelete(photo.id)
                                                    }
                                                    className="rounded-full bg-black/60 p-1 text-white hover:bg-black/80"
                                                >
                                                    <X className="size-3" />
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                            {photos.length === 0 && (
                                <p className="mt-3 text-sm text-muted-foreground">
                                    No photos uploaded yet.
                                </p>
                            )}
                        </div>
                    </section>
                )}

                <div className="max-w-xl space-y-4 border-t pt-8">
                    <Heading
                        variant="small"
                        title="Delete card"
                        description="Permanently remove this card and its data"
                    />
                    <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                        <p className="text-sm text-red-600 dark:text-red-100">
                            Once deleted, this card cannot be recovered.
                        </p>
                        <Dialog>
                            <DialogTrigger asChild>
                                <Button variant="destructive">
                                    Delete card
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogTitle>Delete {card.name}?</DialogTitle>
                                <DialogDescription>
                                    This will permanently delete this card and
                                    its data. This cannot be undone.
                                </DialogDescription>
                                <Form
                                    {...CardController.destroy.form({
                                        submission: submission.id,
                                        card: card.id,
                                    })}
                                    className="mt-4"
                                >
                                    {({ processing }) => (
                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button
                                                    type="button"
                                                    variant="secondary"
                                                >
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                disabled={processing}
                                            >
                                                Delete card
                                            </Button>
                                        </DialogFooter>
                                    )}
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
