import { Head, Link, router } from '@inertiajs/react';
import {
    Archive,
    ArchiveRestore,
    ImageIcon,
    Package,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import ProductRestorationController from '@/actions/App/Http/Controllers/Admin/ProductRestorationController';
import { ProductForm } from '@/components/admin/product-form';
import { ConfirmActionDialog } from '@/components/confirm-action-dialog';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { formatMoney } from '@/lib/money';
import { stockDotClass, stockLabel } from '@/lib/stock-status';
import { cn } from '@/lib/utils';
import { create, edit, index } from '@/routes/admin/products';
import { index as archivedIndex } from '@/routes/admin/products/archived';
import type {
    AdminProduct,
    ProductFormData,
    ProductTypeOption,
} from '@/types/admin-products';

type SheetProps =
    | { sheet?: undefined }
    | {
          sheet: 'create';
          productTypes: ProductTypeOption[];
          currency: string;
      }
    | {
          sheet: 'edit';
          product: ProductFormData;
          productTypes: ProductTypeOption[];
          currency: string;
      };

type Props = {
    view: 'active' | 'archived';
    products: AdminProduct[];
} & SheetProps;

export default function ProductsIndex(props: Props) {
    const { view, products } = props;
    const archived = view === 'archived';

    return (
        <>
            <Head title="Products" />

            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Products"
                        description="What's for sale, at what price, and how many are left."
                    />
                    {!archived && (
                        <Button asChild>
                            <Link href={create()} preserveScroll>
                                <Plus />
                                Add product
                            </Link>
                        </Button>
                    )}
                </div>

                <nav className="flex gap-1 border-b" aria-label="Product views">
                    {[
                        {
                            label: 'Products',
                            href: index(),
                            current: !archived,
                        },
                        {
                            label: 'Archived',
                            href: archivedIndex(),
                            current: archived,
                        },
                    ].map((tab) => (
                        <Link
                            key={tab.label}
                            href={tab.href}
                            className={cn(
                                '-mb-px border-b-2 px-3 py-2 text-sm font-medium transition-colors',
                                tab.current
                                    ? 'border-primary text-foreground'
                                    : 'border-transparent text-muted-foreground hover:text-foreground',
                            )}
                        >
                            {tab.label}
                        </Link>
                    ))}
                </nav>

                {products.length === 0 ? (
                    <div className="flex flex-col items-center rounded-xl border border-dashed px-6 py-16 text-center">
                        <Package className="size-8 text-muted-foreground" />
                        <p className="mt-4 font-medium">
                            {archived ? 'Nothing archived' : 'No products yet'}
                        </p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {archived
                                ? 'Archived products are kept here, out of the shop, until you restore them.'
                                : 'Add your first product to start selling.'}
                        </p>
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-xl border bg-card shadow-xs">
                        <table className="w-full text-sm">
                            <thead className="border-b text-left text-muted-foreground">
                                <tr>
                                    <th className="w-14 py-3 pl-4">
                                        <span className="sr-only">Image</span>
                                    </th>
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
                                    <th className="px-4 py-3">
                                        <span className="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {products.map((product) => (
                                    <tr
                                        key={product.id}
                                        className="transition-colors hover:bg-accent/50"
                                    >
                                        <td className="w-14 py-3 pl-4">
                                            <div className="flex size-10 items-center justify-center overflow-hidden rounded-md border bg-muted">
                                                {product.thumbnail_url ? (
                                                    <img
                                                        src={
                                                            product.thumbnail_url
                                                        }
                                                        alt=""
                                                        className="size-full object-cover"
                                                    />
                                                ) : (
                                                    <ImageIcon className="size-4 text-muted-foreground" />
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3">
                                            {archived ? (
                                                <span className="font-medium">
                                                    {product.name}
                                                    {product.amount &&
                                                        ` ${product.amount}`}
                                                </span>
                                            ) : (
                                                <Link
                                                    href={edit(product.id)}
                                                    preserveScroll
                                                    className="font-medium hover:underline"
                                                >
                                                    {product.name}
                                                    {product.amount &&
                                                        ` ${product.amount}`}
                                                </Link>
                                            )}
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
                                        <td className="px-4 py-3">
                                            <div className="flex justify-end gap-1">
                                                {archived ? (
                                                    <ConfirmActionDialog
                                                        trigger={
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                aria-label={`Restore ${product.name}`}
                                                                title="Restore"
                                                            >
                                                                <ArchiveRestore />
                                                            </Button>
                                                        }
                                                        title={`Restore ${product.name}?`}
                                                        description="It goes back to your products as it was. It shows in the shop again only if it's set to visible."
                                                        confirmLabel="Restore"
                                                        action={ProductRestorationController.store.form(
                                                            product.id,
                                                        )}
                                                    />
                                                ) : (
                                                    <>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={edit(
                                                                    product.id,
                                                                )}
                                                                preserveScroll
                                                                aria-label={`Edit ${product.name}`}
                                                                title="Edit"
                                                            >
                                                                <Pencil />
                                                            </Link>
                                                        </Button>
                                                        <ConfirmActionDialog
                                                            trigger={
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="text-destructive hover:text-destructive"
                                                                    aria-label={`${product.has_been_ordered ? 'Archive' : 'Delete'} ${product.name}`}
                                                                    title={
                                                                        product.has_been_ordered
                                                                            ? 'Archive'
                                                                            : 'Delete'
                                                                    }
                                                                >
                                                                    {product.has_been_ordered ? (
                                                                        <Archive />
                                                                    ) : (
                                                                        <Trash2 />
                                                                    )}
                                                                </Button>
                                                            }
                                                            title={`${product.has_been_ordered ? 'Archive' : 'Delete'} ${product.name}?`}
                                                            description={
                                                                product.has_been_ordered
                                                                    ? "It's been ordered, so it's archived rather than deleted: it leaves the shop and this list, and its orders keep their history. You can restore it from the Archived tab."
                                                                    : "Nobody has ordered it, so it's deleted for good, along with its image and prices. This can't be undone."
                                                            }
                                                            confirmLabel={
                                                                product.has_been_ordered
                                                                    ? 'Archive'
                                                                    : 'Delete'
                                                            }
                                                            action={ProductController.destroy.form(
                                                                product.id,
                                                            )}
                                                            destructive
                                                        />
                                                    </>
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

            {props.sheet && (
                <ProductSheet
                    key={props.sheet === 'edit' ? props.product.id : 'create'}
                    {...props}
                />
            )}
        </>
    );
}

function ProductSheet(props: Exclude<Props, { sheet?: undefined }>) {
    const [open, setOpen] = useState(true);
    const product = props.sheet === 'edit' ? props.product : undefined;

    const close = () => {
        setOpen(false);
        setTimeout(() => router.visit(index(), { preserveScroll: true }), 300);
    };

    return (
        <Sheet open={open} onOpenChange={(isOpen) => !isOpen && close()}>
            <SheetContent className="w-full gap-0 sm:max-w-3xl">
                <SheetHeader className="border-b px-6 py-5">
                    <SheetTitle>
                        {product ? `Edit ${product.name}` : 'Add product'}
                    </SheetTitle>
                    <SheetDescription>
                        {product
                            ? 'Changes show in the shop as soon as you save.'
                            : 'Set its details, prices and stock.'}
                    </SheetDescription>
                </SheetHeader>
                <ProductForm
                    action={
                        product
                            ? ProductController.update.form(product.id)
                            : ProductController.store.form()
                    }
                    productTypes={props.productTypes}
                    currency={props.currency}
                    product={product}
                    submitLabel={product ? 'Save changes' : 'Create product'}
                    onCancel={close}
                />
            </SheetContent>
        </Sheet>
    );
}

ProductsIndex.layout = {
    breadcrumbs: [{ title: 'Products', href: index() }],
};
