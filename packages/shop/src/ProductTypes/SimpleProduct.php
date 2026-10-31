<?php

declare(strict_types=1);

namespace Shop\ProductTypes;

use Shop\Contracts\ProductType;

final class SimpleProduct implements ProductType
{
    public function label(): string
    {
        return 'Simple product';
    }

    public function fields(): array
    {
        return [];
    }
}
