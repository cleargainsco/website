<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Models\Product;

function productPayload(array $overrides = []): array
{
    return array_replace([
        'type' => 'research-peptide',
        'name' => 'Alpha',
        'slug' => 'alpha-10mg',
        'summary' => 'A research peptide.',
        'description' => null,
        'details' => ['amount' => '10 mg', 'storage' => null],
        'stock' => '25',
        'is_active' => '1',
        'prices' => [
            ['min_quantity' => '3', 'unit_price' => '45.00'],
            ['min_quantity' => '1', 'unit_price' => '50'],
        ],
    ], $overrides);
}

describe('access', function () {
    test('guests are sent to log in', function () {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
    });

    test('customers are refused', function () {
        $product = researchPeptide()->priced()->create();

        $this->actingAs(User::factory()->customer()->create());

        $this->get(route('admin.products.index'))->assertForbidden();
        $this->post(route('admin.products.store'), productPayload())->assertForbidden();
        $this->put(route('admin.products.update', $product), productPayload())->assertForbidden();

        expect(Product::query()->count())->toBe(1);
    });
});

describe('index', function () {
    test('admins see every product, hidden ones included, with stock and starting price', function () {
        researchPeptide('10 mg')->priced([1 => 5000, 3 => 4500])->create(['name' => 'Alpha', 'stock' => 4]);
        researchPeptide()->inactive()->priced()->create(['name' => 'Beta']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.products.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/products/index')
                ->has('products', 2)
                ->where('products.0.name', 'Alpha')
                ->where('products.0.type_label', 'Research peptide')
                ->where('products.0.stock', 4)
                ->where('products.0.stock_status', 'low_stock')
                ->where('products.0.base_price', ['minor' => 5000, 'currency' => 'USD'])
                ->where('products.0.tier_count', 2)
                ->where('products.1.is_active', false));
    });
});

describe('store', function () {
    test('an admin creates a product with its details and price tiers', function () {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), productPayload())
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->with('prices')->sole();

        expect($product->slug)->toBe('alpha-10mg')
            ->and($product->details)->toBe(['amount' => '10 mg', 'storage' => null])
            ->and($product->stock)->toBe(25)
            ->and($product->is_active)->toBeTrue()
            ->and($product->is_featured)->toBeFalse()
            ->and($product->prices->pluck('min_quantity')->all())->toBe([1, 3])
            ->and($product->prices->map(fn ($price) => $price->unit_price_minor->getMinorAmount()->toInt())->all())->toBe([5000, 4500]);
    });

    test('a product needs a single-unit price', function () {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), productPayload(['prices' => [['min_quantity' => '3', 'unit_price' => '45']]]))
            ->assertSessionHasErrors('prices');
    });

    test('a price the currency cannot hold exactly is refused rather than rounded', function () {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), productPayload(['prices' => [['min_quantity' => '1', 'unit_price' => '50.005']]]))
            ->assertSessionHasErrors('prices.0.unit_price');
    });

    test('the product type decides which details are required', function () {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), productPayload(['details' => ['amount' => '']]))
            ->assertSessionHasErrors('details.amount');
    });

    test('an unregistered product type is refused', function () {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), productPayload(['type' => 'gift-card']))
            ->assertSessionHasErrors('type');
    });

    test('slugs are unique', function () {
        researchPeptide()->create(['slug' => 'alpha-10mg']);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), productPayload())
            ->assertSessionHasErrors('slug');
    });
});

describe('update', function () {
    test('an admin edits a product and its price tiers are replaced', function () {
        $product = researchPeptide()->priced([1 => 9900, 6 => 8000])->create(['slug' => 'alpha-10mg', 'is_featured' => true]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.products.edit', $product))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/products/edit')
                ->where('product.id', $product->public_id)
                ->where('product.prices.1', ['min_quantity' => 6, 'unit_price' => '80.00']));

        $this->put(route('admin.products.update', $product), productPayload(['name' => 'Alpha Prime', 'stock' => '7']))
            ->assertRedirect(route('admin.products.index'));

        $product->refresh()->load('prices');

        expect($product->name)->toBe('Alpha Prime')
            ->and($product->stock)->toBe(7)
            ->and($product->is_featured)->toBeFalse()
            ->and($product->prices->pluck('min_quantity')->all())->toBe([1, 3]);
    });

    test('products are addressed by their public id, not the database id', function () {
        $product = researchPeptide()->priced()->create();

        $this->actingAs(User::factory()->admin()->create());

        $this->get("/admin/products/{$product->id}/edit")->assertNotFound();
        $this->get("/admin/products/{$product->public_id}/edit")->assertOk();
    });
});
