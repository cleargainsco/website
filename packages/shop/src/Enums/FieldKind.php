<?php

declare(strict_types=1);

namespace Shop\Enums;

enum FieldKind: string
{
    case Text = 'text';
    case Textarea = 'textarea';
}
