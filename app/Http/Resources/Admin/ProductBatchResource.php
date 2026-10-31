<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductBatch
 */
final class ProductBatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'number' => $this->number,
            'tested_at' => $this->tested_at->toDateString(),
            'purity' => $this->purity,
            'lab' => $this->lab,
            'certificate_url' => $this->certificateUrl(),
        ];
    }
}
