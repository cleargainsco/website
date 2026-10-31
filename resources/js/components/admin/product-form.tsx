import { Form } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { ProductImageField } from '@/components/admin/product-image-field';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type {
    ProductFormData,
    ProductPriceInput,
    ProductTypeOption,
} from '@/types/admin-products';
import { FieldKind } from '@/types/enums';
import type { RouteFormDefinition } from '@/wayfinder';

type PriceRow = ProductPriceInput & { key: number };

const textareaClass =
    'flex min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm';

const slugify = (value: string): string =>
    value
        .toLowerCase()
        .normalize('NFKD')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

export function ProductForm({
    action,
    productTypes,
    currency,
    product,
    submitLabel,
    onCancel,
}: {
    action: RouteFormDefinition<'post'>;
    productTypes: ProductTypeOption[];
    currency: string;
    product?: ProductFormData;
    submitLabel: string;
    onCancel: () => void;
}) {
    const [type, setType] = useState(product?.type ?? productTypes[0]?.key);
    const [slug, setSlug] = useState(product?.slug ?? '');
    const [slugEdited, setSlugEdited] = useState(product !== undefined);
    const [prices, setPrices] = useState<PriceRow[]>(
        (product?.prices ?? [{ min_quantity: 1, unit_price: '' }]).map(
            (price, index) => ({ ...price, key: index }),
        ),
    );

    const fields =
        productTypes.find((option) => option.key === type)?.fields ?? [];

    const updatePrice = (key: number, change: Partial<ProductPriceInput>) =>
        setPrices((rows) =>
            rows.map((row) => (row.key === key ? { ...row, ...change } : row)),
        );

    return (
        <Form
            {...action}
            options={{ preserveScroll: true }}
            noValidate
            className="flex min-h-0 flex-1 flex-col"
        >
            {({ processing, errors }) => (
                <>
                    <div className="flex-1 divide-y overflow-y-auto px-6">
                        <Section title="Product">
                            <div className="grid gap-2">
                                <Label htmlFor="type">Type</Label>
                                <Select
                                    name="type"
                                    value={type}
                                    onValueChange={setType}
                                >
                                    <SelectTrigger
                                        id="type"
                                        className="w-full sm:w-72"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {productTypes.map((option) => (
                                            <SelectItem
                                                key={option.key}
                                                value={option.key}
                                            >
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.type} />
                            </div>

                            <div className="grid gap-6 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={product?.name}
                                        required
                                        onChange={(event) => {
                                            if (!slugEdited) {
                                                setSlug(
                                                    slugify(event.target.value),
                                                );
                                            }
                                        }}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="slug">URL slug</Label>
                                    <Input
                                        id="slug"
                                        name="slug"
                                        value={slug}
                                        required
                                        onChange={(event) => {
                                            setSlugEdited(true);
                                            setSlug(event.target.value);
                                        }}
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        The product's address in the shop:
                                        /shop/
                                        {slug || 'your-product'}
                                    </p>
                                    <InputError message={errors.slug} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="summary">Summary</Label>
                                <textarea
                                    id="summary"
                                    name="summary"
                                    rows={2}
                                    defaultValue={product?.summary}
                                    required
                                    className={textareaClass}
                                />
                                <p className="text-xs text-muted-foreground">
                                    One or two sentences, shown on product cards
                                    and at the top of the product page.
                                </p>
                                <InputError message={errors.summary} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">
                                    Description{' '}
                                    <span className="font-normal text-muted-foreground">
                                        (optional)
                                    </span>
                                </Label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows={5}
                                    defaultValue={product?.description ?? ''}
                                    className={textareaClass}
                                />
                                <InputError message={errors.description} />
                            </div>
                        </Section>

                        <Section title="Image">
                            <ProductImageField
                                currentUrl={product?.image_url ?? null}
                                error={errors.image}
                            />
                        </Section>

                        {fields.length > 0 && (
                            <Section title="Details">
                                {fields.map((field) => (
                                    <div
                                        key={field.name}
                                        className="grid gap-2"
                                    >
                                        <Label
                                            htmlFor={`details-${field.name}`}
                                        >
                                            {field.label}
                                            {!field.required && (
                                                <span className="font-normal text-muted-foreground">
                                                    {' '}
                                                    (optional)
                                                </span>
                                            )}
                                        </Label>
                                        {field.kind === FieldKind.Textarea ? (
                                            <textarea
                                                id={`details-${field.name}`}
                                                name={`details[${field.name}]`}
                                                rows={3}
                                                defaultValue={
                                                    product?.details[
                                                        field.name
                                                    ] ?? ''
                                                }
                                                className={textareaClass}
                                            />
                                        ) : (
                                            <Input
                                                id={`details-${field.name}`}
                                                name={`details[${field.name}]`}
                                                defaultValue={
                                                    product?.details[
                                                        field.name
                                                    ] ?? ''
                                                }
                                                required={field.required}
                                            />
                                        )}
                                        {field.help && (
                                            <p className="text-xs text-muted-foreground">
                                                {field.help}
                                            </p>
                                        )}
                                        <InputError
                                            message={
                                                errors[`details.${field.name}`]
                                            }
                                        />
                                    </div>
                                ))}
                            </Section>
                        )}

                        <Section
                            title="Pricing"
                            description={`Price per unit in ${currency}. Add a tier for each quantity that gets a lower unit price.`}
                        >
                            <div className="space-y-3">
                                <div className="grid grid-cols-[1fr_1fr_2.25rem] gap-3 text-sm font-medium">
                                    <span>From quantity</span>
                                    <span>Unit price ({currency})</span>
                                </div>
                                {prices.map((row, index) => (
                                    <div key={row.key} className="space-y-1">
                                        <div className="grid grid-cols-[1fr_1fr_2.25rem] items-center gap-3">
                                            <Input
                                                aria-label="From quantity"
                                                type="number"
                                                min={1}
                                                name={`prices[${index}][min_quantity]`}
                                                value={row.min_quantity}
                                                onChange={(event) =>
                                                    updatePrice(row.key, {
                                                        min_quantity: Number(
                                                            event.target.value,
                                                        ),
                                                    })
                                                }
                                            />
                                            <Input
                                                aria-label="Unit price"
                                                inputMode="decimal"
                                                placeholder="0.00"
                                                name={`prices[${index}][unit_price]`}
                                                value={row.unit_price}
                                                onChange={(event) =>
                                                    updatePrice(row.key, {
                                                        unit_price:
                                                            event.target.value,
                                                    })
                                                }
                                            />
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                aria-label="Remove tier"
                                                disabled={prices.length === 1}
                                                onClick={() =>
                                                    setPrices((rows) =>
                                                        rows.filter(
                                                            (candidate) =>
                                                                candidate.key !==
                                                                row.key,
                                                        ),
                                                    )
                                                }
                                            >
                                                <Trash2 />
                                            </Button>
                                        </div>
                                        <InputError
                                            message={
                                                errors[
                                                    `prices.${index}.min_quantity`
                                                ] ??
                                                errors[
                                                    `prices.${index}.unit_price`
                                                ]
                                            }
                                        />
                                    </div>
                                ))}
                                <InputError message={errors.prices} />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        setPrices((rows) => [
                                            ...rows,
                                            {
                                                key:
                                                    Math.max(
                                                        ...rows.map(
                                                            (candidate) =>
                                                                candidate.key,
                                                        ),
                                                    ) + 1,
                                                min_quantity:
                                                    Math.max(
                                                        ...rows.map(
                                                            (candidate) =>
                                                                candidate.min_quantity,
                                                        ),
                                                    ) + 1,
                                                unit_price: '',
                                            },
                                        ])
                                    }
                                >
                                    <Plus />
                                    Add tier
                                </Button>
                            </div>
                        </Section>

                        <Section title="Stock and visibility">
                            <div className="grid gap-2">
                                <Label htmlFor="stock">Units in stock</Label>
                                <Input
                                    id="stock"
                                    name="stock"
                                    type="number"
                                    min={0}
                                    defaultValue={product?.stock ?? 0}
                                    className="w-full sm:w-40"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Goes down automatically as orders are
                                    placed.
                                </p>
                                <InputError message={errors.stock} />
                            </div>

                            <div className="space-y-3">
                                <label className="flex items-start gap-3">
                                    <Checkbox
                                        name="is_active"
                                        value="1"
                                        defaultChecked={
                                            product?.is_active ?? true
                                        }
                                        className="mt-0.5"
                                    />
                                    <span className="text-sm">
                                        <span className="font-medium">
                                            Show in the shop
                                        </span>
                                        <span className="block text-muted-foreground">
                                            Hidden products stay here but
                                            customers can't see or order them.
                                        </span>
                                    </span>
                                </label>
                                <label className="flex items-start gap-3">
                                    <Checkbox
                                        name="is_featured"
                                        value="1"
                                        defaultChecked={product?.is_featured}
                                        className="mt-0.5"
                                    />
                                    <span className="text-sm">
                                        <span className="font-medium">
                                            Feature on the home page
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </Section>
                    </div>

                    <div className="flex items-center gap-3 border-t px-6 py-4">
                        <Button disabled={processing}>{submitLabel}</Button>
                        <Button
                            type="button"
                            variant="ghost"
                            onClick={onCancel}
                        >
                            Cancel
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}

function Section({
    title,
    description,
    children,
}: {
    title: string;
    description?: string;
    children: React.ReactNode;
}) {
    return (
        <section className="space-y-6 py-6">
            <header>
                <h2 className="text-base font-semibold">{title}</h2>
                {description && (
                    <p className="mt-1 text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
            </header>
            {children}
        </section>
    );
}
