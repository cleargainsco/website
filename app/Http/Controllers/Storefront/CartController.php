<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Resources\MoneyResource;
use App\Http\Resources\Shop\CartLineResource;
use Inertia\Inertia;
use Inertia\Response;
use Shop\Cart\Cart;
use Shop\Contracts\ShippingCalculator;

final class CartController extends Controller
{
    public function show(Cart $cart, ShippingCalculator $shipping): Response
    {
        $lines = $cart->lines();
        $subtotal = $cart->subtotal($lines);
        $shippingQuote = $lines->isEmpty() ? null : $shipping->quote($lines);

        return Inertia::render('marketing/cart', [
            'lines' => CartLineResource::collection($lines),
            'subtotal' => $subtotal === null ? null : new MoneyResource($subtotal),
            'shipping' => $shippingQuote === null ? null : new MoneyResource($shippingQuote),
        ]);
    }
}
