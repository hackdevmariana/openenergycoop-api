<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'avatar' => $this->faker->boolean(60) ? $this->faker->imageUrl(200, 200, 'people') : null,
            'bio' => $this->faker->boolean(80) ? $this->faker->paragraph() : null,
            'municipality_id' => $this->faker->randomElement(['28001', '28002', '28003', '28004', '28005']),
            'join_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'role_in_cooperative' => $this->faker->randomElement(['miembro', 'voluntario', 'promotor', 'coordinador']),
            'profile_completed' => $this->faker->boolean(70),
            'newsletter_opt_in' => $this->faker->boolean(60),
            'show_in_rankings' => $this->faker->boolean(85),
            'co2_avoided_total' => $this->faker->randomFloat(2, 0, 5000),
            'kwh_produced_total' => $this->faker->randomFloat(2, 0, 10000),
            'points_total' => $this->faker->numberBetween(0, 5000),
            'badges_earned' => $this->generateBadges(),
            'birth_date' => $this->faker->dateTimeBetween('-70 years', '-18 years'),
            'organization_id' => Organization::factory(),
            'team_id' => $this->faker->boolean(40) ? $this->faker->word() : null,
        ];
    }

    /**
     * Generate random badges array
     */
    private function generateBadges(): array
    {
        $availableBadges = [
            'first_kwh', 'eco_warrior', 'community_builder', 'green_champion',
            'energy_saver', 'solar_pioneer', 'carbon_reducer', 'team_player'
        ];

        $badgeCount = $this->faker->numberBetween(0, 5);
        return $this->faker->randomElements($availableBadges, $badgeCount);
    }

    /**
     * Indicate that the profile is completed
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_completed' => true,
            'bio' => $this->faker->paragraph(),
            'municipality_id' => $this->faker->randomElement(['28001', '28002', '28003']),
            'join_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
        ]);
    }

    /**
     * Indicate that the profile is incomplete
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_completed' => false,
            'bio' => null,
            'municipality_id' => null,
            'birth_date' => null,
        ]);
    }

    /**
     * Indicate that the user is active in rankings
     */
    public function inRankings(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_rankings' => true,
        ]);
    }

    /**
     * Indicate that the user is not in rankings
     */
    public function notInRankings(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_rankings' => false,
        ]);
    }

    /**
     * Indicate that the user has high energy production
     */
    public function highProducer(): static
    {
        return $this->state(fn (array $attributes) => [
            'kwh_produced_total' => $this->faker->randomFloat(2, 5000, 15000),
            'co2_avoided_total' => $this->faker->randomFloat(2, 2000, 6000),
            'points_total' => $this->faker->numberBetween(3000, 8000),
        ]);
    }

    /**
     * Indicate that the user is a new member
     */
    public function newMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'join_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'kwh_produced_total' => $this->faker->randomFloat(2, 0, 500),
            'co2_avoided_total' => $this->faker->randomFloat(2, 0, 200),
            'points_total' => $this->faker->numberBetween(0, 500),
            'badges_earned' => [],
        ]);
    }

    /**
     * Indicate that the user is a veteran member
     */
    public function veteran(): static
    {
        return $this->state(fn (array $attributes) => [
            'join_date' => $this->faker->dateTimeBetween('-3 years', '-1 year'),
            'kwh_produced_total' => $this->faker->randomFloat(2, 3000, 12000),
            'co2_avoided_total' => $this->faker->randomFloat(2, 1500, 5000),
            'points_total' => $this->faker->numberBetween(2000, 7000),
            'badges_earned' => $this->faker->randomElements([
                'first_kwh', 'eco_warrior', 'community_builder', 'green_champion',
                'energy_saver', 'solar_pioneer', 'carbon_reducer', 'team_player', 'veteran'
            ], $this->faker->numberBetween(3, 7)),
        ]);
    }

    /**
     * Indicate that the user opted in for newsletter
     */
    public function newsletterOptIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'newsletter_opt_in' => true,
        ]);
    }

    /**
     * Indicate that the user is a coordinator
     */
    public function coordinator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_in_cooperative' => 'coordinador',
            'points_total' => $this->faker->numberBetween(2000, 6000),
        ]);
    }

    /**
     * Indicate that the user is a promoter
     */
    public function promoter(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_in_cooperative' => 'promotor',
            'badges_earned' => array_merge(
                $this->generateBadges(),
                ['community_builder', 'green_champion']
            ),
        ]);
    }

    /**
     * Indicate that the user belongs to a specific organization
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Indicate that the user belongs to a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the user is part of a team
     */
    public function withTeam(string $teamId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $teamId ?? $this->faker->word(),
        ]);
    }
}
