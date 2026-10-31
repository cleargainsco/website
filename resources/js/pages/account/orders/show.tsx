import { Head, Link } from '@inertiajs/react';
import { CircleCheck } from 'lucide-react';
import { AccountPanel } from '@/components/account/account-panel';
import { OrderStatusBadge } from '@/components/order-status-badge';
import { OrderTotals } from '@/components/order-summary';
import { formatDate } from '@/lib/dates';
import { formatMoney } from '@/lib/money';
import { index as ordersIndex } from '@/routes/account/orders';
import { OrderStatus } from '@/types/enums';
import type { Order } from '@/types/shop';

const nextStep: Record<OrderStatus, string> = {
    [OrderStatus.AwaitingPayment]:
        "We'll email you an invoice with payment instructions. Your order ships once payment is received.",
    [OrderStatus.Paid]:
        "Payment received. We're packing your order and will email you tracking details when it ships.",
    [OrderStatus.Shipped]: 'Your order is on its way.',
    [OrderStatus.Cancelled]:
        "This order was cancelled. If you'd already paid, or think this is a mistake, get in touch.",
};

export default function AccountOrder({
    order,
    justPlaced,
}: {
    order: Order;
    justPlaced: boolean;
}) {
    const address = order.shipping_address;

    return (
        <>
            <Head title={`Order ${order.number}`} />

            <div className="space-y-6">
                {justPlaced && (
                    <div className="flex gap-4 rounded-3xl bg-deep-ocean p-6 text-linen">
                        <CircleCheck className="mt-0.5 size-6 shrink-0 text-champagne" />
                        <div>
                            <p className="font-serif text-2xl">
                                Thank you, your order is placed
                            </p>
                            <p className="mt-1 text-sm text-linen/80">
                                We've emailed a confirmation to {order.email}.
                                Your items are reserved while we send your
                                invoice.
                            </p>
                        </div>
                    </div>
                )}

                <AccountPanel
                    title={`Order ${order.number}`}
                    description={`Placed ${formatDate(order.placed_at)}`}
                    action={<OrderStatusBadge status={order.status} />}
                >
                    <p className="rounded-2xl bg-linen px-4 py-3 text-sm text-charcoal/80">
                        {nextStep[order.status]}
                        {order.status === OrderStatus.Shipped &&
                            order.tracking_number &&
                            ` Tracking: ${[order.carrier, order.tracking_number].filter(Boolean).join(' ')}.`}
                    </p>

                    <ul className="mt-6 divide-y divide-charcoal/10 text-sm">
                        {order.lines?.map((line, index) => (
                            <li
                                key={index}
                                className="flex justify-between gap-4 py-3"
                            >
                                <span>
                                    {line.quantity} × {line.name}
                                    {line.amount && ` ${line.amount}`}
                                    <span className="block text-charcoal/50">
                                        {formatMoney(line.unit_price)} per vial
                                    </span>
                                </span>
                                <span className="tabular-nums">
                                    {formatMoney(line.total)}
                                </span>
                            </li>
                        ))}
                    </ul>

                    <div className="mt-4 border-t border-charcoal/10 pt-4">
                        <OrderTotals
                            subtotal={order.subtotal}
                            shipping={order.shipping}
                            total={order.total}
                        />
                    </div>
                </AccountPanel>

                <AccountPanel title="Shipping to">
                    <address className="text-sm leading-relaxed text-charcoal/80 not-italic">
                        {address.name}
                        <br />
                        {address.line1}
                        {address.line2 && (
                            <>
                                <br />
                                {address.line2}
                            </>
                        )}
                        <br />
                        {address.city}, {address.region} {address.postal_code}
                        <br />
                        {address.country}
                        {address.phone && (
                            <>
                                <br />
                                {address.phone}
                            </>
                        )}
                    </address>
                </AccountPanel>

                <Link
                    href={ordersIndex()}
                    className="inline-block text-sm font-medium text-ocean hover:text-deep-ocean"
                >
                    ← All orders
                </Link>
            </div>
        </>
    );
}
