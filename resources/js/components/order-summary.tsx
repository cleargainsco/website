import { formatMoney } from '@/lib/money';
import type { Money } from '@/types/money';

export function OrderTotals({
    subtotal,
    shipping,
    total,
}: {
    subtotal: Money;
    shipping: Money | null;
    total?: Money;
}) {
    return (
        <dl className="space-y-2 text-sm">
            <div className="flex justify-between">
                <dt className="opacity-70">Subtotal</dt>
                <dd className="tabular-nums">{formatMoney(subtotal)}</dd>
            </div>
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
            {shipping && total && (
                <div className="flex justify-between border-t pt-2 font-medium">
                    <dt>Total</dt>
                    <dd className="tabular-nums">{formatMoney(total)}</dd>
                </div>
            )}
        </dl>
    );
}
