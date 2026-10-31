import { Link } from '@inertiajs/react';
import {
    CircleDollarSign,
    Clock,
    Package,
    PackageCheck,
    Users,
} from 'lucide-react';
import { OrdersChart } from '@/components/admin/dashboard/orders-chart';
import { StatTile } from '@/components/admin/dashboard/stat-tile';
import { OrderStatusBadge } from '@/components/order-status-badge';
import { formatDate } from '@/lib/dates';
import { formatMoney } from '@/lib/money';
import { stockDotClass } from '@/lib/stock-status';
import { dashboard } from '@/routes';
import { index as ordersIndex, show as orderShow } from '@/routes/admin/orders';
import { edit as productEdit } from '@/routes/admin/products';
import type { DashboardProps } from '@/types/dashboard';
import type { Money } from '@/types/money';

function revenueChange(current: Money, previous: Money): string {
    if (previous.minor === 0) {
        return current.minor === 0
            ? 'No paid orders yet this month'
            : 'Up from nothing last month';
    }

    const change = Math.round(
        ((current.minor - previous.minor) / previous.minor) * 100,
    );

    return `${change >= 0 ? '+' : ''}${change}% on this point last month`;
}

export default function Dashboard({
    revenue,
    awaitingPayment,
    readyToShip,
    customers,
    dailyOrders,
    recentOrders,
    lowStock,
}: DashboardProps) {
    return (
        <div className="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatTile
                    label="Revenue this month"
                    value={formatMoney(revenue.this_month)}
                    detail={revenueChange(
                        revenue.this_month,
                        revenue.last_month_to_date,
                    )}
                    icon={CircleDollarSign}
                />
                <StatTile
                    label="Awaiting payment"
                    value={awaitingPayment.count}
                    detail={`${formatMoney(awaitingPayment.value)} to invoice`}
                    icon={Clock}
                    href={ordersIndex()}
                />
                <StatTile
                    label="Ready to ship"
                    value={readyToShip}
                    detail={
                        readyToShip === 0
                            ? 'Nothing waiting to go out'
                            : 'Paid and waiting to go out'
                    }
                    icon={PackageCheck}
                    href={ordersIndex()}
                />
                <StatTile
                    label="Customers"
                    value={customers.total}
                    detail={`${customers.new_this_month} new this month`}
                    icon={Users}
                />
            </div>

            <section className="rounded-xl border bg-card p-5 shadow-xs">
                <header className="mb-6">
                    <h2 className="font-semibold">Orders placed</h2>
                    <p className="text-sm text-muted-foreground">
                        Each day, over the last {dailyOrders.length} days
                    </p>
                </header>
                <OrdersChart days={dailyOrders} />
            </section>

            <div className="grid gap-6 lg:grid-cols-[1fr_20rem]">
                <section className="rounded-xl border bg-card shadow-xs">
                    <header className="flex items-center justify-between border-b px-5 py-4">
                        <h2 className="font-semibold">Recent orders</h2>
                        <Link
                            href={ordersIndex()}
                            className="text-sm text-muted-foreground hover:text-foreground"
                        >
                            View all
                        </Link>
                    </header>
                    {recentOrders.length === 0 ? (
                        <p className="px-5 py-10 text-center text-sm text-muted-foreground">
                            Orders placed in the shop show up here.
                        </p>
                    ) : (
                        <ul className="divide-y">
                            {recentOrders.map((order) => (
                                <li key={order.id}>
                                    <Link
                                        href={orderShow(order.id)}
                                        className="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-3 text-sm transition-colors hover:bg-accent/50"
                                    >
                                        <span className="w-24 font-medium">
                                            {order.number}
                                        </span>
                                        <span className="min-w-0 flex-1 truncate">
                                            {order.customer_name ??
                                                order.shipping_address.name}
                                            <span className="ml-2 text-muted-foreground">
                                                {formatDate(order.placed_at)}
                                            </span>
                                        </span>
                                        <OrderStatusBadge
                                            status={order.status}
                                        />
                                        <span className="w-20 text-right tabular-nums">
                                            {formatMoney(order.subtotal)}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section className="rounded-xl border bg-card shadow-xs">
                    <header className="border-b px-5 py-4">
                        <h2 className="font-semibold">Running low</h2>
                    </header>
                    {lowStock.length === 0 ? (
                        <p className="px-5 py-10 text-center text-sm text-muted-foreground">
                            Every product is well stocked.
                        </p>
                    ) : (
                        <ul className="divide-y">
                            {lowStock.map((product) => (
                                <li key={product.id}>
                                    <Link
                                        href={productEdit(product.id)}
                                        className="flex items-center gap-3 px-5 py-3 text-sm transition-colors hover:bg-accent/50"
                                    >
                                        <span className="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-md bg-muted">
                                            {product.thumbnail_url ? (
                                                <img
                                                    src={product.thumbnail_url}
                                                    alt=""
                                                    className="size-full object-cover"
                                                />
                                            ) : (
                                                <Package className="size-4 text-muted-foreground" />
                                            )}
                                        </span>
                                        <span className="min-w-0 flex-1 truncate">
                                            {product.name}
                                        </span>
                                        <span className="flex items-center gap-1.5 tabular-nums">
                                            <span
                                                className={`size-2 rounded-full ${stockDotClass[product.stock_status]}`}
                                            />
                                            {product.stock} left
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </div>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
