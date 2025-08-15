<?php

namespace Database\Factories;

use App\Models\ConsentLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsentLog>
 */
class ConsentLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $consentTypes = array_keys(ConsentLog::CONSENT_TYPES);
        
        return [
            'user_id' => User::factory(),
            'consent_type' => $this->faker->randomElement($consentTypes),
            'consented_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'version' => $this->faker->optional(0.7)->randomElement(['1.0', '1.1', '2.0', '2.1']),
            'consent_document_url' => $this->faker->optional(0.8)->url(),
            'revoked_at' => $this->faker->optional(0.1)->dateTimeBetween('-6 months', 'now'),
            'consent_context' => $this->faker->optional(0.5)->randomElements([
                'source' => $this->faker->randomElement(['registration', 'login', 'settings', 'onboarding']),
                'campaign' => $this->faker->optional()->word(),
                'referrer' => $this->faker->optional()->url(),
            ]),
        ];
    }

    /**
     * Estado para consentimiento activo (no revocado)
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => null,
        ]);
    }

    /**
     * Estado para consentimiento revocado
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => $this->faker->dateTimeBetween($attributes['consented_at'] ?? '-1 year', 'now'),
        ]);
    }

    /**
     * Estado para un tipo específico de consentimiento
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_type' => $type,
        ]);
    }

    /**
     * Estado para consentimiento reciente
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'consented_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
