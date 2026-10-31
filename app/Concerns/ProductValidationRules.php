<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Rules\ValidMoney;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Shop\Contracts\ProductType;
use Shop\Data\ProductData;
use Shop\Models\Product;
use Shop\ProductTypeRegistry;
use Shop\ProductTypes\ProductField;

trait ProductValidationRules
{
    /**
     * @return array<string, mixed>
     */
    protected function productRules(?Product $product = null): array
    {
        $types = app(ProductTypeRegistry::class);
        $type = $this->input('type');

        return [
            'type' => ['required', 'string', Rule::in($types->keys())],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique(Product::class)->ignore($product)],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'prices' => ['required', 'array', 'min:1', 'max:20'],
            'prices.*.min_quantity' => ['required', 'integer', 'min:1', 'max:100000', 'distinct'],
            'prices.*.unit_price' => ['required', new ValidMoney(config()->string('shop.currency'), min: '0.01')],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_image' => ['boolean'],
            'details' => ['array'],
            ...(is_string($type) && in_array($type, $types->keys(), true) ? $types->detailRules($type) : []),
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    protected function productAfterRules(): array
    {
        return [
            function (Validator $validator): void {
                $quantities = $this->collect('prices')->pluck('min_quantity')->map(fn (mixed $quantity): int => (int) $quantity);

                if (! $quantities->contains(1)) {
                    $validator->errors()->add('prices', 'Add a price for a single unit (quantity 1).');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function productAttributes(): array
    {
        return [
            'prices.*.min_quantity' => 'quantity',
            'prices.*.unit_price' => 'price',
            ...collect(app(ProductTypeRegistry::class)->all())
                ->flatMap(fn (ProductType $type): array => collect($type->fields())->mapWithKeys(fn (ProductField $field): array => ["details.{$field->name}" => mb_strtolower($field->label)])->all())
                ->all(),
        ];
    }

    public function toProductData(): ProductData
    {
        return ProductData::from($this->validated());
    }
}
