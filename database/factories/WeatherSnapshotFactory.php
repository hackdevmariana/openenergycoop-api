<?php

namespace Database\Factories;

use App\Models\WeatherSnapshot;
use App\Models\Municipality;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WeatherSnapshot>
 */
class WeatherSnapshotFactory extends Factory
{
    protected $model = WeatherSnapshot::class;

    public function definition(): array
    {
        $municipality = Municipality::factory()->create();
        $timestamp = $this->faker->dateTimeBetween('-30 days', 'now');
        
        // Simular condiciones climáticas realistas para España
        [$temperature, $cloudCoverage, $solarRadiation] = $this->generateRealisticWeatherData($timestamp);

        return [
            'municipality_id' => $municipality->id,
            'temperature' => $temperature,
            'cloud_coverage' => $cloudCoverage,
            'solar_radiation' => $solarRadiation,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * Generate realistic weather data based on time of day and season
     */
    private function generateRealisticWeatherData($timestamp): array
    {
        $carbon = Carbon::parse($timestamp);
        $hour = $carbon->hour;
        $month = $carbon->month;
        
        // Temperaturas base por estación (España)
        $baseTemperature = match(true) {
            $month >= 12 || $month <= 2 => $this->faker->numberBetween(5, 15),   // Invierno
            $month >= 3 && $month <= 5 => $this->faker->numberBetween(12, 22),   // Primavera
            $month >= 6 && $month <= 8 => $this->faker->numberBetween(20, 35),   // Verano
            default => $this->faker->numberBetween(10, 25),                      // Otoño
        };
        
        // Variación por hora del día
        $hourVariation = match(true) {
            $hour >= 6 && $hour <= 8 => -3,    // Mañana temprano
            $hour >= 9 && $hour <= 11 => -1,   // Mañana
            $hour >= 12 && $hour <= 16 => 3,   // Mediodía/tarde
            $hour >= 17 && $hour <= 19 => 1,   // Tarde
            default => -5,                       // Noche
        };
        
        $temperature = round($baseTemperature + $hourVariation + $this->faker->numberBetween(-3, 3), 2);
        
        // Cobertura de nubes (afecta la radiación solar)
        $cloudCoverage = $this->faker->numberBetween(0, 100);
        
        // Radiación solar realista
        $solarRadiation = $this->calculateSolarRadiation($hour, $month, $cloudCoverage);
        
        return [$temperature, $cloudCoverage, $solarRadiation];
    }

    /**
     * Calculate realistic solar radiation based on hour, month and cloud coverage
     */
    private function calculateSolarRadiation(int $hour, int $month, float $cloudCoverage): float
    {
        // No hay radiación solar durante la noche
        if ($hour < 6 || $hour > 20) {
            return 0;
        }
        
        // Radiación base por estación en España
        $baseSolarRadiation = match(true) {
            $month >= 12 || $month <= 2 => 400,  // Invierno
            $month >= 3 && $month <= 5 => 600,   // Primavera
            $month >= 6 && $month <= 8 => 900,   // Verano
            default => 500,                       // Otoño
        };
        
        // Factor por hora del día (curva solar)
        $hourFactor = match(true) {
            $hour >= 6 && $hour <= 8 => 0.3,     // Amanecer
            $hour >= 9 && $hour <= 11 => 0.7,    // Mañana
            $hour >= 12 && $hour <= 15 => 1.0,   // Mediodía (pico)
            $hour >= 16 && $hour <= 18 => 0.6,   // Tarde
            default => 0.2,                       // Atardecer
        };
        
        // Reducción por nubes
        $cloudFactor = 1 - ($cloudCoverage / 100 * 0.8); // Las nubes pueden reducir hasta 80%
        
        $radiation = $baseSolarRadiation * $hourFactor * $cloudFactor;
        
        // Añadir algo de variabilidad
        $radiation += $this->faker->numberBetween(-50, 50);
        
        return round(max(0, $radiation), 2);
    }

    /**
     * Create weather snapshot for specific municipality
     */
    public function forMunicipality(Municipality $municipality): static
    {
        return $this->state(fn (array $attributes) => [
            'municipality_id' => $municipality->id,
        ]);
    }

    /**
     * Create weather snapshot for specific time
     */
    public function atTime(Carbon $timestamp): static
    {
        [$temperature, $cloudCoverage, $solarRadiation] = $this->generateRealisticWeatherData($timestamp);
        
        return $this->state(fn (array $attributes) => [
            'temperature' => $temperature,
            'cloud_coverage' => $cloudCoverage,
            'solar_radiation' => $solarRadiation,
            'timestamp' => $timestamp,
        ]);
    }

    /**
     * Create optimal solar conditions
     */
    public function optimalSolar(): static
    {
        return $this->state(fn (array $attributes) => [
            'temperature' => $this->faker->numberBetween(20, 28),
            'cloud_coverage' => $this->faker->numberBetween(0, 15),
            'solar_radiation' => $this->faker->numberBetween(800, 1200),
            'timestamp' => $this->faker->dateTimeBetween('10:00', '16:00'),
        ]);
    }

    /**
     * Create poor solar conditions
     */
    public function poorSolar(): static
    {
        return $this->state(fn (array $attributes) => [
            'temperature' => $this->faker->numberBetween(5, 15),
            'cloud_coverage' => $this->faker->numberBetween(70, 100),
            'solar_radiation' => $this->faker->numberBetween(0, 200),
        ]);
    }

    /**
     * Create summer conditions
     */
    public function summer(): static
    {
        return $this->state(function (array $attributes) {
            $hour = $this->faker->numberBetween(6, 20);
            [$temp, $clouds, $radiation] = $this->generateRealisticWeatherData(
                Carbon::createFromDate(2024, 7, 15)->setHour($hour)
            );
            
            return [
                'temperature' => $temp,
                'cloud_coverage' => $clouds,
                'solar_radiation' => $radiation,
                'timestamp' => Carbon::createFromDate(2024, 7, $this->faker->numberBetween(1, 31))
                                   ->setHour($hour)
                                   ->setMinute($this->faker->numberBetween(0, 59)),
            ];
        });
    }

    /**
     * Create winter conditions
     */
    public function winter(): static
    {
        return $this->state(function (array $attributes) {
            $hour = $this->faker->numberBetween(8, 18);
            [$temp, $clouds, $radiation] = $this->generateRealisticWeatherData(
                Carbon::createFromDate(2024, 1, 15)->setHour($hour)
            );
            
            return [
                'temperature' => $temp,
                'cloud_coverage' => $clouds,
                'solar_radiation' => $radiation,
                'timestamp' => Carbon::createFromDate(2024, 1, $this->faker->numberBetween(1, 31))
                                   ->setHour($hour)
                                   ->setMinute($this->faker->numberBetween(0, 59)),
            ];
        });
    }

    /**
     * Create midday snapshot (peak solar)
     */
    public function midday(): static
    {
        return $this->state(function (array $attributes) {
            $timestamp = $this->faker->dateTimeBetween('-7 days', 'now');
            $carbon = Carbon::parse($timestamp)->setHour(13)->setMinute($this->faker->numberBetween(0, 59));
            
            [$temp, $clouds, $radiation] = $this->generateRealisticWeatherData($carbon);
            
            return [
                'temperature' => $temp,
                'cloud_coverage' => $clouds,
                'solar_radiation' => $radiation,
                'timestamp' => $carbon,
            ];
        });
    }

    /**
     * Create night snapshot (no solar)
     */
    public function night(): static
    {
        return $this->state(function (array $attributes) {
            $timestamp = $this->faker->dateTimeBetween('-7 days', 'now');
            $carbon = Carbon::parse($timestamp)->setHour($this->faker->numberBetween(22, 5));
            
            return [
                'temperature' => $this->faker->numberBetween(5, 20),
                'cloud_coverage' => $this->faker->numberBetween(0, 100),
                'solar_radiation' => 0,
                'timestamp' => $carbon,
            ];
        });
    }

    /**
     * Create recent weather data (last 24 hours)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'timestamp' => $this->faker->dateTimeBetween('-24 hours', 'now'),
        ]);
    }

    /**
     * Create hourly sequence for a full day
     */
    public function hourlySequence(Municipality $municipality, Carbon $date): array
    {
        $snapshots = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $timestamp = $date->copy()->setHour($hour)->setMinute(0)->setSecond(0);
            [$temp, $clouds, $radiation] = $this->generateRealisticWeatherData($timestamp);
            
            $snapshots[] = [
                'municipality_id' => $municipality->id,
                'temperature' => $temp,
                'cloud_coverage' => $clouds,
                'solar_radiation' => $radiation,
                'timestamp' => $timestamp,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $snapshots;
    }
}