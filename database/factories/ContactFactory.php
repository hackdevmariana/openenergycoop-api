<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'address' => $this->faker->address(),
            'icon_address' => $this->faker->randomElement(['fas fa-map-marker-alt', 'fas fa-building', 'fas fa-home']),
            'phone' => $this->faker->phoneNumber(),
            'icon_phone' => $this->faker->randomElement(['fas fa-phone', 'fas fa-mobile-alt', 'fas fa-phone-alt']),
            'email' => $this->faker->email(),
            'icon_email' => $this->faker->randomElement(['fas fa-envelope', 'fas fa-at', 'fas fa-mail-bulk']),
            'latitude' => $this->faker->latitude(40.0, 42.0), // Spain area
            'longitude' => $this->faker->longitude(-5.0, 3.0), // Spain area
            'contact_type' => $this->faker->randomElement(['main', 'support', 'sales', 'media', 'technical']),
            'business_hours' => [
                'monday' => ['open' => '09:00', 'close' => '18:00'],
                'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                'thursday' => ['open' => '09:00', 'close' => '18:00'],
                'friday' => ['open' => '09:00', 'close' => '17:00'],
            ],
            'additional_info' => $this->faker->optional()->paragraph(),
            'organization_id' => null, // Can be overridden
            'is_draft' => false,
            'is_primary' => false,
            'created_by_user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the contact is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
        ]);
    }

    /**
     * Indicate that the contact is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
        ]);
    }

    /**
     * Indicate that the contact is primary.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Create a main contact.
     */
    public function main(): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_type' => 'main',
        ]);
    }

    /**
     * Create a support contact.
     */
    public function support(): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_type' => 'support',
        ]);
    }

    /**
     * Create a sales contact.
     */
    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_type' => 'sales',
        ]);
    }

    /**
     * Create a technical contact.
     */
    public function technical(): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_type' => 'technical',
        ]);
    }

    /**
     * Create a contact with location data.
     */
    public function withLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(40.0, 42.0),
            'longitude' => $this->faker->longitude(-5.0, 3.0),
        ]);
    }

    /**
     * Create a contact without location data.
     */
    public function withoutLocation(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    /**
     * Create a contact with minimal data (only required fields).
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => null,
            'icon_address' => null,
            'phone' => null,
            'icon_phone' => null,
            'email' => null,
            'icon_email' => null,
            'latitude' => null,
            'longitude' => null,
            'business_hours' => null,
            'additional_info' => null,
        ]);
    }

    /**
     * Create a contact with extended business hours.
     */
    public function extendedHours(): static
    {
        return $this->state(fn (array $attributes) => [
            'business_hours' => [
                'monday' => ['open' => '08:00', 'close' => '20:00'],
                'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                'thursday' => ['open' => '08:00', 'close' => '20:00'],
                'friday' => ['open' => '08:00', 'close' => '20:00'],
                'saturday' => ['open' => '09:00', 'close' => '14:00'],
            ],
        ]);
    }

    /**
     * Create a contact with 24/7 availability.
     */
    public function always(): static
    {
        return $this->state(fn (array $attributes) => [
            'business_hours' => null, // No hours means always available
            'contact_type' => 'support', // Support is usually 24/7
        ]);
    }

    /**
     * Create a contact with specific organization.
     */
    public function withOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }
}
