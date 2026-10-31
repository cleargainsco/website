<?php

declare(strict_types=1);

namespace App\Handlers\ProductTypes;

use Shop\Contracts\ProductType;
use Shop\Enums\FieldKind;
use Shop\ProductTypes\ProductField;

final class ResearchPeptideProductType implements ProductType
{
    public const string KEY = 'research-peptide';

    public function label(): string
    {
        return 'Research peptide';
    }

    public function fields(): array
    {
        return [
            new ProductField(
                name: 'amount',
                label: 'Amount per vial',
                required: true,
                help: 'As printed on the label, e.g. "10 mg".',
            ),
            new ProductField(
                name: 'storage',
                label: 'Storage',
                kind: FieldKind::Textarea,
                help: 'Leave blank to use the standard storage guidance.',
            ),
        ];
    }
}
