import { AccountPanel } from '@/components/account/account-panel';
import { NoOrders } from '@/components/account/no-orders';
import { OrderList } from '@/components/account/order-list';
import type { Order } from '@/types/shop';

export default function AccountOrders({ orders }: { orders: Order[] }) {
    return (
        <>
            <AccountPanel
                title="Orders"
                description="Your order history, status and tracking."
            >
                {orders.length === 0 ? (
                    <NoOrders />
                ) : (
                    <OrderList orders={orders} />
                )}
            </AccountPanel>
        </>
    );
}
