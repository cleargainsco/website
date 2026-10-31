import { VialLabel } from '@/components/marketing/vial-label';
import { cn } from '@/lib/utils';
import type { Product } from '@/types/storefront';

export function ProductVisual({
    product,
    className,
}: {
    product: Pick<Product, 'name' | 'amount' | 'image_url'>;
    className?: string;
}) {
    if (product.image_url) {
        return (
            <img
                src={product.image_url}
                alt={`${product.name} ${product.amount}`}
                className={cn(
                    'aspect-4/5 w-full rounded-2xl bg-linen object-cover ring-1 ring-charcoal/5',
                    className,
                )}
            />
        );
    }

    return (
        <VialLabel
            name={product.name}
            amount={product.amount}
            className={className}
        />
    );
}
