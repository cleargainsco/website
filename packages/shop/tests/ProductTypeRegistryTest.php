<?php

declare(strict_types=1);

use Shop\Contracts\ProductType;
use Shop\Enums\FieldKind;
use Shop\ProductTypeRegistry;
use Shop\ProductTypes\ProductField;
use Shop\ProductTypes\SimpleProduct;
use Tests\TestCase;

uses(TestCase::class);

final class TestBookProductType implements ProductType
{
    public function label(): string
    {
        return 'Book';
    }

    public function fields(): array
    {
        return [
            new ProductField('isbn', 'ISBN', required: true),
            new ProductField('blurb', 'Blurb', FieldKind::Textarea),
        ];
    }
}

it('builds validation rules for a type from the fields it declares', function () {
    $registry = new ProductTypeRegistry(['simple' => SimpleProduct::class, 'book' => TestBookProductType::class]);

    expect($registry->keys())->toBe(['simple', 'book'])
        ->and($registry->detailRules('simple'))->toBe([])
        ->and($registry->detailRules('book'))->toBe([
            'details.isbn' => ['required', 'string', 'max:255'],
            'details.blurb' => ['nullable', 'string', 'max:5000'],
        ]);
});

it('refuses a type that is not registered, or a class that is not a product type', function (string $key) {
    (new ProductTypeRegistry(['broken' => stdClass::class]))->get($key);
})->with(['unknown', 'broken'])->throws(InvalidArgumentException::class);
