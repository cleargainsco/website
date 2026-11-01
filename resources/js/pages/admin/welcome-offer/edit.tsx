import { Form } from '@inertiajs/react';
import { useState } from 'react';
import WelcomeOfferController from '@/actions/App/Http/Controllers/Admin/WelcomeOfferController';
import WelcomeOfferStatusController from '@/actions/App/Http/Controllers/Admin/WelcomeOfferStatusController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { WelcomeOfferCard } from '@/components/marketing/welcome-offer-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { brandButton } from '@/lib/brand-button';
import { edit } from '@/routes/admin/welcome-offer';
import type { AdminWelcomeOffer, WelcomeOfferCopy } from '@/types/marketing';

const copyFields: {
    name: keyof WelcomeOfferCopy;
    label: string;
    max: number;
    multiline?: boolean;
    help?: string;
}[] = [
    { name: 'eyebrow', label: 'Small heading', max: 60 },
    { name: 'headline', label: 'Headline', max: 120 },
    { name: 'body', label: 'Message', max: 500, multiline: true },
    { name: 'button_label', label: 'Button label', max: 40 },
    {
        name: 'fine_print',
        label: 'Fine print',
        max: 500,
        multiline: true,
        help: 'Keep the line about agreeing to marketing emails: it is what makes the sign-up consent.',
    },
    {
        name: 'success_headline',
        label: 'Thank-you headline',
        max: 120,
        help: 'Shown after they sign up.',
    },
    {
        name: 'success_body',
        label: 'Thank-you message',
        max: 500,
        multiline: true,
    },
];

const textareaClass =
    'w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

