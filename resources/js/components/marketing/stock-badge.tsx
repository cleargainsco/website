import { stockDotClass, stockLabel } from '@/lib/stock-status';
import { cn } from '@/lib/utils';
import type { StockStatus } from '@/types/enums';

export function StockBadge({
    status,
    className,
}: {
    status: StockStatus;
    className?: string;
}) {
    return (
        <span
            className={cn(
                'inline-flex items-center gap-2 text-xs font-medium text-charcoal/70',
                className,
            )}
        >
            <span
                className={cn('size-2 rounded-full', stockDotClass[status])}
            />
            {stockLabel[status]}
        </span>
    );
}
