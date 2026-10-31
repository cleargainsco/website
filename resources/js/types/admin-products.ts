import type { FieldKind, StockStatus } from '@/types/enums';
import type { Money } from '@/types/money';

export type AdminProduct = {
    id: string;
    name: string;
    slug: string;
    thumbnail_url: string | null;
    type_label: string;
    amount: string | null;
    stock: number;
    stock_status: StockStatus;
    base_price: Money | null;
    tier_count: number;
    is_active: boolean;
    is_featured: boolean;
    has_been_ordered: boolean;
};

export type ProductField = {
    name: string;
    label: string;
    kind: FieldKind;
    required: boolean;
    help: string | null;
};

export type ProductTypeOption = {
    key: string;
    label: string;
    fields: ProductField[];
};

export type ProductPriceInput = {
    min_quantity: number;
    unit_price: string;
};

export type ProductFormData = {
    id: string;
    type: string;
    name: string;
    slug: string;
    summary: string;
    description: string | null;
    details: Record<string, string | null>;
    image_url: string | null;
    stock: number;
    is_active: boolean;
    is_featured: boolean;
    prices: ProductPriceInput[];
};

export type AdminProductBatch = {
    id: string;
    number: string;
    tested_at: string;
    purity: string | null;
    lab: string | null;
    certificate_url: string | null;
};
