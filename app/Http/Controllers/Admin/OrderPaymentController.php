<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Shop\Actions\MarkOrderPaidAction;
use Shop\Exceptions\OrderStateException;
use Shop\Models\Order;

final class OrderPaymentController extends Controller
{
    public function store(Order $order, MarkOrderPaidAction $markPaid): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $markPaid->handle($order);
        } catch (OrderStateException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Order :number marked paid.', ['number' => $order->number])]);

        return back();
    }
}
