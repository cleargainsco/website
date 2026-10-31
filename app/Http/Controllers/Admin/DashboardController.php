<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminOrderResource;
use App\Http\Resources\MoneyResource;
use App\Support\DashboardMetrics;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use Inertia\Response;
use Shop\Enums\OrderStatus;
use Shop\Models\Order;
use Shop\Models\Product;

final class DashboardController extends Controller
{
    private const int CHART_DAYS = 30;

    public function index(DashboardMetrics $metrics): Response
    {
        $this->authorize('viewAny', Order::class);

        $monthStart = Date::now()->startOfMonth();
        $awaitingPayment = $metrics->ordersWithStatus(OrderStatus::AwaitingPayment);

        return Inertia::render('dashboard', [
            'revenue' => [
                'this_month' => new MoneyResource($metrics->revenueBetween($monthStart, Date::now())),
                'last_month_to_date' => new MoneyResource($metrics->revenueBetween(
                    $monthStart->subMonthNoOverflow(),
                    Date::now()->subMonthNoOverflow(),
                )),
            ],
            'awaitingPayment' => [
                'count' => $awaitingPayment->count(),
                'value' => new MoneyResource($metrics->totalOf($awaitingPayment)),
            ],
            'readyToShip' => $metrics->ordersWithStatus(OrderStatus::Paid)->count(),
            'customers' => [
                'total' => $metrics->customerCount(),
                'new_this_month' => $metrics->customerCount(since: $monthStart),
            ],
            'dailyOrders' => array_map(fn (array $day): array => [
                ...$day,
                'value' => new MoneyResource($day['value']),
            ], $metrics->dailyOrders(self::CHART_DAYS)),
            'recentOrders' => AdminOrderResource::collection(
                Order::query()->with(['lines', 'customer'])->latest('placed_at')->latest('id')->limit(5)->get(),
            ),
            'lowStock' => $metrics->lowStockProducts(6)->map(fn (Product $product): array => [
                'id' => $product->public_id,
                'name' => trim($product->name.' '.($product->details['amount'] ?? '')),
                'stock' => $product->stock,
                'stock_status' => $product->stockStatus(),
                'thumbnail_url' => $product->thumbnailUrl(),
            ]),
        ]);
    }
}
