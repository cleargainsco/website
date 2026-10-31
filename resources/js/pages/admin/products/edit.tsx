import { Head } from '@inertiajs/react';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import { ProductForm } from '@/components/admin/product-form';
import Heading from '@/components/heading';
import { edit, index } from '@/routes/admin/products';
import type {
    ProductFormData,
    ProductTypeOption,
} from '@/types/admin-products';

type Props = {
    product: ProductFormData;
    productTypes: ProductTypeOption[];
    currency: string;
};

export default function ProductsEdit({
    product,
    productTypes,
    currency,
}: Props) {
    return (
        <>
            <Head title={`Edit ${product.name}`} />

            <div className="max-w-3xl space-y-6 p-4 md:p-6">
                <Heading title={`Edit ${product.name}`} />
                <ProductForm
                    key={product.id}
                    action={ProductController.update.form(product.id)}
                    productTypes={productTypes}
                    currency={currency}
                    product={product}
                    submitLabel="Save changes"
                />
            </div>
        </>
    );
}

ProductsEdit.layout = ({ product }: Props) => ({
    breadcrumbs: [
        { title: 'Products', href: index() },
        { title: product.name, href: edit(product.id) },
    ],
});
