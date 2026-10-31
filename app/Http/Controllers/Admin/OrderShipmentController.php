<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShipmentRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Shop\Actions\ShipOrderAction;
use Shop\Data\ShipmentData;
use Shop\Exceptions\OrderStateException;
use Shop\Models\Order;

final class OrderShipmentController extends Controller
{
    public function store(StoreShipmentRequest $request, Order $order, ShipOrderAction $shipOrder): RedirectResponse
    {
        try {
            $shipOrder->handle($order, ShipmentData::from($request->validated()));
        } catch (OrderStateException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Order :number marked shipped. The customer has been emailed.', ['number' => $order->number])]);

        return back();
    }
}
