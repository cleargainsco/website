<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Shop\Models\Product;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Built without the user factory: staging installs without dev dependencies, so Faker is not there.
     *
     * @var list<array{name: string, email: string, role: RoleName}>
     */
    private const array ACCOUNTS = [
        ['name' => 'Admin User', 'email' => 'admin@example.com', 'role' => RoleName::Admin],
        ['name' => 'Test Customer', 'email' => 'customer@example.com', 'role' => RoleName::Customer],
    ];

    public function run(): void
    {
        throw_unless(
            app()->environment(config()->array('app.lower_environments')),
            RuntimeException::class,
            'DatabaseSeeder creates users with a known password, so it only runs in '.implode(', ', config()->array('app.lower_environments')).'. Deployments run ReferenceDataSeeder instead.',
        );

        $this->call([ReferenceDataSeeder::class, CatalogSeeder::class]);

        Product::query()->where('stock', 0)->update(['stock' => 50]);

        collect(self::ACCOUNTS)
            ->reject(fn (array $account): bool => User::query()->where('email', $account['email'])->exists())
            ->each(function (array $account): void {
                $user = new User;
                $user->forceFill([
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'password' => Hash::make(UserFactory::PASSWORD),
                    'email_verified_at' => now(),
                ])->save();

                $user->assignRole($account['role']);
            });

        $this->command?->info('Sign in as admin@example.com or customer@example.com with the password "'.UserFactory::PASSWORD.'".');
    }
}
