import { Head } from '@inertiajs/react';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import { ProductForm } from '@/components/admin/product-form';
import Heading from '@/components/heading';
import { create, index } from '@/routes/admin/products';
import type { ProductTypeOption } from '@/types/admin-products';

export default function ProductsCreate({
    productTypes,
    currency,
}: {
    productTypes: ProductTypeOption[];
    currency: string;
}) {
    return (
        <>
            <Head title="Add product" />

            <div className="max-w-3xl space-y-6 p-4 md:p-6">
                <Heading title="Add product" />
                <ProductForm
                    action={ProductController.store.form()}
                    productTypes={productTypes}
                    currency={currency}
                    submitLabel="Create product"
                />
            </div>
        </>
    );
}

ProductsCreate.layout = {
    breadcrumbs: [
        { title: 'Products', href: index() },
        { title: 'Add product', href: create() },
    ],
};
