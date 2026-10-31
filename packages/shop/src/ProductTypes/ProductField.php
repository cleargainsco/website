<?php

declare(strict_types=1);

namespace Shop\ProductTypes;

use Shop\Enums\FieldKind;

/**
 * One extra field a product type carries. Its public properties are the shape the
 * admin form receives.
 */
final readonly class ProductField
{
    public function __construct(
        public string $name,
        public string $label,
        public FieldKind $kind = FieldKind::Text,
        public bool $required = false,
        public ?string $help = null,
    ) {}

    /**
     * @return list<string>
     */
    public function rules(): array
    {
        return [
            $this->required ? 'required' : 'nullable',
            'string',
            $this->kind === FieldKind::Text ? 'max:255' : 'max:5000',
        ];
    }
}
