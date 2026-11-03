import { Link, router } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { PaymentMethods } from '@/components/marketing/payment-methods';
import { ProductVisual } from '@/components/marketing/product-visual';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetTitle,
} from '@/components/ui/sheet';
import { brandButton } from '@/lib/brand-button';
import { formatMoney } from '@/lib/money';
import { cn } from '@/lib/utils';
import { show as cartShow } from '@/routes/cart';
import { create as checkoutCreate } from '@/routes/checkout';
import type { CartAdded } from '@/types/shop';

export function CartDrawer() {
    const [added, setAdded] = useState<CartAdded | null>(null);
    const [open, setOpen] = useState(false);

    useEffect(() => {
        return router.on('flash', (event) => {
            const data = (event as CustomEvent).detail?.flash?.cartAdded as
                | CartAdded
                | undefined;

            if (data) {
                setAdded(data);
                setOpen(true);
            }
        });
    }, []);

    const addedLine = added?.lines.find(
        (line) => line.product_id === added.product_id,
    );
    const itemCount =
        added?.lines.reduce((count, line) => count + line.quantity, 0) ?? 0;

    return (
        <Sheet open={open} onOpenChange={setOpen}>
            <SheetContent className="w-full gap-0 bg-linen p-0 font-sans text-charcoal sm:max-w-md">
                <div className="border-b border-charcoal/10 px-6 py-5">
                    <SheetTitle className="flex items-center gap-2 font-serif text-2xl font-medium text-deep-ocean">
                        <CheckCircle2 className="size-6 text-ocean" />
                        Added to your cart
                    </SheetTitle>
                    <SheetDescription className="mt-1 text-sm text-charcoal/60">
                        {addedLine
                            ? `${addedLine.name}${addedLine.amount ? ` ${addedLine.amount}` : ''}. Your cart now has ${itemCount} ${itemCount === 1 ? 'vial' : 'vials'}.`
                            : 'Your cart has been updated.'}
                    </SheetDescription>
                </div>

                <ul className="flex-1 space-y-3 overflow-y-auto px-6 py-5">
                    {added?.lines.map((line) => (
                        <li
                            key={line.product_id}
                            className={cn(
                                'flex items-center gap-4 rounded-2xl bg-white p-3 ring-1',
                                line.product_id === added.product_id
                                    ? 'ring-2 ring-ocean/60'
                                    : 'ring-charcoal/5',
                            )}
                        >
                            <ProductVisual
                                product={{
                                    ...line,
                                    amount: line.amount ?? '',
                                }}
                                className="w-14 shrink-0 rounded-xl"
                            />
                            <div className="min-w-0 flex-1 text-sm">
                                <p className="truncate font-medium">
                                    {line.name}
                                    {line.amount && ` ${line.amount}`}
                                </p>
                                <p className="text-charcoal/60">
                                    {line.quantity} ×{' '}
                                    {formatMoney(line.unit_price)}
                                </p>
                            </div>
                            <p className="text-sm font-medium tabular-nums">
                                {formatMoney(line.total)}
                            </p>
                        </li>
                    ))}
                </ul>

                <div className="space-y-4 border-t border-charcoal/10 bg-white px-6 py-5">
                    {added?.subtotal && (
                        <div className="flex items-baseline justify-between">
                            <span className="text-sm text-charcoal/70">
                                Subtotal
                            </span>
                            <span className="font-serif text-2xl text-charcoal tabular-nums">
                                {formatMoney(added.subtotal)}
                            </span>
                        </div>
                    )}
                    <Link
                        href={checkoutCreate()}
                        onClick={() => setOpen(false)}
                        className={brandButton({ className: 'w-full' })}
                    >
                        Check out
                    </Link>
                    <div className="flex items-center justify-between text-sm">
                        <Link
                            href={cartShow()}
                            onClick={() => setOpen(false)}
                            className="font-medium text-deep-ocean hover:text-ocean"
                        >
                            View cart
                        </Link>
                        <button
                            type="button"
                            onClick={() => setOpen(false)}
                            className="text-charcoal/60 hover:text-charcoal"
                        >
                            Keep shopping
                        </button>
                    </div>
                    <PaymentMethods />
                </div>
            </SheetContent>
        </Sheet>
    );
}
