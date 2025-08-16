<?php

namespace Database\Factories;

use App\Models\Collaborator;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollaboratorFactory extends Factory
{
    protected $model = Collaborator::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'logo' => 'collaborators/' . $this->faker->uuid() . '.png',
            'url' => $this->faker->optional()->url(),
            'description' => $this->faker->optional()->paragraph(),
            'order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(85), // 85% probabilidad de estar activo
            'collaborator_type' => $this->faker->randomElement(['partner', 'sponsor', 'member', 'supporter']),
            'organization_id' => Organization::factory(),
            'is_draft' => $this->faker->boolean(20), // 20% probabilidad de ser borrador
            'published_at' => function (array $attributes) {
                return $attributes['is_draft'] ? null : $this->faker->dateTimeBetween('-6 months', 'now');
            },
            'created_by_user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the collaborator is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the collaborator is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the collaborator is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the collaborator is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific collaborator type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'collaborator_type' => $type,
        ]);
    }

    /**
     * Set a specific order.
     */
    public function atOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order' => $order,
        ]);
    }

    /**
     * Create a partner collaborator.
     */
    public function partner(): static
    {
        return $this->ofType('partner');
    }

    /**
     * Create a sponsor collaborator.
     */
    public function sponsor(): static
    {
        return $this->ofType('sponsor');
    }

    /**
     * Create a member collaborator.
     */
    public function member(): static
    {
        return $this->ofType('member');
    }

    /**
     * Create a supporter collaborator.
     */
    public function supporter(): static
    {
        return $this->ofType('supporter');
    }

    /**
     * Create collaborator with specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }
}
