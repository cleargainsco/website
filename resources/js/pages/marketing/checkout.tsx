import { Form, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import CheckoutController from '@/actions/App/Http/Controllers/Storefront/CheckoutController';
import CheckoutDiscountController from '@/actions/App/Http/Controllers/Storefront/CheckoutDiscountController';
import { LoginForm } from '@/components/auth/login-form';
import { RegisterForm } from '@/components/auth/register-form';
import InputError from '@/components/input-error';
import { OrderTotals } from '@/components/order-summary';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { PaymentMethods } from '@/components/marketing/payment-methods';
import { brandButton } from '@/lib/brand-button';
import { formatMoney } from '@/lib/money';
import { show as cartShow } from '@/routes/cart';
import { terms } from '@/routes/policies';
import type { Money } from '@/types/money';
import type { AppliedDiscount, CartLine } from '@/types/shop';

type AddressFields = {
    shipping_name: string;
    shipping_phone: string | null;
    shipping_line1: string | null;
    shipping_line2: string | null;
    shipping_city: string | null;
    shipping_region: string | null;
    shipping_postal_code: string | null;
    shipping_country: string;
};

const fields: {
    name: keyof AddressFields;
    label: string;
    autoComplete: string;
    optional?: boolean;
    wide?: boolean;
}[] = [
    {
        name: 'shipping_name',
        label: 'Full name',
        autoComplete: 'name',
        wide: true,
    },
    {
        name: 'shipping_line1',
        label: 'Address',
        autoComplete: 'address-line1',
        wide: true,
    },
    {
        name: 'shipping_line2',
        label: 'Apartment, suite, etc.',
        autoComplete: 'address-line2',
        optional: true,
        wide: true,
    },
    { name: 'shipping_city', label: 'City', autoComplete: 'address-level2' },
    {
        name: 'shipping_region',
        label: 'State / region',
        autoComplete: 'address-level1',
    },
    {
        name: 'shipping_postal_code',
        label: 'ZIP / postal code',
        autoComplete: 'postal-code',
    },
    {
        name: 'shipping_country',
        label: 'Country',
        autoComplete: 'country-name',
    },
    {
        name: 'shipping_phone',
        label: 'Phone',
        autoComplete: 'tel',
        optional: true,
        wide: true,
    },
];

const CHECKOUT_FORM = 'checkout-form';

type Discount = AppliedDiscount & { percent_off: number };

function DiscountCodeField({ discount }: { discount: Discount | null }) {
    if (discount) {
        return (
            <div className="flex items-center justify-between gap-4 rounded-2xl bg-sand/40 px-4 py-3 text-sm">
                <span>
                    <span className="font-medium">{discount.code}</span> ·{' '}
                    {discount.percent_off}% off
                </span>
                <Form
                    {...CheckoutDiscountController.destroy.form()}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <button
                            type="submit"
                            disabled={processing}
                            className="font-medium text-ocean hover:text-deep-ocean"
                        >
                            Remove
                        </button>
                    )}
                </Form>
            </div>
        );
    }

    return (
        <Form
            {...CheckoutDiscountController.store.form()}
            options={{ preserveScroll: true }}
            resetOnSuccess
            noValidate
            className="space-y-2"
        >
            {({ processing, errors }) => (
                <>
                    <Label htmlFor="code">Discount code</Label>
                    <div className="flex gap-2">
                        <Input
                            id="code"
                            name="code"
                            autoComplete="off"
                            className="uppercase placeholder:normal-case"
                            placeholder="Enter code"
                        />
                        <button
                            type="submit"
                            disabled={processing}
                            className={brandButton({ variant: 'secondary' })}
                        >
                            Apply
                        </button>
                    </div>
                    <InputError message={errors.code} />
                </>
            )}
        </Form>
    );
}

function CartLineList({ lines }: { lines: CartLine[] }) {
    return (
        <ul className="space-y-3 text-sm">
            {lines.map((line) => (
                <li
                    key={line.product_id}
                    className="flex justify-between gap-4"
                >
                    <span>
                        {line.quantity} × {line.name}
                        {line.amount && ` ${line.amount}`}
                    </span>
                    <span className="tabular-nums">
                        {formatMoney(line.total)}
                    </span>
                </li>
            ))}
        </ul>
    );
}

