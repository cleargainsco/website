<?php

declare(strict_types=1);

namespace Shop\Data;

use Brick\Money\Money;
use Shop\Data\Casts\MajorUnitsMoneyCast;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class PriceTierData extends Data
{
    public function __construct(
        public int $minQuantity,
        #[WithCast(MajorUnitsMoneyCast::class)]
        public Money $unitPrice,
    ) {}
}
