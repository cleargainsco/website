<?php

declare(strict_types=1);

namespace Shop\Enums;

enum ProductRemoval: string
{
    case Deleted = 'deleted';
    case Archived = 'archived';
}
