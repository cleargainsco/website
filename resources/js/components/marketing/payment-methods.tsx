import { cn } from '@/lib/utils';

export const paymentMethods = [
    { name: 'Venmo', mark: 'V', color: 'bg-[#008cff]' },
    { name: 'PayPal', mark: 'P', color: 'bg-[#003087]' },
    { name: 'Zelle', mark: 'Z', color: 'bg-[#6d1ed4]' },
    { name: 'Cash App', mark: '$', color: 'bg-[#00b929]' },
] as const;

export const paymentMethodNames = paymentMethods
    .map((method) => method.name)
    .join(', ')
    .replace(/, ([^,]*)$/, ' or $1');

export function PaymentMethods({ className }: { className?: string }) {
    return (
        <section
            aria-labelledby="payment-methods-heading"
            className={cn(
                'rounded-2xl bg-linen p-4 ring-1 ring-charcoal/5',
                className,
            )}
        >
            <h3
                id="payment-methods-heading"
                className="text-xs font-medium tracking-[0.2em] text-charcoal/60 uppercase"
            >
                Ways to pay
            </h3>
            <ul className="mt-3 grid grid-cols-2 gap-2">
                {paymentMethods.map((method) => (
                    <li
                        key={method.name}
                        className="flex items-center gap-2.5 rounded-xl bg-white px-3 py-2 text-sm font-medium text-charcoal ring-1 ring-charcoal/5"
                    >
                        <span
                            aria-hidden
                            className={cn(
                                'flex size-6 shrink-0 items-center justify-center rounded-md text-xs font-bold text-white',
                                method.color,
                            )}
                        >
                            {method.mark}
                        </span>
                        {method.name}
                    </li>
                ))}
            </ul>
            <p className="mt-3 text-xs text-charcoal/60">
                Pay your emailed invoice with any of these.
            </p>
        </section>
    );
}
