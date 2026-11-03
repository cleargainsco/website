<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class HomePageData extends Data
{
    public function __construct(
        public string $eyebrow,
        public string $title,
        public ?string $titleHighlight,
        public string $description,
        public string $imageAlt,
        public ?UploadedFile $heroImage = null,
        public bool $removeHeroImage = false,
    ) {}
}
