<?php

declare(strict_types=1);

namespace Shop\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class CheckoutData extends Data
{
    /**
     * @param  array<string, mixed>  $details  anything the site records with the order
     */
    public function __construct(
        public string $shippingName,
        public string $shippingLine1,
        public string $shippingCity,
        public string $shippingRegion,
        public string $shippingPostalCode,
        public string $shippingCountry,
        public ?string $shippingPhone = null,
        public ?string $shippingLine2 = null,
        public ?string $customerNote = null,
        public array $details = [],
    ) {}
}
