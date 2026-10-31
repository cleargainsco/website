<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        throw_unless(
            app()->environment('local', 'testing'),
            RuntimeException::class,
            'DatabaseSeeder creates users with a known password. Deployments run ReferenceDataSeeder instead.',
        );

        $this->call(ReferenceDataSeeder::class);

        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        User::factory()->customer()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);
    }
}
