<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Concerns\ProductValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Shop\Models\Product;

final class StoreProductRequest extends FormRequest
{
    use ProductValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Product::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->productRules();
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return $this->productAfterRules();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->productAttributes();
    }
}
