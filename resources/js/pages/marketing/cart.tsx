import { Form, Link, usePage } from '@inertiajs/react';
import { Minus, Package, Plus, ShoppingBag, X } from 'lucide-react';
import CartItemController from '@/actions/App/Http/Controllers/Storefront/CartItemController';
import { OrderTotals } from '@/components/order-summary';
import { ResearchUseNotice } from '@/components/marketing/research-use-notice';
import { brandButton } from '@/lib/brand-button';
import { formatMoney } from '@/lib/money';
import { login, register } from '@/routes';
import { create as checkout } from '@/routes/checkout';
import { index as shopIndex, show as productShow } from '@/routes/shop';
import type { Money } from '@/types/money';
import type { CartLine } from '@/types/shop';

export default function Cart({
    lines,
    subtotal,
    shipping,
}: {
    lines: CartLine[];
    subtotal: Money | null;
    shipping: Money | null;
}) {
    const { auth, errors } = usePage<{ errors: Record<string, string> }>()
        .props;

    return (
        <>
            <section className="mx-auto max-w-6xl px-6 py-14 lg:px-8 lg:py-20">
                <h1 className="font-serif text-4xl font-medium text-deep-ocean">
                    Your cart
                </h1>

                {lines.length === 0 || subtotal === null ? (
                    <div className="mt-10 flex flex-col items-center rounded-3xl bg-white px-6 py-16 text-center ring-1 ring-charcoal/5">
                        <span className="flex size-12 items-center justify-center rounded-full bg-mist/50 text-deep-ocean">
                            <ShoppingBag className="size-5" />
                        </span>
                        <p className="mt-4 font-medium">Your cart is empty</p>
                        <Link
                            href={shopIndex()}
                            className={brandButton({
                                size: 'sm',
                                className: 'mt-6',
                            })}
                        >
                            Browse the shop
                        </Link>
                    </div>
                ) : (
                    <div className="mt-10 grid gap-8 lg:grid-cols-[1fr_22rem]">
                        <div className="space-y-4">
                            {errors.quantity && (
                                <p className="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-800">
                                    {errors.quantity}
                                </p>
                            )}
                            <ul className="divide-y divide-charcoal/10 rounded-3xl bg-white px-6 ring-1 ring-charcoal/5">
                                {lines.map((line) => (
                                    <li
                                        key={line.product_id}
                                        className="flex flex-wrap items-center gap-4 py-5"
                                    >
                                        <div className="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-linen">
                                            {line.image_url ? (
                                                <img
                                                    src={line.image_url}
                                                    alt=""
                                                    className="size-full object-cover"
                                                />
                                            ) : (
                                                <Package className="size-6 text-deep-ocean/60" />
                                            )}
                                        </div>
                                        <div className="min-w-40 flex-1">
                                            <Link
                                                href={productShow(line.slug)}
                                                className="font-serif text-lg text-charcoal hover:text-deep-ocean"
                                            >
                                                {line.name}
                                                {line.amount &&
                                                    ` ${line.amount}`}
                                            </Link>
                                            <p className="text-sm text-charcoal/60">
                                                {formatMoney(line.unit_price)}{' '}
                                                per vial
                                            </p>
                                        </div>
                                        <QuantityStepper line={line} />
                                        <p className="w-24 text-right font-medium tabular-nums">
                                            {formatMoney(line.total)}
                                        </p>
                                        <Form
                                            {...CartItemController.destroy.form(
                                                line.product_id,
                                            )}
                                            options={{ preserveScroll: true }}
                                        >
                                            <button
                                                type="submit"
                                                className="rounded-full p-2 text-charcoal/50 hover:bg-sand/60 hover:text-charcoal"
                                                aria-label={`Remove ${line.name}`}
                                                title="Remove"
                                            >
                                                <X className="size-4" />
                                            </button>
                                        </Form>
                                    </li>
                                ))}
                            </ul>
                            <p className="text-sm text-charcoal/60">
                                The price per vial drops at 3, 6 and 10 vials of
                                the same product.
                            </p>
                        </div>

                        <aside className="h-fit space-y-5 rounded-3xl bg-white p-6 ring-1 ring-charcoal/5">
                            <h2 className="font-serif text-2xl text-deep-ocean">
                                Summary
                            </h2>
                            <OrderTotals
                                subtotal={subtotal}
                                shipping={shipping}
                            />
                            <Link
                                href={checkout()}
                                className={brandButton({ className: 'w-full' })}
                            >
                                Check out
                            </Link>
                            {!auth.user && (
                                <p className="text-center text-xs text-charcoal/60">
                                    Checkout needs an account, so we can send
                                    your invoice and order updates.{' '}
                                    <Link
                                        href={login()}
                                        className="font-medium text-deep-ocean hover:underline"
                                    >
                                        Log in
                                    </Link>{' '}
                                    or{' '}
                                    <Link
                                        href={register()}
                                        className="font-medium text-deep-ocean hover:underline"
                                    >
                                        create one
                                    </Link>
                                    .
                                </p>
                            )}
                        </aside>
                    </div>
                )}

                <ResearchUseNotice className="mt-12" />
            </section>
        </>
    );
}

function QuantityStepper({ line }: { line: CartLine }) {
    return (
        <div className="flex items-center rounded-full ring-1 ring-charcoal/15">
            <StepButton
                line={line}
                quantity={line.quantity - 1}
                disabled={line.quantity <= 1}
                label={`One fewer ${line.name}`}
            >
                <Minus className="size-3.5" />
            </StepButton>
            <span className="w-8 text-center text-sm font-medium tabular-nums">
                {line.quantity}
            </span>
            <StepButton
                line={line}
                quantity={line.quantity + 1}
                disabled={line.quantity >= line.stock}
                label={`One more ${line.name}`}
            >
                <Plus className="size-3.5" />
            </StepButton>
        </div>
    );
}

function StepButton({
    line,
    quantity,
    disabled,
    label,
    children,
}: {
    line: CartLine;
    quantity: number;
    disabled: boolean;
    label: string;
    children: React.ReactNode;
}) {
    return (
        <Form
            {...CartItemController.update.form(line.product_id)}
            options={{ preserveScroll: true }}
        >
            {({ processing }) => (
                <>
                    <input type="hidden" name="quantity" value={quantity} />
                    <button
                        type="submit"
                        disabled={disabled || processing}
                        aria-label={label}
                        className="flex size-9 items-center justify-center rounded-full text-charcoal/70 hover:bg-sand/60 disabled:opacity-30"
                    >
                        {children}
                    </button>
                </>
            )}
        </Form>
    );
}