type Summary = {
    lines: CartLine[];
    subtotal: Money;
    shipping: Money | null;
};

type AccountStepProps = Summary & {
    step: 'account';
    canResetPassword: boolean;
    passwordRules: string;
};

type DetailsStepProps = Summary & {
    step: 'details';
    discount: Discount | null;
    total: Money | null;
    address: AddressFields;
};

const tabClass = (active: boolean) =>
    active
        ? 'rounded-full bg-white px-4 py-2 font-medium text-deep-ocean shadow-sm'
        : 'rounded-full px-4 py-2 text-charcoal/60 hover:text-charcoal';

function AccountPanel({
    canResetPassword,
    passwordRules,
}: {
    canResetPassword: boolean;
    passwordRules: string;
}) {
    const [mode, setMode] = useState<'register' | 'login'>('register');

    return (
        <section className="rounded-3xl bg-white p-6 ring-1 ring-charcoal/5 sm:p-8">
            <h2 className="font-serif text-2xl text-deep-ocean">
                {mode === 'register'
                    ? 'Create an account to check out'
                    : 'Log in to check out'}
            </h2>
            <p className="mt-1 text-sm text-charcoal/60">
                We send your invoice and order updates to your account email.
                Your cart is saved while you do this.
            </p>

            <div
                role="tablist"
                aria-label="Account"
                className="mt-6 grid grid-cols-2 gap-1 rounded-full bg-sand/50 p-1 text-sm"
            >
                <button
                    type="button"
                    role="tab"
                    aria-selected={mode === 'register'}
                    onClick={() => setMode('register')}
                    className={tabClass(mode === 'register')}
                >
                    I'm new here
                </button>
                <button
                    type="button"
                    role="tab"
                    aria-selected={mode === 'login'}
                    onClick={() => setMode('login')}
                    className={tabClass(mode === 'login')}
                >
                    I have an account
                </button>
            </div>

            <div className="mt-6">
                {mode === 'register' ? (
                    <RegisterForm passwordRules={passwordRules} />
                ) : (
                    <LoginForm canResetPassword={canResetPassword} />
                )}
            </div>
        </section>
    );
}

export default function Checkout(props: AccountStepProps | DetailsStepProps) {
    if (props.step === 'details') {
        return <DetailsStep {...props} />;
    }

    return (
        <section className="mx-auto max-w-6xl px-6 py-14 lg:px-8 lg:py-20">
            <h1 className="font-serif text-4xl font-medium text-deep-ocean">
                Checkout
            </h1>

            <div className="mt-10 grid gap-8 lg:grid-cols-[1fr_24rem]">
                <AccountPanel
                    canResetPassword={props.canResetPassword}
                    passwordRules={props.passwordRules}
                />

                <aside className="h-fit space-y-5 rounded-3xl bg-white p-6 ring-1 ring-charcoal/5">
                    <h2 className="font-serif text-2xl text-deep-ocean">
                        Your order
                    </h2>
                    <CartLineList lines={props.lines} />
                    <div className="border-t border-charcoal/10 pt-4">
                        <OrderTotals
                            subtotal={props.subtotal}
                            shipping={props.shipping}
                        />
                    </div>
                    <p className="text-xs text-charcoal/60">
                        Have a discount code? You can enter it on the next step.
                    </p>
                </aside>
            </div>
        </section>
    );
}

