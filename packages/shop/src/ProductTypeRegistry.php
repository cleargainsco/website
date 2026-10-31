<?php

declare(strict_types=1);

namespace Shop;

use InvalidArgumentException;
use Shop\Contracts\ProductType;
use Shop\ProductTypes\ProductField;

final readonly class ProductTypeRegistry
{
    /**
     * @param  array<array-key, mixed>  $types  type key => class implementing ProductType
     */
    public function __construct(private array $types) {}

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_map(strval(...), array_keys($this->types));
    }

    public function get(string $key): ProductType
    {
        $class = $this->types[$key] ?? null;

        if (! is_string($class) || ! is_a($class, ProductType::class, true)) {
            throw new InvalidArgumentException("No product type is registered as [{$key}] in config/shop.php.");
        }

        return app($class);
    }

    /**
     * @return array<string, ProductType>
     */
    public function all(): array
    {
        return collect($this->keys())
            ->mapWithKeys(fn (string $key): array => [$key => $this->get($key)])
            ->all();
    }

    /**
     * Validation rules for a product's details, keyed for a request's `details` array.
     *
     * @return array<string, list<string>>
     */
    public function detailRules(string $key): array
    {
        return collect($this->get($key)->fields())
            ->mapWithKeys(fn (ProductField $field): array => ["details.{$field->name}" => $field->rules()])
            ->all();
    }
}
