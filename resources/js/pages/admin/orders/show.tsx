import { Form } from '@inertiajs/react';
import { Ban, CircleDollarSign, Truck } from 'lucide-react';
import OrderCancellationController from '@/actions/App/Http/Controllers/Admin/OrderCancellationController';
import OrderPaymentController from '@/actions/App/Http/Controllers/Admin/OrderPaymentController';
import OrderShipmentController from '@/actions/App/Http/Controllers/Admin/OrderShipmentController';
import { ConfirmActionDialog } from '@/components/confirm-action-dialog';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { OrderStatusBadge } from '@/components/order-status-badge';
import { OrderTotals } from '@/components/order-summary';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate, formatDateTime } from '@/lib/dates';
import { formatMoney } from '@/lib/money';
import { index, show } from '@/routes/admin/orders';
import type { AdminOrder } from '@/types/shop';

type Props = { order: AdminOrder };

export default function OrderShow({ order }: Props) {
    const address = order.shipping_address;
    const timeline = [
        ['Placed', order.placed_at],
        ['Paid', order.paid_at],
        ['Shipped', order.shipped_at],
        ['Cancelled', order.cancelled_at],
    ].filter((entry): entry is [string, string] => entry[1] !== null);

    return (
        <>
            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Heading
                            title={`Order ${order.number}`}
                            description={`Placed ${formatDate(order.placed_at)} by ${order.customer_name ?? address.name}`}
                        />
                    </div>
                    <OrderStatusBadge status={order.status} />
                </div>

                <div className="grid gap-6 lg:grid-cols-[1fr_20rem]">
                    <div className="space-y-6">
                        <section className="rounded-xl border bg-card p-6 shadow-xs">
                            <h2 className="text-base font-semibold">Items</h2>
                            <ul className="mt-4 divide-y text-sm">
                                {order.lines?.map((line, lineIndex) => (
                                    <li
                                        key={lineIndex}
                                        className="flex justify-between gap-4 py-3"
                                    >
                                        <span>
                                            {line.quantity} × {line.name}
                                            {line.amount && ` ${line.amount}`}
                                            <span className="block text-muted-foreground">
                                                {formatMoney(line.unit_price)}{' '}
                                                each
                                            </span>
                                        </span>
                                        <span className="tabular-nums">
                                            {formatMoney(line.total)}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                            <div className="mt-2 border-t pt-4">
                                <OrderTotals
                                    subtotal={order.subtotal}
                                    discount={order.discount}
                                    shipping={order.shipping}
                                    total={order.total}
                                />
                            </div>
                        </section>

                        {order.can_ship && (
                            <section className="rounded-xl border bg-card p-6 shadow-xs">
                                <h2 className="text-base font-semibold">
                                    Mark as shipped
                                </h2>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    The customer is emailed with the tracking
                                    details.
                                </p>
                                <Form
                                    {...OrderShipmentController.store.form(
                                        order.id,
                                    )}
                                    options={{ preserveScroll: true }}
                                    noValidate
                                    className="mt-4 grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <Label htmlFor="carrier">
                                                    Carrier
                                                </Label>
                                                <Input
                                                    id="carrier"
                                                    name="carrier"
                                                    placeholder="e.g. USPS"
                                                />
                                                <InputError
                                                    message={errors.carrier}
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="tracking_number">
                                                    Tracking number
                                                </Label>
                                                <Input
                                                    id="tracking_number"
                                                    name="tracking_number"
                                                />
                                                <InputError
                                                    message={
                                                        errors.tracking_number
                                                    }
                                                />
                                            </div>
                                            <Button disabled={processing}>
                                                <Truck />
                                                Mark shipped
                                            </Button>
                                        </>
                                    )}
                                </Form>
                            </section>
                        )}
                    </div>

                    <aside className="space-y-6">
                        {(order.can_mark_paid || order.can_cancel) && (
                            <section className="space-y-3 rounded-xl border bg-card p-6 shadow-xs">
                                <h2 className="text-base font-semibold">
                                    Actions
                                </h2>
                                {order.can_mark_paid && (
                                    <ConfirmActionDialog
                                        trigger={
                                            <Button className="w-full">
                                                <CircleDollarSign />
                                                Mark paid
                                            </Button>
                                        }
                                        title={`Mark order ${order.number} paid?`}
                                        description="Do this once the invoice is paid. The customer is emailed that payment was received."
                                        confirmLabel="Mark paid"
                                        action={OrderPaymentController.store.form(
                                            order.id,
                                        )}
                                    />
                                )}
                                {order.can_cancel && (
                                    <ConfirmActionDialog
                                        trigger={
                                            <Button
                                                variant="outline"
                                                className="w-full text-destructive hover:text-destructive"
                                            >
                                                <Ban />
                                                Cancel order
                                            </Button>
                                        }
                                        title={`Cancel order ${order.number}?`}
                                        description="Its items go back into stock and the customer is emailed. This can't be undone."
                                        confirmLabel="Cancel order"
                                        action={OrderCancellationController.store.form(
                                            order.id,
                                        )}
                                        destructive
                                    />
                                )}
                            </section>
                        )}

                        <section className="rounded-xl border bg-card p-6 text-sm shadow-xs">
                            <h2 className="text-base font-semibold">
                                Customer
                            </h2>
                            <p className="mt-3">
                                {order.customer_name ?? address.name}
                            </p>
                            <a
                                href={`mailto:${order.email}?subject=${encodeURIComponent(`Invoice for order ${order.number}`)}`}
                                className="text-primary hover:underline"
                            >
                                {order.email}
                            </a>
                            {address.phone && (
                                <p className="text-muted-foreground">
                                    {address.phone}
                                </p>
                            )}
                        </section>

                        <section className="rounded-xl border bg-card p-6 text-sm shadow-xs">
                            <h2 className="text-base font-semibold">Ship to</h2>
                            <address className="mt-3 leading-relaxed text-muted-foreground not-italic">
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
                                {address.city}, {address.region}{' '}
                                {address.postal_code}
                                <br />
                                {address.country}
                            </address>
                            {order.tracking_number && (
                                <p className="mt-3">
                                    Tracking:{' '}
                                    {[order.carrier, order.tracking_number]
                                        .filter(Boolean)
                                        .join(' ')}
                                </p>
                            )}
                        </section>

                        {order.customer_note && (
                            <section className="rounded-xl border bg-card p-6 text-sm shadow-xs">
                                <h2 className="text-base font-semibold">
                                    Note
                                </h2>
                                <p className="mt-3 whitespace-pre-line text-muted-foreground">
                                    {order.customer_note}
                                </p>
                            </section>
                        )}

                        <section className="rounded-xl border bg-card p-6 text-sm shadow-xs">
                            <h2 className="text-base font-semibold">History</h2>
                            <ul className="mt-3 space-y-1 text-muted-foreground">
                                {timeline.map(([label, at]) => (
                                    <li key={label}>
                                        {label} {formatDateTime(at)}
                                    </li>
                                ))}
                                {order.research_use_confirmed_at && (
                                    <li>Research use confirmed at checkout</li>
                                )}
                            </ul>
                        </section>
                    </aside>
                </div>
            </div>
        </>
    );
}

OrderShow.layout = ({ order }: Props) => ({
    breadcrumbs: [
        { title: 'Orders', href: index() },
        { title: order.number, href: show(order.id) },
    ],
});