function DetailsStep({
    lines,
    subtotal,
    discount,
    shipping,
    total,
    address,
}: DetailsStepProps) {
    const { auth, errors } = usePage().props;
    const [placing, setPlacing] = useState(false);

    return (
        <>
            <section className="mx-auto max-w-6xl px-6 py-14 lg:px-8 lg:py-20">
                <h1 className="font-serif text-4xl font-medium text-deep-ocean">
                    Checkout
                </h1>

                <div className="mt-10 grid gap-8 lg:grid-cols-[1fr_24rem]">
                    <Form
                        {...CheckoutController.store.form()}
                        id={CHECKOUT_FORM}
                        onStart={() => setPlacing(true)}
                        onFinish={() => setPlacing(false)}
                        noValidate
                    >
                        {({ errors }) => (
                            <div className="space-y-6">
                                {errors.code && (
                                    <p className="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-800">
                                        {errors.code} It has been removed, so
                                        check your total before placing the
                                        order.
                                    </p>
                                )}
                                {errors.cart && (
                                    <p className="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-800">
                                        {errors.cart}{' '}
                                        <Link
                                            href={cartShow()}
                                            className="font-medium underline"
                                        >
                                            Go to your cart
                                        </Link>
                                    </p>
                                )}

                                <section className="rounded-3xl bg-white p-6 ring-1 ring-charcoal/5 sm:p-8">
                                    <h2 className="font-serif text-2xl text-deep-ocean">
                                        Shipping address
                                    </h2>
                                    <p className="mt-1 text-sm text-charcoal/60">
                                        Order updates go to {auth.user.email}.
                                    </p>
                                    <div className="mt-6 grid gap-5 sm:grid-cols-2">
                                        {fields.map((field) => (
                                            <div
                                                key={field.name}
                                                className={
                                                    field.wide
                                                        ? 'grid gap-2 sm:col-span-2'
                                                        : 'grid gap-2'
                                                }
                                            >
                                                <Label htmlFor={field.name}>
                                                    {field.label}
                                                    {field.optional && (
                                                        <span className="font-normal text-charcoal/50">
                                                            {' '}
                                                            (optional)
                                                        </span>
                                                    )}
                                                </Label>
                                                <Input
                                                    id={field.name}
                                                    name={field.name}
                                                    defaultValue={
                                                        address[field.name] ??
                                                        ''
                                                    }
                                                    autoComplete={
                                                        field.autoComplete
                                                    }
                                                    required={!field.optional}
                                                    type={
                                                        field.name ===
                                                        'shipping_phone'
                                                            ? 'tel'
                                                            : 'text'
                                                    }
                                                />
                                                <InputError
                                                    message={errors[field.name]}
                                                />
                                            </div>
                                        ))}
                                        <div className="grid gap-2 sm:col-span-2">
                                            <Label htmlFor="customer_note">
                                                Order note
                                                <span className="font-normal text-charcoal/50">
                                                    {' '}
                                                    (optional)
                                                </span>
                                            </Label>
                                            <textarea
                                                id="customer_note"
                                                name="customer_note"
                                                rows={3}
                                                className="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            />
                                            <InputError
                                                message={errors.customer_note}
                                            />
                                        </div>
                                    </div>
                                </section>
                            </div>
                        )}
                    </Form>

                    <aside className="h-fit space-y-5 rounded-3xl bg-white p-6 ring-1 ring-charcoal/5">
                        <h2 className="font-serif text-2xl text-deep-ocean">
                            Your order
                        </h2>
                        <CartLineList lines={lines} />
                        <DiscountCodeField discount={discount} />
                        <div className="border-t border-charcoal/10 pt-4">
                            <OrderTotals
                                subtotal={subtotal}
                                discount={discount}
                                shipping={shipping}
                                total={total}
                            />
                        </div>
                        <div className="rounded-2xl border border-champagne/50 bg-sand/40 p-4 text-sm">
                            <label className="flex items-start gap-3">
                                <Checkbox
                                    name="research_use"
                                    value="1"
                                    form={CHECKOUT_FORM}
                                    className="mt-0.5 bg-white"
                                />
                                <span>
                                    I confirm these products are for laboratory
                                    research only and not for human or
                                    veterinary use, and I agree to the{' '}
                                    <Link
                                        href={terms()}
                                        className="font-medium text-deep-ocean underline"
                                    >
                                        terms of sale
                                    </Link>
                                    .
                                </span>
                            </label>
                            <InputError
                                className="mt-2"
                                message={errors.research_use}
                            />
                        </div>
                        <button
                            type="submit"
                            form={CHECKOUT_FORM}
                            disabled={placing}
                            className={brandButton({
                                className: 'w-full',
                            })}
                        >
                            Place order
                        </button>
                        <p className="text-xs text-charcoal/60">
                            No payment is taken now. We'll reserve your items
                            and email you an invoice with payment instructions.
                            Your order ships once payment is received.
                        </p>
                        <PaymentMethods />
                    </aside>
                </div>
            </section>
        </>
    );
}
