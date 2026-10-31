<?php

declare(strict_types=1);

namespace Shop\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Shop\Exceptions\InsufficientStockException;
use Shop\Models\Product;

final readonly class DecrementStockAction
{
    /**
     * Takes stock under a row lock, so two orders for the last vial cannot both succeed.
     *
     * @throws InsufficientStockException
     */
    public function handle(Product $product, int $quantity): Product
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Stock is taken one unit or more at a time.');
        }

        return DB::transaction(function () use ($product, $quantity): Product {
            $locked = Product::query()->lockForUpdate()->whereKey($product->id)->firstOrFail();

            if ($locked->stock < $quantity) {
                throw new InsufficientStockException($locked, $quantity);
            }

            $locked->decrement('stock', $quantity);

            return $locked;
        });
    }
}
