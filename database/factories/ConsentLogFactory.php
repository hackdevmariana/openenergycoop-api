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
            'consent_given' => $this->faker->boolean(85), // 85% chance of consent given
            'consented_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'version' => $this->faker->optional(0.7)->randomElement(['1.0', '1.1', '2.0', '2.1']),
            'purpose' => $this->faker->optional(0.6)->sentence(),
            'legal_basis' => $this->faker->optional(0.5)->randomElement([
                'Artículo 6.1.a GDPR - Consentimiento',
                'Artículo 6.1.b GDPR - Ejecución de contrato',
                'Artículo 6.1.f GDPR - Interés legítimo'
            ]),
            'data_categories' => $this->faker->optional(0.4)->randomElements([
                'personal_data', 'contact_info', 'usage_data', 'preferences', 'location_data'
            ], $this->faker->numberBetween(1, 3)),
            'retention_period' => $this->faker->optional(0.5)->randomElement([
                '5 años', '2 años', '1 año', 'Hasta revocación'
            ]),
            'third_parties' => $this->faker->optional(0.3)->randomElements([
                'Google Analytics', 'Mailchimp', 'Stripe', 'Zendesk'
            ], $this->faker->numberBetween(1, 2)),
            'withdrawal_method' => $this->faker->optional(0.4)->randomElement([
                'Contactar a privacy@example.com',
                'Configuración de cuenta',
                'Formulario de contacto'
            ]),
            'consent_document_url' => $this->faker->optional(0.8)->url(),
            'revoked_at' => $this->faker->optional(0.1)->dateTimeBetween('-6 months', 'now'),
            'revocation_reason' => $this->faker->optional(0.05)->sentence(),
            'consent_context' => $this->faker->optional(0.5)->randomElements([
                'source' => $this->faker->randomElement(['registration', 'login', 'settings', 'onboarding']),
                'campaign' => $this->faker->optional()->word(),
                'referrer' => $this->faker->optional()->url(),
            ]),
            'metadata' => $this->faker->optional(0.3)->randomElements([
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari']),
                'device' => $this->faker->randomElement(['mobile', 'desktop', 'tablet']),
                'locale' => $this->faker->randomElement(['es', 'en', 'ca']),
            ]),
        ];
    }

    /**
     * Estado para consentimiento activo (no revocado)
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_given' => true,
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

    /**
     * Estado para consentimiento dado
     */
    public function given(): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_given' => true,
        ]);
    }

    /**
     * Estado para consentimiento denegado
     */
    public function denied(): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_given' => false,
        ]);
    }

    /**
     * Estado para un usuario específico
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
