<?php

declare(strict_types=1);

namespace Shop\Cart;

use Brick\Money\Money;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;
use Shop\Contracts\PriceResolver;
use Shop\Exceptions\DiscountCodeUnavailableException;
use Shop\Exceptions\InsufficientStockException;
use Shop\Exceptions\ProductUnavailableException;
use Shop\Models\DiscountCode;
use Shop\Models\Product;

/**
 * The visitor's cart, kept in the session so it works before they sign in and
 * survives signing in. Prices are always worked out fresh from the product.
 */
final readonly class Cart
{
    private const string SESSION_KEY = 'shop.cart';

    private const string DISCOUNT_SESSION_KEY = 'shop.discount_code';

    public function __construct(
        private Session $session,
        private PriceResolver $prices,
    ) {}

    /**
     * @throws ProductUnavailableException
     * @throws InsufficientStockException
     */
    public function add(Product $product, int $quantity): void
    {
        $this->put($product, ($this->quantities()[$product->id] ?? 0) + $quantity);
    }

    /**
     * @throws ProductUnavailableException
     * @throws InsufficientStockException
     */
    public function update(Product $product, int $quantity): void
    {
        $this->put($product, $quantity);
    }

    public function remove(Product $product): void
    {
        $quantities = $this->quantities();
        unset($quantities[$product->id]);

        $this->session->put(self::SESSION_KEY, $quantities);
    }

    public function clear(): void
    {
        $this->session->forget([self::SESSION_KEY, self::DISCOUNT_SESSION_KEY]);
    }

    /**
     * Checked again, under a lock, when the order is placed.
     *
     * @throws DiscountCodeUnavailableException
     */
    public function applyDiscount(string $code, string $email): DiscountCode
    {
        $normalized = DiscountCode::normalize($code);

        $discount = DiscountCode::query()->where('code', $normalized)->first()
            ?? throw DiscountCodeUnavailableException::notFound($normalized);

        $discount->ensureRedeemableBy($email);

        $this->session->put(self::DISCOUNT_SESSION_KEY, $discount->code);

        return $discount;
    }

    public function removeDiscount(): void
    {
        $this->session->forget(self::DISCOUNT_SESSION_KEY);
    }

    public function discountCode(): ?string
    {
        $code = $this->session->get(self::DISCOUNT_SESSION_KEY);

        return is_string($code) ? $code : null;
    }

    public function discount(): ?DiscountCode
    {
        $code = $this->discountCode();

        return $code === null ? null : DiscountCode::query()->where('code', $code)->first();
    }

    public function count(): int
    {
        return array_sum($this->quantities());
    }

    public function isEmpty(): bool
    {
        return $this->quantities() === [];
    }

    /**
     * Lines for products that can still be sold. A product that has since been hidden,
     * archived or unpriced drops out of the cart.
     *
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        $quantities = $this->quantities();

        $products = Product::query()
            ->whereKey(array_keys($quantities))
            ->active()
            ->whereHas('prices')
            ->with(['prices', 'media'])
            ->get()
            ->keyBy('id');

        if ($products->count() !== count($quantities)) {
            $this->session->put(self::SESSION_KEY, array_intersect_key($quantities, $products->all()));
        }

        return collect($quantities)
            ->filter(fn (int $quantity, int $productId): bool => $products->has($productId))
            ->map(fn (int $quantity, int $productId): CartLine => new CartLine(
                $products->get($productId),
                $quantity,
                $this->prices->unitPrice($products->get($productId), $quantity),
            ))
            ->values();
    }

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function subtotal(Collection $lines): ?Money
    {
        return $lines->reduce(
            fn (?Money $sum, CartLine $line): Money => $sum === null ? $line->total() : $sum->plus($line->total()),
        );
    }

    private function put(Product $product, int $quantity): void
    {
        if (! $product->isActive() || $product->trashed() || ! $product->prices()->exists()) {
            throw new ProductUnavailableException($product);
        }

        if ($quantity > $product->stock) {
            throw new InsufficientStockException($product, $quantity);
        }

        $quantities = $this->quantities();

        if ($quantity < 1) {
            unset($quantities[$product->id]);
        } else {
            $quantities[$product->id] = $quantity;
        }

        $this->session->put(self::SESSION_KEY, $quantities);
    }

    /**
     * @return array<int, int>
     */
    private function quantities(): array
    {
        $stored = $this->session->get(self::SESSION_KEY, []);

        return is_array($stored)
            ? array_filter(array_map(intval(...), $stored), fn (int $quantity): bool => $quantity > 0)
            : [];
    }
}
