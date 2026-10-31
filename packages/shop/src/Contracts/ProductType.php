<?php

declare(strict_types=1);

namespace Shop\Contracts;

use Shop\ProductTypes\ProductField;

interface ProductType
{
    public function label(): string;

    /**
     * @return list<ProductField>
     */
    public function fields(): array;
}
