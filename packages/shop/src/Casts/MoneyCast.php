<?php

declare(strict_types=1);

namespace Shop\Casts;

use Brick\Money\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Reads a minor-units column as a Money in the row's own currency. Writes accept a
 * Money in that currency, an int of minor units, or null for a nullable column. It never writes the currency.
 *
 * @implements CastsAttributes<Money, mixed>
 */
final readonly class MoneyCast implements CastsAttributes
{
    public function __construct(private string $currencyAttribute = 'currency') {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return Money::ofMinor((int) $value, $this->currency($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null || is_int($value)) {
            return $value;
        }

        if (! $value instanceof Money) {
            throw new InvalidArgumentException("[{$key}] takes a Money or an int of minor units.");
        }

        $currency = $this->currency($attributes);

        if ($value->getCurrency()->getCurrencyCode() !== $currency) {
            throw new InvalidArgumentException("[{$key}] is in {$currency}; refusing {$value->getCurrency()->getCurrencyCode()} rather than storing it unconverted.");
        }

        return $value->getMinorAmount()->toInt();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function currency(array $attributes): string
    {
        $currency = $attributes[$this->currencyAttribute] ?? null;

        if (! is_string($currency)) {
            throw new InvalidArgumentException("Set [{$this->currencyAttribute}] before the amount; the amount is stored in its minor units.");
        }

        return $currency;
    }
}
