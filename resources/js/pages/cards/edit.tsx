import { Head, Link, router } from '@inertiajs/react';
import { Form } from '@inertiajs/react';
import {
    Copy,
    ImagePlus,
    Pencil,
    Plus,
    RefreshCw,
    Trash2,
    X,
} from 'lucide-react';
import { useRef, useState } from 'react';
import CardActivityController from '@/actions/App/Http/Controllers/CardActivityController';
import CardController from '@/actions/App/Http/Controllers/CardController';
import CardTimelineController from '@/actions/App/Http/Controllers/CardTimelineController';
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
import { index } from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';

type Activity = {
    id: number;
    type: string;
    title: string;
    description: string | null;
    occurred_at: string;
};

type Photo = {
    id: number;
    url: string;
    name: string;
};

export default function CardsEdit({
    customer,
    card,
    hourlyRate,
    taxRate,
    photos = [],
    timelineShareUrl = '',
    statusOptions,
    conditionOptions,
    activityTypeOptions = [],
}: {
    customer: { id: number; name: string };
    card: {
        id: number;
        name: string;
        work_done?: string | null;
        status: string;
        condition_before?: string | null;
        condition_after?: string | null;
        restoration_hours?: string | null;
        activities?: Activity[];
    };
    hourlyRate: number | null;
    taxRate: number | null;
    photos?: Photo[];
    timelineShareUrl?: string;
    statusOptions: Array<{ value: string; label: string; color: string }>;
    conditionOptions: Array<{ value: string; label: string; color: string }>;
    activityTypeOptions?: Array<{ value: string; label: string }>;
}) {
    const [copied, setCopied] = useState(false);
    const [addingActivity, setAddingActivity] = useState(false);
    const [editingActivityId, setEditingActivityId] = useState<number | null>(
        null,
    );
    const [uploading, setUploading] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const activities = card.activities ?? [];

    const copyTimelineUrl = () => {
        void navigator.clipboard.writeText(timelineShareUrl ?? '').then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

    const formatOccurredAt = (iso: string) => {
        const d = new Date(iso);

        return d.toLocaleString(undefined, {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    const handlePhotoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;

        if (!files || files.length === 0) {
            return;
        }

        const formData = new FormData();
        Array.from(files).forEach((file) => formData.append('photos[]', file));

        router.post(
            `/customers/${customer.id}/cards/${card.id}/photos`,
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
            `/customers/${customer.id}/cards/${card.id}/photos/${mediaId}`,
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
        { title: 'Customers', href: index() },
        {
            title: customer.name,
            href: `/customers/${customer.id}`,
        },
        {
            title: card.name,
            href: CardController.edit.url({
                customer: customer.id,
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
                        customer: customer.id,
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
                                <Label htmlFor="condition_before">
                                    Condition (before)
                                </Label>
                                <select
                                    id="condition_before"
                                    name="condition_before"
                                    defaultValue={card.condition_before ?? ''}
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
                                <InputError message={errors.condition_before} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="condition_after">
                                    Condition (after)
                                </Label>
                                <select
                                    id="condition_after"
                                    name="condition_after"
                                    defaultValue={card.condition_after ?? ''}
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
                                <InputError message={errors.condition_after} />
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
                                    <Link href={`/customers/${customer.id}`}>
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

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
                                        <button
                                            type="button"
                                            onClick={() =>
                                                handlePhotoDelete(photo.id)
                                            }
                                            className="absolute top-1 right-1 rounded-full bg-black/60 p-1 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-black/80"
                                        >
                                            <X className="size-3" />
                                        </button>
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

                <section className="max-w-xl space-y-4 border-t pt-8">
                    <Heading
                        variant="small"
                        title="Timeline"
                        description="Activity and milestones for this card. Share the link below for a public read-only view."
                    />
                    <div className="rounded-lg border border-sidebar-border bg-card p-4">
                        <p className="mb-2 text-sm text-muted-foreground">
                            Anyone with this link can view the timeline (no
                            login required).
                        </p>
                        <div className="flex flex-wrap items-center gap-2">
                            <Input
                                readOnly
                                value={timelineShareUrl}
                                className="font-mono text-sm"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={copyTimelineUrl}
                            >
                                <Copy className="mr-1 size-4" />
                                {copied ? 'Copied' : 'Copy link'}
                            </Button>
                            <Form
                                {...CardTimelineController.rotateToken.form({
                                    customer: customer.id,
                                    card: card.id,
                                })}
                                className="inline-block"
                            >
                                <Button
                                    type="submit"
                                    variant="outline"
                                    size="sm"
                                >
                                    <RefreshCw className="mr-1 size-4" />
                                    Reset link
                                </Button>
                            </Form>
                        </div>
                    </div>
                    <div className="rounded-lg border border-sidebar-border bg-card">
                        <div className="flex items-center justify-between border-b border-sidebar-border px-4 py-3">
                            <h2 className="font-medium">Entries</h2>
                            <Button
                                size="sm"
                                variant={
                                    addingActivity ? 'secondary' : 'default'
                                }
                                onClick={() =>
                                    setAddingActivity((prev) => !prev)
                                }
                            >
                                <Plus className="mr-1 size-4" />
                                {addingActivity ? 'Cancel' : 'Add entry'}
                            </Button>
                        </div>
                        {addingActivity && (
                            <div className="border-b border-sidebar-border p-4">
                                <Form
                                    {...CardActivityController.store.form({
                                        customer: customer.id,
                                        card: card.id,
                                    })}
                                    className="space-y-3"
                                >
                                    {({ errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <Label htmlFor="new_type">
                                                    Type *
                                                </Label>
                                                <select
                                                    id="new_type"
                                                    name="type"
                                                    required
                                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                                >
                                                    {activityTypeOptions.map(
                                                        (opt) => (
                                                            <option
                                                                key={opt.value}
                                                                value={
                                                                    opt.value
                                                                }
                                                            >
                                                                {opt.label}
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
                                                <InputError
                                                    message={errors.type}
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="new_title">
                                                    Title *
                                                </Label>
                                                <Input
                                                    id="new_title"
                                                    name="title"
                                                    required
                                                />
                                                <InputError
                                                    message={errors.title}
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="new_description">
                                                    Description
                                                </Label>
                                                <textarea
                                                    id="new_description"
                                                    name="description"
                                                    rows={2}
                                                    className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                                />
                                                <InputError
                                                    message={errors.description}
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="new_occurred_at">
                                                    Date & time *
                                                </Label>
                                                <Input
                                                    id="new_occurred_at"
                                                    name="occurred_at"
                                                    type="datetime-local"
                                                    required
                                                    defaultValue={new Date()
                                                        .toISOString()
                                                        .slice(0, 16)}
                                                />
                                                <InputError
                                                    message={errors.occurred_at}
                                                />
                                            </div>
                                            <div className="flex gap-2">
                                                <Button type="submit" size="sm">
                                                    Add
                                                </Button>
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() =>
                                                        setAddingActivity(false)
                                                    }
                                                >
                                                    Cancel
                                                </Button>
                                            </div>
                                        </>
                                    )}
                                </Form>
                            </div>
                        )}
                        {activities.length === 0 && !addingActivity ? (
                            <p className="px-4 py-6 text-sm text-muted-foreground">
                                No timeline entries yet.
                            </p>
                        ) : (
                            <ul className="divide-y divide-sidebar-border">
                                {activities.map((activity) => (
                                    <li
                                        key={activity.id}
                                        className="flex items-start justify-between gap-4 px-4 py-3"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <span className="text-xs font-medium text-muted-foreground uppercase">
                                                    {activityTypeOptions.find(
                                                        (o) =>
                                                            o.value ===
                                                            activity.type,
                                                    )?.label ?? activity.type}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {formatOccurredAt(
                                                        activity.occurred_at,
                                                    )}
                                                </span>
                                            </div>
                                            <p className="font-medium">
                                                {activity.title}
                                            </p>
                                            {activity.description && (
                                                <p className="mt-1 text-sm text-muted-foreground">
                                                    {activity.description}
                                                </p>
                                            )}
                                        </div>
                                        <div className="flex shrink-0 gap-1">
                                            <Dialog
                                                open={
                                                    editingActivityId ===
                                                    activity.id
                                                }
                                                onOpenChange={(open) =>
                                                    !open &&
                                                    setEditingActivityId(null)
                                                }
                                            >
                                                <DialogTrigger asChild>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="size-8"
                                                        onClick={() =>
                                                            setEditingActivityId(
                                                                activity.id,
                                                            )
                                                        }
                                                    >
                                                        <Pencil className="size-4" />
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogTitle>
                                                        Edit entry
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        Update this timeline
                                                        entry.
                                                    </DialogDescription>
                                                    <Form
                                                        {...CardActivityController.update.form(
                                                            {
                                                                customer:
                                                                    customer.id,
                                                                card: card.id,
                                                                activity:
                                                                    activity.id,
                                                            },
                                                        )}
                                                        className="mt-4 space-y-3"
                                                        onSuccess={() =>
                                                            setEditingActivityId(
                                                                null,
                                                            )
                                                        }
                                                    >
                                                        {({ errors }) => (
                                                            <>
                                                                <div className="grid gap-2">
                                                                    <Label>
                                                                        Type *
                                                                    </Label>
                                                                    <select
                                                                        name="type"
                                                                        required
                                                                        defaultValue={
                                                                            activity.type
                                                                        }
                                                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                                                    >
                                                                        {activityTypeOptions.map(
                                                                            (
                                                                                opt,
                                                                            ) => (
                                                                                <option
                                                                                    key={
                                                                                        opt.value
                                                                                    }
                                                                                    value={
                                                                                        opt.value
                                                                                    }
                                                                                >
                                                                                    {
                                                                                        opt.label
                                                                                    }
                                                                                </option>
                                                                            ),
                                                                        )}
                                                                    </select>
                                                                    <InputError
                                                                        message={
                                                                            errors.type
                                                                        }
                                                                    />
                                                                </div>
                                                                <div className="grid gap-2">
                                                                    <Label>
                                                                        Title *
                                                                    </Label>
                                                                    <Input
                                                                        name="title"
                                                                        defaultValue={
                                                                            activity.title
                                                                        }
                                                                        required
                                                                    />
                                                                    <InputError
                                                                        message={
                                                                            errors.title
                                                                        }
                                                                    />
                                                                </div>
                                                                <div className="grid gap-2">
                                                                    <Label>
                                                                        Description
                                                                    </Label>
                                                                    <textarea
                                                                        name="description"
                                                                        defaultValue={
                                                                            activity.description ??
                                                                            ''
                                                                        }
                                                                        rows={2}
                                                                        className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                                                    />
                                                                    <InputError
                                                                        message={
                                                                            errors.description
                                                                        }
                                                                    />
                                                                </div>
                                                                <div className="grid gap-2">
                                                                    <Label>
                                                                        Date &
                                                                        time *
                                                                    </Label>
                                                                    <Input
                                                                        name="occurred_at"
                                                                        type="datetime-local"
                                                                        required
                                                                        defaultValue={
                                                                            activity.occurred_at
                                                                                ? new Date(
                                                                                      activity.occurred_at,
                                                                                  )
                                                                                      .toISOString()
                                                                                      .slice(
                                                                                          0,
                                                                                          16,
                                                                                      )
                                                                                : ''
                                                                        }
                                                                    />
                                                                    <InputError
                                                                        message={
                                                                            errors.occurred_at
                                                                        }
                                                                    />
                                                                </div>
                                                                <DialogFooter>
                                                                    <DialogClose
                                                                        asChild
                                                                    >
                                                                        <Button
                                                                            type="button"
                                                                            variant="secondary"
                                                                        >
                                                                            Cancel
                                                                        </Button>
                                                                    </DialogClose>
                                                                    <Button
                                                                        type="submit"
                                                                        size="sm"
                                                                    >
                                                                        Save
                                                                    </Button>
                                                                </DialogFooter>
                                                            </>
                                                        )}
                                                    </Form>
                                                </DialogContent>
                                            </Dialog>
                                            <Dialog>
                                                <DialogTrigger asChild>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="size-8 text-destructive hover:text-destructive"
                                                    >
                                                        <Trash2 className="size-4" />
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogTitle>
                                                        Remove this entry?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        This timeline entry will
                                                        be permanently removed.
                                                    </DialogDescription>
                                                    <Form
                                                        {...CardActivityController.destroy.form(
                                                            {
                                                                customer:
                                                                    customer.id,
                                                                card: card.id,
                                                                activity:
                                                                    activity.id,
                                                            },
                                                        )}
                                                        className="mt-4"
                                                    >
                                                        <DialogFooter className="gap-2">
                                                            <DialogClose
                                                                asChild
                                                            >
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
                                                            >
                                                                Remove
                                                            </Button>
                                                        </DialogFooter>
                                                    </Form>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </section>

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
                                        customer: customer.id,
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
