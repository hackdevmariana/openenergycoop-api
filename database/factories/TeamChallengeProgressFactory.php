<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Team;
use App\Models\TeamChallengeProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamChallengeProgress>
 */
class TeamChallengeProgressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TeamChallengeProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'challenge_id' => Challenge::factory(),
            'progress_kwh' => $this->faker->randomFloat(2, 0, 5000),
            'completed_at' => null, // Por defecto, no completado
        ];
    }

    /**
     * Indicate that the challenge is completed
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            // Asegurar que el progreso sea igual o mayor al objetivo
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            
            return [
                'progress_kwh' => $this->faker->randomFloat(2, $challenge->target_kwh, $challenge->target_kwh * 1.5),
                'completed_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
            ];
        });
    }

    /**
     * Indicate that the challenge is in progress
     */
    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            // Progreso entre 10% y 95% del objetivo
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            $minProgress = $challenge->target_kwh * 0.1;
            $maxProgress = $challenge->target_kwh * 0.95;
            
            return [
                'progress_kwh' => $this->faker->randomFloat(2, $minProgress, $maxProgress),
                'completed_at' => null,
            ];
        });
    }

    /**
     * Indicate that the challenge was just started (low progress)
     */
    public function justStarted(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            $maxProgress = $challenge->target_kwh * 0.15; // Máximo 15% del objetivo
            
            return [
                'progress_kwh' => $this->faker->randomFloat(2, 0, $maxProgress),
                'completed_at' => null,
            ];
        });
    }

    /**
     * Indicate that the challenge is nearly completed (80-99%)
     */
    public function nearCompletion(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            $minProgress = $challenge->target_kwh * 0.8;
            $maxProgress = $challenge->target_kwh * 0.99;
            
            return [
                'progress_kwh' => $this->faker->randomFloat(2, $minProgress, $maxProgress),
                'completed_at' => null,
            ];
        });
    }

    /**
     * Indicate that the challenge exceeded expectations
     */
    public function exceeded(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            $minProgress = $challenge->target_kwh * 1.1; // Al menos 110%
            $maxProgress = $challenge->target_kwh * 2.0; // Hasta 200%
            
            return [
                'progress_kwh' => $this->faker->randomFloat(2, $minProgress, $maxProgress),
                'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * Create progress for a specific team
     */
    public function forTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
        ]);
    }

    /**
     * Create progress for a specific challenge
     */
    public function forChallenge(Challenge $challenge): static
    {
        return $this->state(fn (array $attributes) => [
            'challenge_id' => $challenge->id,
        ]);
    }

    /**
     * Create progress with specific percentage of completion
     */
    public function withProgressPercentage(float $percentage): static
    {
        return $this->state(function (array $attributes) use ($percentage) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            $targetProgress = $challenge->target_kwh * ($percentage / 100);
            
            return [
                'progress_kwh' => $targetProgress,
                'completed_at' => $percentage >= 100 ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            ];
        });
    }

    /**
     * Create progress with specific kWh amount
     */
    public function withProgress(float $kwhAmount): static
    {
        return $this->state(function (array $attributes) use ($kwhAmount) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            
            return [
                'progress_kwh' => $kwhAmount,
                'completed_at' => $kwhAmount >= $challenge->target_kwh 
                    ? $this->faker->dateTimeBetween('-1 month', 'now') 
                    : null,
            ];
        });
    }

    /**
     * Create recently completed progress
     */
    public function recentlyCompleted(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            
            return [
                'progress_kwh' => $this->faker->randomFloat(2, $challenge->target_kwh, $challenge->target_kwh * 1.3),
                'completed_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            ];
        });
    }

    /**
     * Create progress that was completed long ago
     */
    public function completedLongAgo(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            
            return [
                'progress_kwh' => $this->faker->randomFloat(2, $challenge->target_kwh, $challenge->target_kwh * 1.2),
                'completed_at' => $this->faker->dateTimeBetween('-6 months', '-2 months'),
            ];
        });
    }

    /**
     * Create competitive progress (high performance)
     */
    public function competitive(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            
            // Progreso alto, entre 70% y 150% del objetivo
            $minProgress = $challenge->target_kwh * 0.7;
            $maxProgress = $challenge->target_kwh * 1.5;
            $progress = $this->faker->randomFloat(2, $minProgress, $maxProgress);
            
            return [
                'progress_kwh' => $progress,
                'completed_at' => $progress >= $challenge->target_kwh 
                    ? $this->faker->dateTimeBetween('-2 weeks', 'now') 
                    : null,
            ];
        });
    }

    /**
     * Create struggling progress (low performance)
     */
    public function struggling(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            
            // Progreso bajo, entre 5% y 30% del objetivo
            $minProgress = $challenge->target_kwh * 0.05;
            $maxProgress = $challenge->target_kwh * 0.3;
            
            return [
                'progress_kwh' => $this->faker->randomFloat(2, $minProgress, $maxProgress),
                'completed_at' => null,
            ];
        });
    }

    /**
     * Create steady progress (consistent advancement)
     */
    public function steady(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            
            // Progreso moderado, entre 40% y 80% del objetivo
            $minProgress = $challenge->target_kwh * 0.4;
            $maxProgress = $challenge->target_kwh * 0.8;
            
            return [
                'progress_kwh' => $this->faker->randomFloat(2, $minProgress, $maxProgress),
                'completed_at' => null,
            ];
        });
    }

    /**
     * Create progress for leaderboard testing
     */
    public function forLeaderboard(int $rank = null): static
    {
        return $this->state(function (array $attributes) use ($rank) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            
            // Generar progreso basado en el ranking deseado
            if ($rank) {
                $baseProgress = $challenge->target_kwh * (1.5 - ($rank * 0.1)); // Ranking más alto = más progreso
                $variation = $challenge->target_kwh * 0.05; // 5% de variación
                $progress = max(0, $this->faker->randomFloat(2, 
                    $baseProgress - $variation, 
                    $baseProgress + $variation
                ));
            } else {
                $progress = $this->faker->randomFloat(2, 0, $challenge->target_kwh * 1.5);
            }
            
            return [
                'progress_kwh' => $progress,
                'completed_at' => $progress >= $challenge->target_kwh 
                    ? $this->faker->dateTimeBetween('-1 month', 'now') 
                    : null,
            ];
        });
    }

    /**
     * Create progress with realistic incremental updates
     */
    public function withIncrementalProgress(): static
    {
        return $this->state(function (array $attributes) {
            $challenge = Challenge::find($attributes['challenge_id']) ?? Challenge::factory()->create();
            
            // Simular progreso incremental realista
            $dailyAverage = $challenge->target_kwh / $challenge->duration_in_days;
            $daysElapsed = $this->faker->numberBetween(1, $challenge->duration_in_days);
            $efficiency = $this->faker->randomFloat(2, 0.7, 1.3); // Factor de eficiencia del equipo
            
            $expectedProgress = $dailyAverage * $daysElapsed * $efficiency;
            $actualProgress = $this->faker->randomFloat(2, 
                $expectedProgress * 0.8, 
                $expectedProgress * 1.2
            );
            
            return [
                'progress_kwh' => max(0, $actualProgress),
                'completed_at' => $actualProgress >= $challenge->target_kwh 
                    ? $this->faker->dateTimeBetween('-1 week', 'now') 
                    : null,
            ];
        });
    }
}
