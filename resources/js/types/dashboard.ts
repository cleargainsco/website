import type { StockStatus } from '@/types/enums';
import type { Money } from '@/types/money';
import type { AdminOrder } from '@/types/shop';

export type DailyOrders = {
    date: string;
    orders: number;
    value: Money;
};

export type LowStockProduct = {
    id: string;
    name: string;
    stock: number;
    stock_status: StockStatus;
    thumbnail_url: string | null;
};

export type DashboardProps = {
    revenue: { this_month: Money; last_month_to_date: Money };
    awaitingPayment: { count: number; value: Money };
    readyToShip: number;
    customers: { total: number; new_this_month: number };
    dailyOrders: DailyOrders[];
    recentOrders: AdminOrder[];
    lowStock: LowStockProduct[];
};
