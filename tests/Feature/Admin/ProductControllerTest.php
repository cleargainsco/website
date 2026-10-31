<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Models\Order;
use Shop\Models\Product;
use Shop\Models\ProductPrice;

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
        $this->get(route('admin.products.create'))->assertForbidden();
        $this->get(route('admin.products.edit', $product))->assertForbidden();
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
    test('the add form opens in a sheet over the product list', function () {
        researchPeptide()->priced()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.products.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/products/index')
                ->where('sheet', 'create')
                ->has('products', 1)
                ->where('productTypes.0.key', 'research-peptide')
                ->where('productTypes.0.fields.0.name', 'amount')
                ->missing('product'));
    });

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
                ->component('admin/products/index')
                ->where('sheet', 'edit')
                ->has('products', 1)
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

    test('an admin sets the search title and description', function () {
        $product = researchPeptide()->priced()->create(['slug' => 'alpha-10mg']);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.products.update', $product), productPayload(['meta_title' => 'Buy Alpha 10 mg', 'meta_description' => 'Alpha, tested.']));

        expect($product->refresh())
            ->meta_title->toBe('Buy Alpha 10 mg')
            ->meta_description->toBe('Alpha, tested.');

        $this->put(route('admin.products.update', $product), productPayload(['meta_title' => str_repeat('a', 71)]))
            ->assertInvalid('meta_title');
    });

    test('products are addressed by their public id, not the database id', function () {
        $product = researchPeptide()->priced()->create();

        $this->actingAs(User::factory()->admin()->create());

        $this->get("/admin/products/{$product->id}/edit")->assertNotFound();
        $this->get("/admin/products/{$product->public_id}/edit")->assertOk();
    });
});

describe('image', function () {
    beforeEach(fn () => Storage::fake('public'));

    test('an uploaded photo is turned into a scaled-down WebP with a thumbnail', function () {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), productPayload([
                'image' => UploadedFile::fake()->image('vial.jpg', 3000, 1500),
            ]))
            ->assertRedirect(route('admin.products.index'));

        $media = Product::query()->sole()->getFirstMedia(Product::IMAGE_COLLECTION);

        expect($media->file_name)->toBe('alpha-10mg.webp')
            ->and($media->mime_type)->toBe('image/webp')
            ->and(getimagesizefromstring(Storage::disk('public')->get($media->getPathRelativeToRoot()))[0])->toBe(2000)
            ->and(Storage::disk('public')->exists($media->getPathRelativeToRoot(Product::THUMBNAIL_CONVERSION)))->toBeTrue();
    });

    test('a new upload replaces the old image, and an edit without one keeps it', function () {
        $product = researchPeptide()->priced()->create(['slug' => 'alpha-10mg']);
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.products.update', $product), productPayload(['image' => UploadedFile::fake()->image('one.jpg')]));
        $this->put(route('admin.products.update', $product), productPayload(['image' => UploadedFile::fake()->image('two.png')]));
        $this->put(route('admin.products.update', $product), productPayload());

        expect($product->refresh()->getMedia(Product::IMAGE_COLLECTION))->toHaveCount(1)
            ->and(Storage::disk('public')->allFiles())->toHaveCount(2);
    });

    test('an admin can remove the image', function () {
        $product = researchPeptide()->priced()->create(['slug' => 'alpha-10mg']);
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.products.update', $product), productPayload(['image' => UploadedFile::fake()->image('one.jpg')]));
        $this->put(route('admin.products.update', $product), productPayload(['remove_image' => '1']));

        expect($product->refresh()->getMedia(Product::IMAGE_COLLECTION))->toBeEmpty()
            ->and(Storage::disk('public')->allFiles())->toBeEmpty();
    });

    test('only images are accepted', function () {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), productPayload([
                'image' => UploadedFile::fake()->create('coa.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('image');

        expect(Product::query()->count())->toBe(0);
    });

    test('the list shows a thumbnail and the edit form shows the image', function () {
        $product = researchPeptide()->priced()->create(['slug' => 'alpha-10mg']);
        $this->actingAs(User::factory()->admin()->create());
        $this->put(route('admin.products.update', $product), productPayload(['image' => UploadedFile::fake()->image('one.jpg')]));

        $this->get(route('admin.products.edit', $product))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.0.thumbnail_url', fn (string $url) => str_ends_with($url, 'alpha-10mg-thumbnail.webp'))
                ->where('product.image_url', fn (string $url) => str_ends_with($url, 'alpha-10mg.webp')));
    });
});

describe('remove', function () {
    test('a product nobody has ordered is deleted for good, with its prices and image', function () {
        Storage::fake('public');
        $product = researchPeptide()->priced([1 => 5000, 3 => 4500])->create(['slug' => 'alpha-10mg']);
        $this->actingAs(User::factory()->admin()->create());
        $this->put(route('admin.products.update', $product), productPayload(['image' => UploadedFile::fake()->image('one.jpg')]));

        $this->get(route('admin.products.index'))
            ->assertInertia(fn (Assert $page) => $page->where('products.0.has_been_ordered', false));

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        expect(Product::withTrashed()->count())->toBe(0)
            ->and(ProductPrice::query()->count())->toBe(0)
            ->and(Storage::disk('public')->allFiles())->toBeEmpty();
    });

    test('a product that has been ordered is archived instead, out of the list and the shop', function () {
        $product = researchPeptide()->priced()->create(['slug' => 'alpha-10mg']);
        Order::factory()->forProduct($product)->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        expect($product->refresh()->trashed())->toBeTrue();

        $this->get(route('admin.products.index'))
            ->assertInertia(fn (Assert $page) => $page->where('view', 'active')->has('products', 0));
        $this->get(route('admin.products.archived.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('view', 'archived')
                ->has('products', 1)
                ->where('products.0.id', $product->public_id));
        $this->get(route('shop.show', 'alpha-10mg'))->assertNotFound();
        $this->get(route('admin.products.edit', $product))->assertNotFound();
    });

    test('an archived product can be restored', function () {
        $product = researchPeptide()->priced()->create();
        $product->delete();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.restoration.store', $product))
            ->assertRedirect(route('admin.products.archived.index'));

        expect($product->refresh()->trashed())->toBeFalse();
    });

    test('customers cannot remove, restore or see archived products', function () {
        $product = researchPeptide()->priced()->create();
        $archived = researchPeptide()->priced()->create();
        $archived->delete();

        $this->actingAs(User::factory()->customer()->create());

        $this->delete(route('admin.products.destroy', $product))->assertForbidden();
        $this->post(route('admin.products.restoration.store', $archived))->assertForbidden();
        $this->get(route('admin.products.archived.index'))->assertForbidden();

        expect(Product::query()->whereKey($product->id)->exists())->toBeTrue()
            ->and($archived->refresh()->trashed())->toBeTrue();
    });
});
