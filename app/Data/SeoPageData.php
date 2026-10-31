<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class SeoPageData extends Data
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?UploadedFile $shareImage = null,
        public bool $removeShareImage = false,
    ) {}
}
