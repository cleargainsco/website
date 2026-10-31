<?php

declare(strict_types=1);

namespace Shop\Enums;

enum StockStatus: string
{
    case InStock = 'in_stock';
    case LowStock = 'low_stock';
    case OutOfStock = 'out_of_stock';

    public static function forCount(int $stock, int $lowStockThreshold): self
    {
        return match (true) {
            $stock <= 0 => self::OutOfStock,
            $stock <= $lowStockThreshold => self::LowStock,
            default => self::InStock,
        };
    }
}
