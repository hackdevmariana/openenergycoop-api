<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge>
 */
class ChallengeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Challenge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-3 months', '+3 months');
        $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d') . ' +3 months');
        
        return [
            'name' => $this->generateChallengeName(),
            'description' => $this->faker->paragraph(3),
            'type' => $this->faker->randomElement(['individual', 'team', 'organization']),
            'target_kwh' => $this->faker->randomFloat(2, 100, 10000),
            'points_reward' => $this->faker->numberBetween(50, 1000),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $this->faker->boolean(80), // 80% activos
            'criteria' => $this->generateCriteria(),
            'icon' => $this->faker->randomElement([
                'solar-panel', 'wind-turbine', 'battery', 'leaf', 'lightning',
                'recycle', 'earth', 'water-drop', 'fire', 'snowflake'
            ]),
            'organization_id' => $this->faker->optional(0.7)->randomElement([
                Organization::factory(),
                null
            ]),
        ];
    }

    /**
     * Generate a challenge name
     */
    private function generateChallengeName(): string
    {
        $prefixes = [
            'Desafío', 'Reto', 'Misión', 'Meta', 'Objetivo',
            'Challenge', 'Quest', 'Goal'
        ];
        
        $themes = [
            'Solar', 'Eólico', 'Verde', 'Limpio', 'Sostenible',
            'Renovable', 'Eco', 'Energético', 'Ambiental'
        ];
        
        $actions = [
            'Generación', 'Producción', 'Ahorro', 'Eficiencia',
            'Reducción', 'Optimización', 'Maximización'
        ];
        
        $timeframes = [
            'Mensual', 'Trimestral', 'Semanal', 'Anual',
            'de Verano', 'de Invierno', 'de Primavera'
        ];

        $prefix = $this->faker->randomElement($prefixes);
        $theme = $this->faker->randomElement($themes);
        $action = $this->faker->randomElement($actions);
        $timeframe = $this->faker->optional(0.6)->randomElement($timeframes);

        return trim("$prefix $theme $action $timeframe");
    }

    /**
     * Generate challenge criteria
     */
    private function generateCriteria(): array
    {
        $baseCriteria = [
            'min_team_size' => $this->faker->optional(0.4)->numberBetween(3, 10),
            'max_team_size' => $this->faker->optional(0.3)->numberBetween(15, 50),
            'requires_verification' => $this->faker->boolean(30),
            'bonus_multiplier' => $this->faker->optional(0.2)->randomFloat(2, 1.1, 2.0),
        ];

        $specialCriteria = $this->faker->optional(0.5)->randomElement([
            ['peak_hours_only' => true],
            ['weekend_bonus' => 1.5],
            ['consecutive_days' => $this->faker->numberBetween(7, 30)],
            ['technology_type' => $this->faker->randomElement(['solar', 'wind', 'hydro', 'biomass'])],
            ['region_specific' => $this->faker->randomElement(['urban', 'rural', 'coastal'])],
        ]);

        return array_merge($baseCriteria, $specialCriteria ?? []);
    }

    /**
     * Indicate that the challenge is active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the challenge is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a team challenge
     */
    public function team(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'team',
            'target_kwh' => $this->faker->randomFloat(2, 1000, 50000), // Objetivos más altos para equipos
            'points_reward' => $this->faker->numberBetween(200, 2000),
        ]);
    }

    /**
     * Create an individual challenge
     */
    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'individual',
            'target_kwh' => $this->faker->randomFloat(2, 50, 1000), // Objetivos más bajos para individuos
            'points_reward' => $this->faker->numberBetween(25, 500),
        ]);
    }

    /**
     * Create an organization challenge
     */
    public function organization(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'organization',
            'target_kwh' => $this->faker->randomFloat(2, 10000, 100000), // Objetivos muy altos para organizaciones
            'points_reward' => $this->faker->numberBetween(1000, 10000),
            'organization_id' => Organization::factory(),
        ]);
    }

    /**
     * Create a current challenge (happening now)
     */
    public function current(): static
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', 'now');
        $endDate = $this->faker->dateTimeBetween('now', '+2 months');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
        ]);
    }

    /**
     * Create an upcoming challenge
     */
    public function upcoming(): static
    {
        $startDate = $this->faker->dateTimeBetween('+1 day', '+3 months');
        $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d') . ' +3 months');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
        ]);
    }

    /**
     * Create a past challenge
     */
    public function past(): static
    {
        $startDate = $this->faker->dateTimeBetween('-6 months', '-2 months');
        $endDate = $this->faker->dateTimeBetween($startDate, '-1 week');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Create a short-term challenge (1-2 weeks)
     */
    public function shortTerm(): static
    {
        $startDate = $this->faker->dateTimeBetween('-1 week', '+1 week');
        $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d') . ' +2 weeks');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'target_kwh' => $this->faker->randomFloat(2, 100, 2000), // Objetivos más bajos
        ]);
    }

    /**
     * Create a long-term challenge (3-6 months)
     */
    public function longTerm(): static
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate->format('Y-m-d') . ' +3 months', $startDate->format('Y-m-d') . ' +6 months');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'target_kwh' => $this->faker->randomFloat(2, 5000, 50000), // Objetivos más altos
            'points_reward' => $this->faker->numberBetween(500, 5000),
        ]);
    }

    /**
     * Create a challenge for a specific organization
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create a global challenge (no organization restriction)
     */
    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => null,
        ]);
    }

    /**
     * Create a high-reward challenge
     */
    public function highReward(): static
    {
        return $this->state(fn (array $attributes) => [
            'points_reward' => $this->faker->numberBetween(1000, 5000),
            'target_kwh' => $this->faker->randomFloat(2, 5000, 25000),
        ]);
    }

    /**
     * Create a beginner-friendly challenge
     */
    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_kwh' => $this->faker->randomFloat(2, 50, 500),
            'points_reward' => $this->faker->numberBetween(25, 200),
            'name' => 'Desafío para Principiantes - ' . $this->faker->words(2, true),
            'description' => 'Un desafío perfecto para quienes están comenzando en el mundo de la energía renovable.',
        ]);
    }

    /**
     * Create an expert-level challenge
     */
    public function expert(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_kwh' => $this->faker->randomFloat(2, 10000, 100000),
            'points_reward' => $this->faker->numberBetween(2000, 10000),
            'name' => 'Desafío Experto - ' . $this->faker->words(2, true),
            'description' => 'Un desafío avanzado para expertos en energía renovable que buscan superar sus límites.',
            'criteria' => array_merge($this->generateCriteria(), [
                'requires_verification' => true,
                'expert_level' => true,
                'min_experience_months' => 12,
            ]),
        ]);
    }

    /**
     * Create a seasonal challenge
     */
    public function seasonal(string $season = null): static
    {
        $seasons = [
            'summer' => [
                'name' => 'Desafío de Verano Solar',
                'description' => 'Aprovecha al máximo la energía solar durante los meses de verano.',
                'icon' => 'solar-panel',
                'criteria' => ['peak_summer_bonus' => 1.5, 'solar_focus' => true],
            ],
            'winter' => [
                'name' => 'Desafío de Invierno Eólico',
                'description' => 'Genera energía eólica durante los meses de mayor viento.',
                'icon' => 'wind-turbine',
                'criteria' => ['winter_wind_bonus' => 1.3, 'wind_focus' => true],
            ],
            'spring' => [
                'name' => 'Desafío de Primavera Verde',
                'description' => 'Celebra la renovación de la naturaleza con energía limpia.',
                'icon' => 'leaf',
                'criteria' => ['spring_growth_bonus' => 1.2],
            ],
            'autumn' => [
                'name' => 'Desafío de Otoño Eficiente',
                'description' => 'Optimiza tu consumo energético durante el otoño.',
                'icon' => 'recycle',
                'criteria' => ['efficiency_focus' => true, 'autumn_savings' => 1.4],
            ],
        ];

        $selectedSeason = $season ?? $this->faker->randomElement(array_keys($seasons));
        $seasonData = $seasons[$selectedSeason];

        return $this->state(fn (array $attributes) => [
            'name' => $seasonData['name'],
            'description' => $seasonData['description'],
            'icon' => $seasonData['icon'],
            'criteria' => array_merge($this->generateCriteria(), $seasonData['criteria']),
        ]);
    }
}
