<?php

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAchievement>
 */
class UserAchievementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserAchievement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'achievement_id' => Achievement::factory(),
            'earned_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'custom_message' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'reward_granted' => $this->faker->boolean(60),
        ];
    }

    /**
     * Indicate that the reward has been granted
     */
    public function rewardGranted(): static
    {
        return $this->state(fn (array $attributes) => [
            'reward_granted' => true,
        ]);
    }

    /**
     * Indicate that the reward is pending
     */
    public function pendingReward(): static
    {
        return $this->state(fn (array $attributes) => [
            'reward_granted' => false,
        ]);
    }

    /**
     * Indicate that the achievement was earned recently
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'earned_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the achievement was earned long ago
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'earned_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
        ]);
    }

    /**
     * Indicate that the achievement was earned today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'earned_at' => now(),
        ]);
    }

    /**
     * Indicate that the achievement has a custom message
     */
    public function withCustomMessage(string $message = null): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_message' => $message ?? $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the achievement has no custom message
     */
    public function withoutCustomMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_message' => null,
        ]);
    }

    /**
     * Indicate that the achievement is for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the achievement is for a specific achievement
     */
    public function forAchievement(Achievement $achievement): static
    {
        return $this->state(fn (array $attributes) => [
            'achievement_id' => $achievement->id,
        ]);
    }

    /**
     * Indicate that the achievement is energy-related
     */
    public function energyAchievement(): static
    {
        return $this->state(fn (array $attributes) => [
            'achievement_id' => Achievement::factory()->energy(),
            'custom_message' => $this->faker->randomElement([
                '¡Felicidades por tu primera producción de energía!',
                '¡Excelente trabajo generando energía limpia!',
                '¡Sigue así, cada kWh cuenta!',
            ]),
        ]);
    }

    /**
     * Indicate that the achievement is participation-related
     */
    public function participationAchievement(): static
    {
        return $this->state(fn (array $attributes) => [
            'achievement_id' => Achievement::factory()->participation(),
            'custom_message' => $this->faker->randomElement([
                '¡Gracias por ser un miembro activo!',
                '¡Tu participación hace la diferencia!',
                '¡Excelente compromiso con la comunidad!',
            ]),
        ]);
    }

    /**
     * Indicate that the achievement is community-related
     */
    public function communityAchievement(): static
    {
        return $this->state(fn (array $attributes) => [
            'achievement_id' => Achievement::factory()->community(),
            'custom_message' => $this->faker->randomElement([
                '¡Gracias por ayudar a crecer nuestra comunidad!',
                '¡Eres un embajador excepcional!',
                '¡Tu trabajo de divulgación es invaluable!',
            ]),
        ]);
    }

    /**
     * Indicate that the achievement is a milestone
     */
    public function milestoneAchievement(): static
    {
        return $this->state(fn (array $attributes) => [
            'achievement_id' => Achievement::factory()->milestone(),
            'custom_message' => $this->faker->randomElement([
                '¡Felicidades por este importante hito!',
                '¡Has alcanzado una meta significativa!',
                '¡Tu dedicación es inspiradora!',
            ]),
        ]);
    }
}
