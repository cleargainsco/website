<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class ProductBatchData extends Data
{
    public function __construct(
        public string $number,
        public string $testedAt,
        public UploadedFile $certificate,
        public ?string $purity = null,
        public ?string $lab = null,
    ) {}
}
