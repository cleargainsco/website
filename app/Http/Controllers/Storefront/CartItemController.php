<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreCartItemRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Http\Resources\MoneyResource;
use App\Http\Resources\Shop\CartLineResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Shop\Cart\Cart;
use Shop\Exceptions\InsufficientStockException;
use Shop\Exceptions\ProductUnavailableException;
use Shop\Models\Product;

final class CartItemController extends Controller
{
    /**
     * The flash opens the cart drawer on whatever page the customer added from.
     */
    public function store(StoreCartItemRequest $request, Cart $cart): RedirectResponse
    {
        $product = Product::query()->where('public_id', $request->safe()->string('product')->toString())->firstOrFail();

        $this->attempt(fn () => $cart->add($product, $request->integer('quantity')));

        $lines = $cart->lines();
        $subtotal = $cart->subtotal($lines);

        Inertia::flash('cartAdded', [
            'product_id' => $product->public_id,
            'lines' => CartLineResource::collection($lines)->resolve($request),
            'subtotal' => $subtotal === null ? null : (new MoneyResource($subtotal))->resolve($request),
        ]);

        return back();
    }

    public function update(UpdateCartItemRequest $request, Product $product, Cart $cart): RedirectResponse
    {
        $this->attempt(fn () => $cart->update($product, $request->integer('quantity')));

        return back();
    }

    public function destroy(Product $product, Cart $cart): RedirectResponse
    {
        $cart->remove($product);

        return back();
    }

    /**
     * The cart refuses on stock and availability, which only it can check; the refusal
     * reaches the page as a quantity error.
     *
     * @param  callable(): void  $change
     */
    private function attempt(callable $change): void
    {
        try {
            $change();
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'quantity' => $exception->product->stock === 0
                    ? __(':name is out of stock.', ['name' => $exception->product->name])
                    : __('Only :count of :name left.', ['count' => $exception->product->stock, 'name' => $exception->product->name]),
            ]);
        } catch (ProductUnavailableException $exception) {
            throw ValidationException::withMessages([
                'quantity' => __(':name is not available right now.', ['name' => $exception->product->name]),
            ]);
        }
    }
}