export default function WelcomeOfferEdit({
    offer,
    stats,
}: {
    offer: AdminWelcomeOffer;
    stats: { codes_sent: number; codes_redeemed: number };
}) {
    const [copy, setCopy] = useState<WelcomeOfferCopy>(offer);
    const [previewClaimed, setPreviewClaimed] = useState(false);

    return (
        <div className="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
            <Heading
                title="Welcome offer"
                description="A popup that offers first-time visitors a one-time discount code in exchange for their email. Each code is emailed, tied to that address and works once."
            />

            <Form
                {...WelcomeOfferStatusController.update.form()}
                options={{ preserveScroll: true }}
                className="flex flex-wrap items-center justify-between gap-6 rounded-xl border bg-card p-5 shadow-xs"
            >
                {({ processing, submit }) => (
                    <>
                        <div>
                            <p className="font-medium">
                                {offer.is_enabled
                                    ? 'The popup is live'
                                    : 'The popup is off'}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {offer.is_enabled
                                    ? 'First-time visitors see it a few seconds after the age check, once per browser. Signed-in customers never do.'
                                    : 'Nobody sees it, and no new codes can be claimed. Codes already sent still work.'}
                            </p>
                            <p className="mt-2 text-xs text-muted-foreground">
                                {stats.codes_sent} codes sent ·{' '}
                                {stats.codes_redeemed} redeemed
                            </p>
                        </div>
                        <input
                            type="hidden"
                            name="is_enabled"
                            value={offer.is_enabled ? '0' : '1'}
                        />
                        <Switch
                            checked={offer.is_enabled}
                            onCheckedChange={() => submit()}
                            disabled={processing}
                            aria-label="Show the welcome offer popup"
                            className="h-8 w-14 [&_[data-slot=switch-thumb]]:size-7"
                        />
                    </>
                )}
            </Form>

            <div className="grid gap-6 lg:grid-cols-[1fr_28rem]">
                <Form
                    {...WelcomeOfferController.update.form()}
                    options={{ preserveScroll: true }}
                    noValidate
                    className="space-y-6 rounded-xl border bg-card p-6 shadow-xs"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="percent_off">
                                        Discount (% off)
                                    </Label>
                                    <Input
                                        id="percent_off"
                                        name="percent_off"
                                        type="number"
                                        min={1}
                                        max={90}
                                        defaultValue={offer.percent_off}
                                    />
                                    <InputError message={errors.percent_off} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="valid_days">
                                        Code valid for (days)
                                    </Label>
                                    <Input
                                        id="valid_days"
                                        name="valid_days"
                                        type="number"
                                        min={1}
                                        max={365}
                                        defaultValue={offer.valid_days ?? ''}
                                        placeholder="Never expires"
                                    />
                                    <InputError message={errors.valid_days} />
                                </div>
                                <p className="text-xs text-muted-foreground sm:col-span-2">
                                    Changes apply to new codes. Codes already
                                    sent keep the discount and expiry they were
                                    issued with. Mention the discount in the
                                    copy below if you change it.
                                </p>
                            </div>

                            {copyFields.map((field) => (
                                <div key={field.name} className="grid gap-2">
                                    <div className="flex items-baseline justify-between gap-4">
                                        <Label htmlFor={field.name}>
                                            {field.label}
                                        </Label>
                                        <span className="text-xs text-muted-foreground tabular-nums">
                                            {copy[field.name].length}/
                                            {field.max}
                                        </span>
                                    </div>
                                    {field.multiline ? (
                                        <textarea
                                            id={field.name}
                                            name={field.name}
                                            rows={3}
                                            maxLength={field.max}
                                            value={copy[field.name]}
                                            onChange={(event) =>
                                                setCopy({
                                                    ...copy,
                                                    [field.name]:
                                                        event.target.value,
                                                })
                                            }
                                            onFocus={() =>
                                                setPreviewClaimed(
                                                    field.name.startsWith(
                                                        'success_',
                                                    ),
                                                )
                                            }
                                            className={textareaClass}
                                        />
                                    ) : (
                                        <Input
                                            id={field.name}
                                            name={field.name}
                                            maxLength={field.max}
                                            value={copy[field.name]}
                                            onChange={(event) =>
                                                setCopy({
                                                    ...copy,
                                                    [field.name]:
                                                        event.target.value,
                                                })
                                            }
                                            onFocus={() =>
                                                setPreviewClaimed(
                                                    field.name.startsWith(
                                                        'success_',
                                                    ),
                                                )
                                            }
                                        />
                                    )}
                                    {field.help && (
                                        <p className="text-xs text-muted-foreground">
                                            {field.help}
                                        </p>
                                    )}
                                    <InputError message={errors[field.name]} />
                                </div>
                            ))}

                            <Button disabled={processing}>Save</Button>
                        </>
                    )}
                </Form>

                <div className="space-y-3 lg:sticky lg:top-6 lg:h-fit">
                    <div className="flex items-center justify-between">
                        <h3 className="text-sm font-medium">Preview</h3>
                        <div className="flex gap-1 text-xs">
                            <Button
                                type="button"
                                size="sm"
                                variant={previewClaimed ? 'ghost' : 'secondary'}
                                onClick={() => setPreviewClaimed(false)}
                            >
                                Sign-up
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                variant={previewClaimed ? 'secondary' : 'ghost'}
                                onClick={() => setPreviewClaimed(true)}
                            >
                                Thank you
                            </Button>
                        </div>
                    </div>
                    <div className="rounded-3xl bg-linen p-8 shadow-sm ring-1 ring-black/5">
                        <WelcomeOfferCard
                            copy={copy}
                            claimed={previewClaimed}
                            form={
                                <div className="space-y-3">
                                    <Input
                                        disabled
                                        placeholder="you@example.com"
                                        aria-label="Email address (preview)"
                                        className="h-11 bg-white"
                                    />
                                    <span
                                        className={brandButton({
                                            className:
                                                'pointer-events-none w-full',
                                        })}
                                    >
                                        {copy.button_label}
                                    </span>
                                </div>
                            }
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}

WelcomeOfferEdit.layout = {
    breadcrumbs: [{ title: 'Welcome offer', href: edit() }],
};
