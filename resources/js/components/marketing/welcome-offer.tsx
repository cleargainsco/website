import { Form, usePage } from '@inertiajs/react';
import * as DialogPrimitive from '@radix-ui/react-dialog';
import { X } from 'lucide-react';
import { useEffect, useState } from 'react';
import WelcomeOfferClaimController from '@/actions/App/Http/Controllers/Storefront/WelcomeOfferClaimController';
import InputError from '@/components/input-error';
import { WelcomeOfferCard } from '@/components/marketing/welcome-offer-card';
import { Input } from '@/components/ui/input';
import { brandButton } from '@/lib/brand-button';

const COOKIE = 'welcome_offer_seen';
const SHOW_AFTER_MS = 4000;

function rememberSeen() {
    document.cookie = `${COOKIE}=1;path=/;max-age=${60 * 60 * 24 * 365};SameSite=Lax`;
}

export function WelcomeOffer() {
    const { welcomeOffer, ageVerified } = usePage().props;
    // Holds the last offer the server sent: signing up stops it being sent, and the
    // thank-you message still has to show.
    const [copy, setCopy] = useState(welcomeOffer);

    if (welcomeOffer && welcomeOffer.headline !== copy?.headline) {
        setCopy(welcomeOffer);
    }
    const [open, setOpen] = useState(false);
    const [claimed, setClaimed] = useState(false);

    useEffect(() => {
        if (!copy || !ageVerified) {
            return;
        }

        const timer = window.setTimeout(() => setOpen(true), SHOW_AFTER_MS);

        return () => window.clearTimeout(timer);
    }, [copy, ageVerified]);

    if (!copy) {
        return null;
    }

    return (
        <DialogPrimitive.Root
            open={open}
            onOpenChange={(next) => {
                if (!next) {
                    rememberSeen();
                }

                setOpen(next);
            }}
        >
            <DialogPrimitive.Portal>
                <DialogPrimitive.Overlay className="fixed inset-0 z-40 bg-deep-ocean/50 backdrop-blur-sm" />
                <DialogPrimitive.Content className="fixed top-1/2 left-1/2 z-40 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-3xl bg-linen p-8 shadow-2xl ring-1 ring-charcoal/5 sm:p-10">
                    <DialogPrimitive.Close
                        aria-label="Close"
                        className="absolute top-4 right-4 rounded-full p-1.5 text-charcoal/50 transition-colors hover:bg-charcoal/5 hover:text-charcoal"
                    >
                        <X className="size-5" />
                    </DialogPrimitive.Close>

                    <WelcomeOfferCard
                        copy={copy}
                        claimed={claimed}
                        title={DialogPrimitive.Title}
                        description={DialogPrimitive.Description}
                        form={
                            <Form
                                {...WelcomeOfferClaimController.store.form()}
                                options={{
                                    preserveScroll: true,
                                    preserveState: true,
                                }}
                                onSuccess={() => setClaimed(true)}
                                noValidate
                                className="space-y-3 text-left"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <Input
                                            type="email"
                                            name="email"
                                            required
                                            autoComplete="email"
                                            placeholder="you@example.com"
                                            aria-label="Email address"
                                            className="h-11 bg-white"
                                        />
                                        <InputError message={errors.email} />
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className={brandButton({
                                                className: 'w-full',
                                            })}
                                        >
                                            {copy.button_label}
                                        </button>
                                    </>
                                )}
                            </Form>
                        }
                    />

                    {claimed && (
                        <DialogPrimitive.Close
                            className={brandButton({
                                variant: 'secondary',
                                className: 'mt-8 w-full',
                            })}
                        >
                            Continue shopping
                        </DialogPrimitive.Close>
                    )}
                </DialogPrimitive.Content>
            </DialogPrimitive.Portal>
        </DialogPrimitive.Root>
    );
}
