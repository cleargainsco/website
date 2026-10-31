<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\RoleName;
use App\Models\User;
use Brick\Money\Money;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Shop\Enums\OrderStatus;
use Shop\Models\Order;
use Shop\Models\Product;

/**
 * The figures on the admin dashboard. Revenue counts orders by the day they were
 * paid, so an order placed last month and paid this month is this month's revenue.
 *
 * @phpstan-type DailyOrders array{date: string, orders: int, value: Money}
 */
final readonly class DashboardMetrics
{
    private string $currency;

    public function __construct()
    {
        $this->currency = config()->string('shop.currency');
    }

    public function revenueBetween(CarbonInterface $from, CarbonInterface $until): Money
    {
        return Money::ofMinor(
            (int) Order::query()
                ->whereIn('status', [OrderStatus::Paid, OrderStatus::Shipped])
                ->whereBetween('paid_at', [$from, $until])
                ->sum('total_minor'),
            $this->currency,
        );
    }

    /**
     * @return Collection<int, Order>
     */
    public function ordersWithStatus(OrderStatus $status): Collection
    {
        return Order::query()->where('status', $status)->get(['id', 'currency', 'total_minor']);
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    public function totalOf(Collection $orders): Money
    {
        return Money::ofMinor($orders->sum(fn (Order $order): int => $order->total_minor->getMinorAmount()->toInt()), $this->currency);
    }

    public function customerCount(?CarbonInterface $since = null): int
    {
        return User::query()
            ->role(RoleName::Customer->value)
            ->when($since !== null, fn ($query) => $query->where('created_at', '>=', $since))
            ->count();
    }

    /**
     * Every day in the window, including the ones with no orders, so the chart has no gaps.
     *
     * @return list<DailyOrders>
     */
    public function dailyOrders(int $days): array
    {
        $start = Date::today()->subDays($days - 1);

        $byDay = Order::query()
            ->where('placed_at', '>=', $start)
            ->get(['id', 'currency', 'total_minor', 'placed_at'])
            ->groupBy(fn (Order $order): string => $order->placed_at->toDateString());

        return array_values(collect(range(0, $days - 1))
            ->map(function (int $offset) use ($start, $byDay): array {
                $date = $start->addDays($offset)->toDateString();
                $orders = $byDay->get($date, new Collection);

                return ['date' => $date, 'orders' => $orders->count(), 'value' => $this->totalOf($orders)];
            })
            ->all());
    }

    /**
     * @return Collection<int, Product>
     */
    public function lowStockProducts(int $limit): Collection
    {
        return Product::query()
            ->active()
            ->where('stock', '<=', config()->integer('shop.low_stock_threshold'))
            ->with('media')
            ->orderBy('stock')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
