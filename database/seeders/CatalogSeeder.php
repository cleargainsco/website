<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Handlers\ProductTypes\ResearchPeptideProductType;
use Brick\Money\Money;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Shop\Models\Product;

/**
 * The launch catalogue. Run once per environment:
 * `php artisan db:seed --class=CatalogSeeder --force`.
 *
 * Products are created only when their slug is missing, so a re-run never overwrites
 * what an admin has since changed. Stock starts at zero: the real count is entered in
 * the admin.
 */
final class CatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Prices are per vial in minor units, keyed by the quantity each tier starts at.
     *
     * @var list<array{slug: string, name: string, amount: string, summary: string, featured: bool, prices: array<int, int>}>
     */
    private const array PRODUCTS = [
        [
            'slug' => 'retatrutide-14mg',
            'name' => 'Retatrutide',
            'amount' => '14 mg',
            'summary' => 'Synthetic triple-agonist peptide, supplied as a lyophilized powder for laboratory research.',
            'featured' => true,
            'prices' => [1 => 11200, 3 => 10700, 6 => 9900, 10 => 9200],
        ],
        [
            'slug' => 'retatrutide-20mg',
            'name' => 'Retatrutide',
            'amount' => '20 mg',
            'summary' => 'Synthetic triple-agonist peptide in a larger vial, supplied as a lyophilized powder for laboratory research.',
            'featured' => false,
            'prices' => [1 => 14900, 3 => 14300, 6 => 13500, 10 => 12800],
        ],
        [
            'slug' => 'klow-80mg',
            'name' => 'KLOW',
            'amount' => '80 mg',
            'summary' => 'Multi-peptide research blend, supplied as a lyophilized powder in a single vial.',
            'featured' => true,
            'prices' => [1 => 12500, 3 => 12100, 6 => 11500, 10 => 11200],
        ],
        [
            'slug' => 'ghk-cu-50mg',
            'name' => 'GHK-Cu',
            'amount' => '50 mg',
            'summary' => 'Copper tripeptide complex, supplied as a lyophilized powder for laboratory research.',
            'featured' => false,
            'prices' => [1 => 4700, 3 => 4400, 6 => 4000, 10 => 3600],
        ],
        [
            'slug' => 'bpc-157-tb-500-20mg',
            'name' => 'BPC-157 / TB-500',
            'amount' => '20 mg blend',
            'summary' => 'Two-peptide research blend, 20 mg total per vial, supplied as a lyophilized powder.',
            'featured' => true,
            'prices' => [1 => 9600, 3 => 9200, 6 => 8600, 10 => 8300],
        ],
        [
            'slug' => 'nad-plus-1000mg',
            'name' => 'NAD+',
            'amount' => '1,000 mg',
            'summary' => 'Nicotinamide adenine dinucleotide, supplied as a lyophilized powder for laboratory research.',
            'featured' => true,
            'prices' => [1 => 7900, 3 => 7500, 6 => 6900, 10 => 6300],
        ],
        [
            'slug' => 'mots-c-10mg',
            'name' => 'MOTS-C',
            'amount' => '10 mg',
            'summary' => 'Mitochondrial-derived peptide, supplied as a lyophilized powder for laboratory research.',
            'featured' => false,
            'prices' => [1 => 4700, 3 => 4400, 6 => 4000, 10 => 3600],
        ],
    ];

    public function run(): void
    {
        $currency = config()->string('shop.currency');

        collect(self::PRODUCTS)->each(function (array $entry, int $position) use ($currency): void {
            $product = Product::query()->firstOrCreate(['slug' => $entry['slug']], [
                'type' => ResearchPeptideProductType::KEY,
                'name' => $entry['name'],
                'summary' => $entry['summary'],
                'details' => ['amount' => $entry['amount']],
                'stock' => 0,
                'is_active' => true,
                'is_featured' => $entry['featured'],
                'sort_order' => $position * 10,
            ]);

            if (! $product->wasRecentlyCreated) {
                return;
            }

            collect($entry['prices'])->each(fn (int $minor, int $quantity) => $product->prices()->create([
                'currency' => $currency,
                'min_quantity' => $quantity,
                'unit_price_minor' => Money::ofMinor($minor, $currency),
            ]));
        });
    }
}
