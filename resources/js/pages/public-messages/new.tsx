import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, MessageSquare } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    slug: string;
    companyName: string;
};

export default function NewPublicMessage({ slug, companyName }: Props) {
    return (
        <>
            <Head title={`Message ${companyName}`} />
            <div className="flex min-h-screen items-center justify-center bg-[#f5f5f4] px-4 py-12">
                <div className="w-full max-w-md">
                    <div className="rounded-lg border border-[#e8e8e6] bg-white p-8 shadow-sm">
                        <div className="mb-6 text-center">
                            <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#f5f5f4]">
                                <MessageSquare className="h-6 w-6 text-[#575754]" />
                            </div>
                            <h1 className="text-xl font-semibold tracking-tight text-[#1b1b18]">
                                {companyName}
                            </h1>
                            <p className="mt-1 text-sm text-[#575754]">
                                Send us a message and we'll get back to you as
                                soon as we can.
                            </p>
                        </div>

                        <Form
                            action={`/c/${slug}/messages`}
                            method="post"
                            className="flex flex-col gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="guest_name">Name</Label>
                                        <Input
                                            id="guest_name"
                                            type="text"
                                            name="guest_name"
                                            required
                                            autoFocus
                                            placeholder="Your name"
                                        />
                                        <InputError
                                            message={errors.guest_name}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="guest_email">
                                            Email
                                        </Label>
                                        <Input
                                            id="guest_email"
                                            type="email"
                                            name="guest_email"
                                            required
                                            placeholder="you@example.com"
                                        />
                                        <InputError
                                            message={errors.guest_email}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="body">Message</Label>
                                        <textarea
                                            id="body"
                                            name="body"
                                            rows={4}
                                            required
                                            placeholder="How can we help you?"
                                            className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                        />
                                        <InputError message={errors.body} />
                                    </div>

                                    <Button
                                        type="submit"
                                        className="mt-2 w-full"
                                        disabled={processing}
                                    >
                                        {processing && <Spinner />}
                                        Send message
                                    </Button>
                                </>
                            )}
                        </Form>
                    </div>

                    <div className="mt-4 text-center">
                        <Link
                            href={`/c/${slug}`}
                            className="inline-flex items-center gap-1.5 text-sm text-[#575754] hover:text-[#1b1b18]"
                        >
                            <ArrowLeft className="h-3.5 w-3.5" />
                            Back to storefront
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
