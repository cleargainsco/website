import { Link } from '@inertiajs/react';
import { Package } from 'lucide-react';
import { brandButton } from '@/lib/brand-button';
import { index as shopIndex } from '@/routes/shop';

export function NoOrders() {
    return (
        <div className="flex flex-col items-center rounded-2xl border border-dashed border-charcoal/15 px-6 py-12 text-center">
            <span className="flex size-12 items-center justify-center rounded-full bg-mist/50 text-deep-ocean">
                <Package className="size-5" />
            </span>
            <p className="mt-4 font-medium">No orders yet</p>
            <p className="mt-1 max-w-sm text-sm text-charcoal/60">
                Orders you place will show here with their status and tracking.
            </p>
            <Link
                href={shopIndex()}
                className={brandButton({ size: 'sm', className: 'mt-6' })}
            >
                Browse the shop
            </Link>
        </div>
    );
}
