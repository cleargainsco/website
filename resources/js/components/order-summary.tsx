import { formatMoney } from '@/lib/money';
import type { Money } from '@/types/money';
import type { AppliedDiscount } from '@/types/shop';

export function OrderTotals({
    subtotal,
    discount,
    shipping,
    total,
}: {
    subtotal: Money;
    discount?: AppliedDiscount | null;
    shipping: Money | null;
    total?: Money | null;
}) {
    return (
        <dl className="space-y-2 text-sm">
            <div className="flex justify-between">
                <dt className="opacity-70">Subtotal</dt>
                <dd className="tabular-nums">{formatMoney(subtotal)}</dd>
            </div>
            {discount && (
                <div className="flex justify-between gap-4">
                    <dt className="opacity-70">Discount ({discount.code})</dt>
                    <dd className="tabular-nums">
                        −{formatMoney(discount.amount)}
                    </dd>
                </div>
            )}
            <div className="flex justify-between gap-4">
                <dt className="opacity-70">Shipping and tax</dt>
                <dd className="text-right">
                    {shipping ? (
                        <span className="tabular-nums">
                            {formatMoney(shipping)} shipping
                        </span>
                    ) : (
                        <span className="opacity-70">
                            Confirmed on your invoice
                        </span>
                    )}
                </dd>
            </div>
            {total && (shipping || discount) && (
                <div className="flex justify-between border-t pt-2 font-medium">
                    <dt>{shipping ? 'Total' : 'Total before shipping'}</dt>
                    <dd className="tabular-nums">{formatMoney(total)}</dd>
                </div>
            )}
        </dl>
    );
}
