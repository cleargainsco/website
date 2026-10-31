import { Head, Link } from '@inertiajs/react';
import { Package, Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/money';
import { stockDotClass, stockLabel } from '@/lib/stock-status';
import { cn } from '@/lib/utils';
import { create, edit, index } from '@/routes/admin/products';
import type { AdminProduct } from '@/types/admin-products';

export default function ProductsIndex({
    products,
}: {
    products: AdminProduct[];
}) {
    return (
        <>
            <Head title="Products" />

            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Products"
                        description="What's for sale, at what price, and how many are left."
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Add product
                        </Link>
                    </Button>
                </div>

                {products.length === 0 ? (
                    <div className="flex flex-col items-center rounded-xl border border-dashed px-6 py-16 text-center">
                        <Package className="size-8 text-muted-foreground" />
                        <p className="mt-4 font-medium">No products yet</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Add your first product to start selling.
                        </p>
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-xl border bg-card shadow-xs">
                        <table className="w-full text-sm">
                            <thead className="border-b text-left text-muted-foreground">
                                <tr>
                                    <th className="px-4 py-3 font-medium">
                                        Product
                                    </th>
                                    <th className="px-4 py-3 font-medium">
                                        Type
                                    </th>
                                    <th className="px-4 py-3 text-right font-medium">
                                        Price
                                    </th>
                                    <th className="px-4 py-3 font-medium">
                                        Stock
                                    </th>
                                    <th className="px-4 py-3 font-medium">
                                        Shop
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {products.map((product) => (
                                    <tr
                                        key={product.id}
                                        className="transition-colors hover:bg-accent/50"
                                    >
                                        <td className="px-4 py-3">
                                            <Link
                                                href={edit(product.id)}
                                                className="font-medium hover:underline"
                                            >
                                                {product.name}
                                                {product.amount &&
                                                    ` ${product.amount}`}
                                            </Link>
                                            <p className="text-xs text-muted-foreground">
                                                /shop/{product.slug}
                                            </p>
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {product.type_label}
                                        </td>
                                        <td className="px-4 py-3 text-right whitespace-nowrap tabular-nums">
                                            {product.base_price
                                                ? formatMoney(
                                                      product.base_price,
                                                  )
                                                : '—'}
                                            {product.tier_count > 1 && (
                                                <p className="text-xs text-muted-foreground">
                                                    {product.tier_count} tiers
                                                </p>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap">
                                            <span className="inline-flex items-center gap-2">
                                                <span
                                                    className={cn(
                                                        'size-2 rounded-full',
                                                        stockDotClass[
                                                            product.stock_status
                                                        ],
                                                    )}
                                                />
                                                <span className="tabular-nums">
                                                    {product.stock}
                                                </span>
                                                <span className="text-muted-foreground">
                                                    {
                                                        stockLabel[
                                                            product.stock_status
                                                        ]
                                                    }
                                                </span>
                                            </span>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex flex-wrap gap-1.5">
                                                <Badge
                                                    variant={
                                                        product.is_active
                                                            ? 'default'
                                                            : 'outline'
                                                    }
                                                >
                                                    {product.is_active
                                                        ? 'Visible'
                                                        : 'Hidden'}
                                                </Badge>
                                                {product.is_featured && (
                                                    <Badge variant="secondary">
                                                        Featured
                                                    </Badge>
                                                )}
                                            </div>
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

ProductsIndex.layout = {
    breadcrumbs: [{ title: 'Products', href: index() }],
};
