<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;
use Shop\Models\Product;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        throw_unless(
            app()->environment(config()->array('app.lower_environments')),
            RuntimeException::class,
            'DatabaseSeeder creates users with a known password, so it only runs in '.implode(', ', config()->array('app.lower_environments')).'. Deployments run ReferenceDataSeeder instead.',
        );

        $this->call([ReferenceDataSeeder::class, CatalogSeeder::class]);

        Product::query()->where('stock', 0)->update(['stock' => 50]);

        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        User::factory()->customer()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        $this->command?->info('Sign in as admin@example.com or customer@example.com with the password "'.UserFactory::PASSWORD.'".');
    }
}
