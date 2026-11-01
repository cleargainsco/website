<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class WelcomeOfferData extends Data
{
    public function __construct(
        public int $percentOff,
        public ?int $validDays,
        public string $eyebrow,
        public string $headline,
        public string $body,
        public string $buttonLabel,
        public string $successHeadline,
        public string $successBody,
        public string $finePrint,
    ) {}
}
