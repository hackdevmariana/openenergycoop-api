<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMembership>
 */
class TeamMembershipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TeamMembership::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'joined_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'role' => $this->faker->randomElement(['member', 'member', 'member', 'moderator', 'admin']), // Más miembros regulares
            'left_at' => null, // Por defecto, membresías activas
        ];
    }

    /**
     * Indicate that the membership is active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'left_at' => null,
        ]);
    }

    /**
     * Indicate that the membership is inactive (user left)
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'left_at' => $this->faker->dateTimeBetween($attributes['joined_at'] ?? '-3 months', 'now'),
        ]);
    }

    /**
     * Indicate that the user is an admin
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user is a moderator
     */
    public function moderator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'moderator',
        ]);
    }

    /**
     * Indicate that the user is a regular member
     */
    public function member(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'member',
        ]);
    }

    /**
     * Indicate that the membership is for a specific team
     */
    public function forTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
        ]);
    }

    /**
     * Indicate that the membership is for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the user joined recently (last 30 days)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the user is a veteran member (joined more than 6 months ago)
     */
    public function veteran(): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
        ]);
    }

    /**
     * Indicate that the user joined today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => now(),
        ]);
    }

    /**
     * Indicate that the user left recently
     */
    public function leftRecently(): static
    {
        $joinedAt = $this->faker->dateTimeBetween('-6 months', '-1 month');
        
        return $this->state(fn (array $attributes) => [
            'joined_at' => $joinedAt,
            'left_at' => $this->faker->dateTimeBetween($joinedAt, 'now'),
        ]);
    }

    /**
     * Create a founding member (admin who joined early)
     */
    public function founder(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'joined_at' => $this->faker->dateTimeBetween('-1 year', '-8 months'),
        ]);
    }

    /**
     * Create a long-term member with moderator privileges
     */
    public function longTermModerator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'moderator',
            'joined_at' => $this->faker->dateTimeBetween('-8 months', '-3 months'),
        ]);
    }

    /**
     * Create a membership with specific duration
     */
    public function withDuration(string $startDate, string $endDate = null): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => $this->faker->dateTimeBetween($startDate, $startDate),
            'left_at' => $endDate ? $this->faker->dateTimeBetween($endDate, $endDate) : null,
        ]);
    }

    /**
     * Create a short-term membership (joined and left within 2 months)
     */
    public function shortTerm(): static
    {
        $joinedAt = $this->faker->dateTimeBetween('-6 months', '-2 months');
        $leftAt = $this->faker->dateTimeBetween($joinedAt, $joinedAt->format('Y-m-d') . ' +2 months');
        
        return $this->state(fn (array $attributes) => [
            'joined_at' => $joinedAt,
            'left_at' => $leftAt,
        ]);
    }

    /**
     * Create memberships for team building scenario
     */
    public function teamBuilding(): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
            'role' => $this->faker->randomElement(['member', 'member', 'moderator']),
        ]);
    }

    /**
     * Create a membership that represents team leadership rotation
     */
    public function leadershipRotation(): static
    {
        // Simular cambios de liderazgo
        $role = $this->faker->randomElement(['admin', 'moderator']);
        $joinedAt = $this->faker->dateTimeBetween('-1 year', '-6 months');
        
        return $this->state(fn (array $attributes) => [
            'role' => $role,
            'joined_at' => $joinedAt,
            // Algunos pueden haber dejado el liderazgo pero seguir en el equipo
            'left_at' => $this->faker->optional(0.2)->dateTimeBetween($joinedAt, 'now'),
        ]);
    }

    /**
     * Create active membership with random role distribution
     */
    public function randomActiveRole(): static
    {
        $roles = ['member', 'member', 'member', 'member', 'member', 'moderator', 'admin'];
        
        return $this->state(fn (array $attributes) => [
            'role' => $this->faker->randomElement($roles),
            'left_at' => null,
        ]);
    }
}
