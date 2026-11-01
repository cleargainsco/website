<?php

use App\Handlers\ProductTypes\ResearchPeptideProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Shop\Database\Factories\ProductFactory;
use Shop\Models\Product;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function researchPeptide(string $amount = '10 mg'): ProductFactory
{
    return Product::factory()->state([
        'type' => ResearchPeptideProductType::KEY,
        'details' => ['amount' => $amount],
    ]);
}

function fakePdf(string $name = 'coa.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n");
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function shippingAddress(array $overrides = []): array
{
    return array_replace([
        'shipping_name' => 'Ada Lab',
        'shipping_line1' => '1 Bench St',
        'shipping_city' => 'Austin',
        'shipping_region' => 'TX',
        'shipping_postal_code' => '78701',
        'shipping_country' => 'United States',
        'research_use' => '1',
    ], $overrides);
}
