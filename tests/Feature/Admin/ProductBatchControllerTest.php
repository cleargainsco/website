<?php

declare(strict_types=1);

use App\Models\ProductBatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => Storage::fake('public'));

function batchPayload(array $overrides = []): array
{
    return array_replace([
        'number' => 'CG-2026-01',
        'tested_at' => '2026-10-01',
        'purity' => '99.2%',
        'lab' => 'Lab One',
        'certificate' => fakePdf('coa.pdf'),
    ], $overrides);
}

test('an admin adds a batch with its COA, and the edit sheet lists it', function () {
    $product = researchPeptide()->priced()->create(['slug' => 'alpha-10mg']);
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.products.batches.store', $product), batchPayload())
        ->assertSessionHasNoErrors();

    $batch = ProductBatch::query()->with('media')->sole();

    expect($batch->product_id)->toBe($product->id)
        ->and($batch->tested_at->toDateString())->toBe('2026-10-01')
        ->and($batch->getFirstMedia(ProductBatch::CERTIFICATE_COLLECTION)->file_name)->toBe('alpha-10mg-CG-2026-01-coa.pdf');

    $this->get(route('admin.products.edit', $product))
        ->assertInertia(fn (Assert $page) => $page
            ->has('batches', 1)
            ->where('batches.0.number', 'CG-2026-01')
            ->where('batches.0.certificate_url', fn (string $url) => str_ends_with($url, '-coa.pdf')));
});

test('a batch needs a PDF COA, a past test date and a number new to the product', function () {
    $product = researchPeptide()->priced()->create();
    ProductBatch::factory()->for($product)->create(['number' => 'TAKEN']);
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.products.batches.store', $product), batchPayload([
        'number' => 'TAKEN',
        'tested_at' => now()->addDay()->toDateString(),
        'certificate' => UploadedFile::fake()->image('coa.jpg'),
    ]))->assertSessionHasErrors(['number', 'tested_at', 'certificate']);
});

test('removing a batch deletes its COA', function () {
    $product = researchPeptide()->priced()->create();
    $this->actingAs(User::factory()->admin()->create());
    $this->post(route('admin.products.batches.store', $product), batchPayload());
    $batch = ProductBatch::query()->sole();

    $this->delete(route('admin.products.batches.destroy', [$product, $batch]))->assertSessionHasNoErrors();

    expect(ProductBatch::query()->count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('a batch is only reachable through its own product', function () {
    $product = researchPeptide()->priced()->create();
    $batch = ProductBatch::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->delete(route('admin.products.batches.destroy', [$product, $batch]))
        ->assertNotFound();

    expect($batch->fresh())->not->toBeNull();
});

test('customers cannot add or remove batches', function () {
    $product = researchPeptide()->priced()->create();
    $batch = ProductBatch::factory()->for($product)->create();
    $this->actingAs(User::factory()->customer()->create());

    $this->post(route('admin.products.batches.store', $product), batchPayload())->assertForbidden();
    $this->delete(route('admin.products.batches.destroy', [$product, $batch]))->assertForbidden();

    expect(ProductBatch::query()->count())->toBe(1);
});
