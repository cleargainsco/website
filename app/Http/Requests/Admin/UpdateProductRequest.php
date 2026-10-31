<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Concerns\ProductValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Shop\Models\Product;

final class UpdateProductRequest extends FormRequest
{
    use ProductValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->product()) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->productRules($this->product());
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

    private function product(): Product
    {
        $product = $this->route('product');

        assert($product instanceof Product);

        return $product;
    }
}
