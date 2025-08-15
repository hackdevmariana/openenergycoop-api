<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Achievement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['energy', 'participation', 'community', 'milestone'];
        
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'icon' => $this->faker->randomElement(['🌱', '⚡', '🌍', '🏆', '💚', '🔋', '☀️', '🌿']),
            'type' => $this->faker->randomElement($types),
            'criteria' => $this->generateCriteria(),
            'points_reward' => $this->faker->numberBetween(10, 500),
            'is_active' => $this->faker->boolean(85), // 85% activos
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Generate random criteria for achievements
     */
    private function generateCriteria(): array
    {
        $criteriaTypes = [
            ['type' => 'kwh_produced', 'value' => $this->faker->numberBetween(100, 5000)],
            ['type' => 'co2_avoided', 'value' => $this->faker->numberBetween(50, 1000)],
            ['type' => 'days_active', 'value' => $this->faker->numberBetween(30, 365)],
            ['type' => 'referrals', 'value' => $this->faker->numberBetween(1, 20)],
        ];

        return $this->faker->randomElement($criteriaTypes);
    }

    /**
     * Indicate that the achievement is for energy production
     */
    public function energy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'energy',
            'name' => $this->faker->randomElement([
                'Primer kWh Generado',
                'Generador Solar',
                'Maestro de la Energía',
                'Productor Verde',
            ]),
            'icon' => '⚡',
            'criteria' => ['type' => 'kwh_produced', 'value' => $this->faker->numberBetween(100, 2000)],
        ]);
    }

    /**
     * Indicate that the achievement is for community participation
     */
    public function participation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'participation',
            'name' => $this->faker->randomElement([
                'Miembro Activo',
                'Participante Comprometido',
                'Líder Comunitario',
                'Embajador Verde',
            ]),
            'icon' => '👥',
            'criteria' => ['type' => 'days_active', 'value' => $this->faker->numberBetween(30, 180)],
        ]);
    }

    /**
     * Indicate that the achievement is for community building
     */
    public function community(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'community',
            'name' => $this->faker->randomElement([
                'Constructor de Comunidad',
                'Referidor Estrella',
                'Evangelista Verde',
                'Conector Social',
            ]),
            'icon' => '🌍',
            'criteria' => ['type' => 'referrals', 'value' => $this->faker->numberBetween(3, 15)],
        ]);
    }

    /**
     * Indicate that the achievement is a milestone
     */
    public function milestone(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'milestone',
            'name' => $this->faker->randomElement([
                'Primer Año',
                'Veterano de la Cooperativa',
                'Pionero Verde',
                'Leyenda Sostenible',
            ]),
            'icon' => '🏆',
            'criteria' => ['type' => 'days_active', 'value' => $this->faker->numberBetween(365, 1825)],
            'points_reward' => $this->faker->numberBetween(100, 1000),
        ]);
    }

    /**
     * Indicate that the achievement is active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the achievement is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the achievement has high points reward
     */
    public function highReward(): static
    {
        return $this->state(fn (array $attributes) => [
            'points_reward' => $this->faker->numberBetween(500, 1500),
        ]);
    }

    /**
     * Indicate that the achievement has low points reward
     */
    public function lowReward(): static
    {
        return $this->state(fn (array $attributes) => [
            'points_reward' => $this->faker->numberBetween(10, 100),
        ]);
    }
}
