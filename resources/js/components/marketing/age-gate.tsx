import { Form, usePage } from '@inertiajs/react';
import * as DialogPrimitive from '@radix-ui/react-dialog';
import { useState } from 'react';
import AgeVerificationController from '@/actions/App/Http/Controllers/Storefront/AgeVerificationController';
import { brandButton } from '@/lib/brand-button';

export function AgeGate() {
    const { ageVerified } = usePage().props;
    const [declined, setDeclined] = useState(false);

    return (
        <DialogPrimitive.Root open={!ageVerified}>
            <DialogPrimitive.Portal>
                <DialogPrimitive.Overlay className="fixed inset-0 z-50 bg-deep-ocean/70 backdrop-blur-md" />
                <DialogPrimitive.Content
                    onEscapeKeyDown={(event) => event.preventDefault()}
                    onPointerDownOutside={(event) => event.preventDefault()}
                    onInteractOutside={(event) => event.preventDefault()}
                    className="fixed top-1/2 left-1/2 z-50 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-3xl bg-linen p-8 text-center text-charcoal shadow-2xl ring-1 ring-charcoal/5 sm:p-10"
                >
                    <img
                        src="/images/brand/clear-gains-horizontal.svg"
                        alt=""
                        className="mx-auto h-10 w-auto"
                    />

                    {declined ? (
                        <>
                            <DialogPrimitive.Title className="mt-8 font-serif text-3xl font-medium text-deep-ocean">
                                Sorry, you can't enter
                            </DialogPrimitive.Title>
                            <DialogPrimitive.Description className="mt-3 text-sm text-charcoal/70">
                                You must be 21 or older to visit this site.
                            </DialogPrimitive.Description>
                            <button
                                type="button"
                                onClick={() => setDeclined(false)}
                                className="mt-8 text-sm font-medium text-ocean hover:text-deep-ocean"
                            >
                                I chose the wrong option
                            </button>
                        </>
                    ) : (
                        <>
                            <DialogPrimitive.Title className="mt-8 font-serif text-3xl font-medium text-deep-ocean">
                                Are you 21 or older?
                            </DialogPrimitive.Title>
                            <DialogPrimitive.Description className="mt-3 text-sm text-charcoal/70">
                                You must be 21 or older to enter. Our products
                                are sold for laboratory research use only and
                                are not for human consumption.
                            </DialogPrimitive.Description>
                            <Form
                                {...AgeVerificationController.store.form()}
                                options={{ preserveScroll: true }}
                                className="mt-8 flex flex-col gap-3"
                            >
                                {({ processing }) => (
                                    <>
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className={brandButton({
                                                className: 'w-full',
                                            })}
                                        >
                                            Yes, I'm 21 or older
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setDeclined(true)}
                                            className={brandButton({
                                                variant: 'secondary',
                                                className: 'w-full',
                                            })}
                                        >
                                            No, I'm under 21
                                        </button>
                                    </>
                                )}
                            </Form>
                            <p className="mt-6 text-xs text-charcoal/50">
                                We'll remember your answer on this device for 30
                                days.
                            </p>
                        </>
                    )}
                </DialogPrimitive.Content>
            </DialogPrimitive.Portal>
        </DialogPrimitive.Root>
    );
}
