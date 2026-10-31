<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminOrderResource;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Head\Facades\Head;
use Shop\Models\Order;

final class OrderController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Order::class);

        return Inertia::render('admin/orders/index', [
            'orders' => AdminOrderResource::collection(
                Order::query()->with(['lines', 'customer'])->latest('placed_at')->latest('id')->get(),
            ),
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        Head::title("Order {$order->number}");

        return Inertia::render('admin/orders/show', [
            'order' => $order->load(['lines', 'customer'])->toResource(AdminOrderResource::class),
        ]);
    }
}
