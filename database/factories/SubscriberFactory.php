<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriberSource;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscriber>
 */
final class SubscriberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'source' => SubscriberSource::WelcomeOffer,
            'marketing_consent_at' => now(),
        ];
    }

    public function withoutConsent(): static
    {
        return $this->state(['marketing_consent_at' => null]);
    }

    public function synced(): static
    {
        return $this->state(['resend_contact_id' => fake()->uuid(), 'synced_to_resend_at' => now()]);
    }
}
