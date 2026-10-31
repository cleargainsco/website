<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\PlaceOrderRequest;
use App\Http\Resources\MoneyResource;
use App\Http\Resources\Shop\CartLineResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Shop\Actions\PlaceOrderAction;
use Shop\Cart\Cart;
use Shop\Contracts\ShippingCalculator;
use Shop\Data\CheckoutData;
use Shop\Exceptions\EmptyCartException;
use Shop\Exceptions\InsufficientStockException;

final class CheckoutController extends Controller
{
    public function create(#[CurrentUser] User $user, Cart $cart, ShippingCalculator $shipping): Response|RedirectResponse
    {
        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            return to_route('cart.show');
        }

        $subtotal = $cart->subtotal($lines);
        $shippingQuote = $shipping->quote($lines);
        $lastOrder = $user->orders()->latest('placed_at')->first();

        return Inertia::render('marketing/checkout', [
            'lines' => CartLineResource::collection($lines),
            'subtotal' => $subtotal === null ? null : new MoneyResource($subtotal),
            'shipping' => $shippingQuote === null ? null : new MoneyResource($shippingQuote),
            'address' => [
                'shipping_name' => $lastOrder->shipping_name ?? $user->name,
                'shipping_phone' => $lastOrder?->shipping_phone,
                'shipping_line1' => $lastOrder?->shipping_line1,
                'shipping_line2' => $lastOrder?->shipping_line2,
                'shipping_city' => $lastOrder?->shipping_city,
                'shipping_region' => $lastOrder?->shipping_region,
                'shipping_postal_code' => $lastOrder?->shipping_postal_code,
                'shipping_country' => $lastOrder->shipping_country ?? 'United States',
            ],
        ]);
    }

    public function store(PlaceOrderRequest $request, #[CurrentUser] User $user, Cart $cart, PlaceOrderAction $placeOrder): RedirectResponse
    {
        $data = CheckoutData::from([
            ...$request->safe()->except('research_use'),
            'details' => ['research_use_confirmed_at' => Date::now()->toIso8601String()],
        ]);

        try {
            $order = $placeOrder->handle($user, $user->email, $data, $cart);
        } catch (EmptyCartException) {
            return to_route('cart.show');
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'cart' => $exception->product->stock === 0
                    ? __(':name sold out while you were checking out. Remove it from your cart to continue.', ['name' => $exception->product->name])
                    : __('Only :count of :name left. Lower the quantity in your cart to continue.', ['count' => $exception->product->stock, 'name' => $exception->product->name]),
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Order :number placed.', ['number' => $order->number])]);

        return to_route('account.orders.show', ['order' => $order, 'placed' => 1]);
    }
}
