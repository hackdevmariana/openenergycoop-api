<?php

namespace Database\Factories;

use App\Models\FaqTopic;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FaqTopic>
 */
class FaqTopicFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FaqTopic::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Membresía', 'Facturación', 'Técnico', 'Energía Renovable', 
            'Cooperativa', 'Sostenibilidad', 'Comunidad', 'General'
        ]);

        return [
            'name' => $name,
            'slug' => \Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 1000),
            'description' => $this->faker->sentence(10),
            'icon' => $this->faker->randomElement([
                'fas fa-users', 'fas fa-credit-card', 'fas fa-cog', 'fas fa-leaf',
                'fas fa-handshake', 'fas fa-recycle', 'fas fa-home', 'fas fa-question'
            ]),
            'color' => $this->faker->hexColor(),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
            'organization_id' => null, // Can be overridden
            'language' => $this->faker->randomElement(['es', 'en', 'ca']),
        ];
    }

    /**
     * Indicate that the topic is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a topic with specific language.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
        ]);
    }

    /**
     * Create a topic with specific language.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
        ]);
    }

    /**
     * Create a topic with high priority (low sort_order).
     */
    public function priority(): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $this->faker->numberBetween(0, 10),
        ]);
    }

    /**
     * Create a topic with specific organization.
     */
    public function withOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }
}
