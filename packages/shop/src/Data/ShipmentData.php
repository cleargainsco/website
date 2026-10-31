<?php

declare(strict_types=1);

namespace Shop\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class ShipmentData extends Data
{
    public function __construct(
        public ?string $carrier = null,
        public ?string $trackingNumber = null,
    ) {}
}
