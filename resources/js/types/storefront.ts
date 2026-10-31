import type { StockStatus } from '@/types/enums';
import type { Money } from '@/types/money';

export type PriceTier = {
    quantity: number;
    unit: Money;
    total: Money;
};

export type Batch = {
    number: string;
    tested_at: string;
    purity: string | null;
    lab: string | null;
    report_url: string;
};

export type Product = {
    slug: string;
    name: string;
    amount: string;
    summary: string;
    stock: StockStatus;
    storage: string;
    featured: boolean;
    price: Money;
    tiers: PriceTier[];
    batches: Batch[];
};
