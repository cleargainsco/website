<?php

declare(strict_types=1);

namespace Shop\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shop\Models\DiscountCode;

/**
 * @extends Factory<DiscountCode>
 */
final class DiscountCodeFactory extends Factory
{
    protected $model = DiscountCode::class;

    public function definition(): array
    {
        return [
            'code' => mb_strtoupper(fake()->unique()->bothify('TEST-######')),
            'percent_off' => 10,
            'email' => null,
            'usage_limit' => null,
            'times_used' => 0,
            'expires_at' => null,
        ];
    }

    public function singleUse(): static
    {
        return $this->state(['usage_limit' => 1]);
    }

    public function forEmail(string $email): static
    {
        return $this->state(['email' => mb_strtolower($email)]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay()]);
    }
}
