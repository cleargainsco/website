<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\ProductBatch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shop\Models\Product;

final class StoreProductBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->product()) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:100', Rule::unique(ProductBatch::class)->where('product_id', $this->product()->id)],
            'tested_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'purity' => ['nullable', 'string', 'max:50'],
            'lab' => ['nullable', 'string', 'max:255'],
            'certificate' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'number' => 'batch number',
            'tested_at' => 'test date',
            'certificate' => 'COA',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'number.unique' => 'This product already has a batch with that number.',
            'certificate.mimes' => 'The COA must be a PDF.',
        ];
    }

    private function product(): Product
    {
        $product = $this->route('product');

        assert($product instanceof Product);

        return $product;
    }
}
