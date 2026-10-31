import { Link } from '@inertiajs/react';
import { ReceiptText } from 'lucide-react';
import Heading from '@/components/heading';
import { OrderStatusBadge } from '@/components/order-status-badge';
import { formatDate } from '@/lib/dates';
import { formatMoney } from '@/lib/money';
import { index, show } from '@/routes/admin/orders';
import type { AdminOrder } from '@/types/shop';

export default function OrdersIndex({ orders }: { orders: AdminOrder[] }) {
    return (
        <>
            <div className="space-y-6 p-4 md:p-6">
                <Heading
                    title="Orders"
                    description="New orders arrive awaiting payment: send the invoice, then mark them paid and shipped."
                />

                {orders.length === 0 ? (
                    <div className="flex flex-col items-center rounded-xl border border-dashed px-6 py-16 text-center">
                        <ReceiptText className="size-8 text-muted-foreground" />
                        <p className="mt-4 font-medium">No orders yet</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Orders placed in the shop show up here.
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
                                        Customer
                                    </th>
                                    <th className="px-4 py-3 font-medium">
                                        Placed
                                    </th>
                                    <th className="px-4 py-3 font-medium">
                                        Status
                                    </th>
                                    <th className="px-4 py-3 text-right font-medium">
                                        Subtotal
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
                                                href={show(order.id)}
                                                className="font-medium hover:underline"
                                            >
                                                {order.number}
                                            </Link>
                                            <p className="text-xs text-muted-foreground">
                                                {order.unit_count}{' '}
                                                {order.unit_count === 1
                                                    ? 'unit'
                                                    : 'units'}
                                            </p>
                                        </td>
                                        <td className="px-4 py-3">
                                            {order.customer_name ??
                                                order.shipping_address.name}
                                            <p className="text-xs text-muted-foreground">
                                                {order.email}
                                            </p>
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
                                            {formatMoney(order.subtotal)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}

OrdersIndex.layout = {
    breadcrumbs: [{ title: 'Orders', href: index() }],
};
