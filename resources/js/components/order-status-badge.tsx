import { orderStatusClass, orderStatusLabel } from '@/lib/order-status';
import { cn } from '@/lib/utils';
import type { OrderStatus } from '@/types/enums';

export function OrderStatusBadge({ status }: { status: OrderStatus }) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-inset',
                orderStatusClass[status],
            )}
        >
            {orderStatusLabel[status]}
        </span>
    );
}
