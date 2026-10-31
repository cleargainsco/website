<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Brick\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Money
 */
final class MoneyResource extends JsonResource
{
    /**
     * @return array{minor: int, currency: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'minor' => $this->resource->getMinorAmount()->toInt(),
            'currency' => $this->resource->getCurrency()->getCurrencyCode(),
        ];
    }
}
