<?php

declare(strict_types=1);

namespace Shop\Data\Casts;

use Brick\Money\Money;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * Reads an amount in major units ("112.50") as Money in the shop currency. The input
 * has already been validated as exact for that currency, so nothing is rounded here.
 */
final class MajorUnitsMoneyCast implements Cast
{
    /**
     * @param  array<string, mixed>  $properties
     * @param  CreationContext<Data>  $context
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): Money
    {
        if ($value instanceof Money) {
            return $value;
        }

        return Money::of((string) $value, config()->string('shop.currency'));
    }
}
