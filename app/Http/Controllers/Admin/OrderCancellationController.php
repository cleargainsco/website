<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Shop\Actions\CancelOrderAction;
use Shop\Exceptions\OrderStateException;
use Shop\Models\Order;

final class OrderCancellationController extends Controller
{
    public function store(Order $order, CancelOrderAction $cancelOrder): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $cancelOrder->handle($order);
        } catch (OrderStateException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Order :number cancelled and its stock put back.', ['number' => $order->number])]);

        return back();
    }
}
