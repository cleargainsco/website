import { Link } from '@inertiajs/react';
import { ReceiptText } from 'lucide-react';
import Heading from '@/components/heading';
import { OrderStatusBadge } from '@/components/order-status-badge';
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/dates';
import { formatMoney } from '@/lib/money';
import { index } from '@/routes/admin/customers';
import { show as orderShow } from '@/routes/admin/orders';
import type { Customer, CustomerWelcomeCode } from '@/types/customers';
import type { AdminOrder } from '@/types/shop';

function Stat({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border bg-card p-4 shadow-xs">
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className="mt-1 text-xl font-semibold tabular-nums">{value}</p>
        </div>
    );
}

export default function CustomerShow({
    customer,
    welcome_code,
    orders,
}: {
    customer: Customer;
    welcome_code: CustomerWelcomeCode | null;
    orders: AdminOrder[];
}) {
    return (
        <div className="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
            <div className="flex flex-wrap items-start justify-between gap-4">
                <Heading
                    title={customer.name}
                    description={`Customer since ${customer.joined_at ? formatDate(customer.joined_at) : '—'}`}
                />
                <a
                    href={`mailto:${customer.email}`}
                    className="text-sm text-primary hover:underline"
                >
                    {customer.email}
                </a>
            </div>

            <div className="grid gap-4 sm:grid-cols-3">
                <Stat label="Orders" value={String(customer.orders_count)} />
                <Stat
                    label="Spent (paid and shipped)"
                    value={formatMoney(customer.spent)}
                />
                <Stat
                    label="Last order"
                    value={
                        customer.last_order_at
                            ? formatDate(customer.last_order_at)
                            : '—'
                    }
                />
            </div>

            <div className="grid gap-6 lg:grid-cols-[1fr_20rem]">
                <section className="space-y-3">
                    <h2 className="text-sm font-medium">Orders</h2>
                    {orders.length === 0 ? (
                        <div className="flex flex-col items-center rounded-xl border border-dashed px-6 py-12 text-center">
                            <ReceiptText className="size-7 text-muted-foreground" />
                            <p className="mt-3 text-sm text-muted-foreground">
                                No orders yet.
                            </p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto rounded-xl border bg-card shadow-xs">
                            <table className="w-full text-sm">
                                <thead className="border-b text-left text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-3 font-medium">
                                            Order
                                        </th>
                                        <th className="px-4 py-3 font-medium">
                                            Placed
                                        </th>
                                        <th className="px-4 py-3 font-medium">
                                            Status
                                        </th>
                                        <th className="px-4 py-3 text-right font-medium">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {orders.map((order) => (
                                        <tr
                                            key={order.id}
                                            className="transition-colors hover:bg-accent/50"
                                        >
                                            <td className="px-4 py-3">
                                                <Link
                                                    href={orderShow(order.id)}
                                                    className="font-medium hover:underline"
                                                >
                                                    {order.number}
                                                </Link>
                                                {order.discount && (
                                                    <p className="text-xs text-muted-foreground">
                                                        Code{' '}
                                                        {order.discount.code}
                                                    </p>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                                {formatDate(order.placed_at)}
                                            </td>
                                            <td className="px-4 py-3">
                                                <OrderStatusBadge
                                                    status={order.status}
                                                />
                                            </td>
                                            <td className="px-4 py-3 text-right tabular-nums">
                                                {formatMoney(order.total)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>

                <aside className="h-fit space-y-4 rounded-xl border bg-card p-5 text-sm shadow-xs">
                    <div>
                        <h2 className="font-medium">Account</h2>
                        <p className="mt-1 text-muted-foreground">
                            {customer.email_verified
                                ? 'Email verified'
                                : "Email not verified yet, so they can't check out."}
                        </p>
                    </div>
                    <div>
                        <h2 className="font-medium">Marketing</h2>
                        <p className="mt-1 text-muted-foreground">
                            {customer.marketing_consent
                                ? 'Agreed to marketing emails.'
                                : 'Not on the marketing list.'}
                        </p>
                    </div>
                    {welcome_code && (
                        <div>
                            <h2 className="font-medium">Welcome code</h2>
                            <p className="mt-1 flex items-center gap-2">
                                <span className="font-mono">
                                    {welcome_code.code}
                                </span>
                                <Badge variant="outline">
                                    {welcome_code.used
                                        ? 'Redeemed'
                                        : welcome_code.expired
                                          ? 'Expired'
                                          : 'Not used yet'}
                                </Badge>
                            </p>
                        </div>
                    )}
                </aside>
            </div>
        </div>
    );
}

CustomerShow.layout = {
    breadcrumbs: [{ title: 'Customers', href: index() }],
};
