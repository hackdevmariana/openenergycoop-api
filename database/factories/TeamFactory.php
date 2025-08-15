<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        
        return [
            'name' => ucwords($name),
            'slug' => \Str::slug($name),
            'description' => $this->faker->paragraph(),
            'created_by_user_id' => User::factory(),
            'organization_id' => Organization::factory(),
            'is_open' => $this->faker->boolean(70), // 70% probabilidad de ser abierto
            'max_members' => $this->faker->optional(0.6)->numberBetween(5, 50), // 60% tienen límite
            'logo_path' => $this->faker->optional(0.3)->imageUrl(200, 200, 'business'),
        ];
    }

    /**
     * Indicate that the team is open for anyone to join
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => true,
        ]);
    }

    /**
     * Indicate that the team is closed (invitation only)
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => false,
        ]);
    }

    /**
     * Indicate that the team has a member limit
     */
    public function withMemberLimit(int $maxMembers = null): static
    {
        return $this->state(fn (array $attributes) => [
            'max_members' => $maxMembers ?? $this->faker->numberBetween(10, 30),
        ]);
    }

    /**
     * Indicate that the team has no member limit
     */
    public function withoutMemberLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_members' => null,
        ]);
    }

    /**
     * Indicate that the team has a logo
     */
    public function withLogo(): static
    {
        return $this->state(fn (array $attributes) => [
            'logo_path' => $this->faker->imageUrl(200, 200, 'business'),
        ]);
    }

    /**
     * Indicate that the team has no logo
     */
    public function withoutLogo(): static
    {
        return $this->state(fn (array $attributes) => [
            'logo_path' => null,
        ]);
    }

    /**
     * Indicate that the team belongs to a specific organization
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Indicate that the team was created by a specific user
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by_user_id' => $user->id,
        ]);
    }

    /**
     * Create a small team (5-10 members limit)
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_members' => $this->faker->numberBetween(5, 10),
        ]);
    }

    /**
     * Create a medium team (11-25 members limit)
     */
    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_members' => $this->faker->numberBetween(11, 25),
        ]);
    }

    /**
     * Create a large team (26-50 members limit)
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_members' => $this->faker->numberBetween(26, 50),
        ]);
    }

    /**
     * Create a team with eco-friendly name
     */
    public function ecoFriendly(): static
    {
        $ecoNames = [
            'Green Warriors', 'Eco Champions', 'Solar Squad', 'Wind Power Team',
            'Renewable Rangers', 'Clean Energy Crew', 'Sustainability Stars',
            'Carbon Crushers', 'Earth Guardians', 'Energy Savers'
        ];

        $name = $this->faker->randomElement($ecoNames);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => \Str::slug($name),
            'description' => 'Un equipo comprometido con la energía renovable y la sostenibilidad ambiental.',
        ]);
    }

    /**
     * Create a competitive team (closed, with specific limits)
     */
    public function competitive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => false,
            'max_members' => $this->faker->numberBetween(10, 20),
            'description' => 'Equipo competitivo enfocado en alcanzar los mejores resultados en desafíos de energía renovable.',
        ]);
    }

    /**
     * Create a community team (open, no limits)
     */
    public function community(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => true,
            'max_members' => null,
            'description' => 'Equipo abierto a toda la comunidad para colaborar en proyectos de energía sostenible.',
        ]);
    }

    /**
     * Create a team with a specific name pattern
     */
    public function withNamePattern(string $pattern): static
    {
        $names = match ($pattern) {
            'tech' => ['Tech Innovators', 'Digital Green', 'Smart Energy', 'Tech for Earth'],
            'local' => ['Local Heroes', 'Neighborhood Power', 'Community First', 'Local Impact'],
            'student' => ['Student Green Team', 'Young Eco Warriors', 'Future Leaders', 'Campus Sustainability'],
            'professional' => ['Pro Green Team', 'Corporate Sustainability', 'Business for Earth', 'Professional Impact'],
            default => ['Team Alpha', 'Team Beta', 'Team Gamma', 'Team Delta']
        };

        $name = $this->faker->randomElement($names);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => \Str::slug($name),
        ]);
    }
}
