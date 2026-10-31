import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { OrderStatusBadge } from '@/components/order-status-badge';
import { formatDate } from '@/lib/dates';
import { formatMoney } from '@/lib/money';
import { show as orderShow } from '@/routes/account/orders';
import type { Order } from '@/types/shop';

export function OrderList({ orders }: { orders: Order[] }) {
    return (
        <ul className="divide-y divide-charcoal/10">
            {orders.map((order) => (
                <li key={order.id}>
                    <Link
                        href={orderShow(order.id)}
                        className="-mx-3 flex flex-wrap items-center gap-x-6 gap-y-2 rounded-2xl px-3 py-4 transition-colors hover:bg-linen"
                    >
                        <div className="min-w-32 flex-1">
                            <p className="font-medium text-charcoal">
                                {order.number}
                            </p>
                            <p className="text-sm text-charcoal/60">
                                {formatDate(order.placed_at)}
                                {order.unit_count !== undefined &&
                                    ` · ${order.unit_count} ${order.unit_count === 1 ? 'vial' : 'vials'}`}
                            </p>
                        </div>
                        <OrderStatusBadge status={order.status} />
                        <p className="w-24 text-right font-medium tabular-nums">
                            {formatMoney(order.total)}
                        </p>
                        <ChevronRight className="size-4 text-charcoal/40" />
                    </Link>
                </li>
            ))}
        </ul>
    );
}
