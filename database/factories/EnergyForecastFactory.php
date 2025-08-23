<?php

namespace Database\Factories;

use App\Models\EnergyForecast;
use App\Models\User;
use App\Models\EnergySource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyForecast>
 */
class EnergyForecastFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $forecastType = $this->faker->randomElement(array_keys(EnergyForecast::getForecastTypes()));
        $forecastHorizon = $this->faker->randomElement(array_keys(EnergyForecast::getForecastHorizons()));
        $forecastMethod = $this->faker->randomElement(array_keys(EnergyForecast::getForecastMethods()));
        $forecastStatus = $this->faker->randomElement(array_keys(EnergyForecast::getForecastStatuses()));
        $accuracyLevel = $this->faker->randomElement(array_keys(EnergyForecast::getAccuracyLevels()));
        
        $accuracyScore = $this->faker->randomFloat(2, 60, 95);
        $confidenceLevel = $this->faker->randomFloat(2, 70, 98);
        $confidenceIntervalLower = $this->faker->randomFloat(2, 50, 100);
        $confidenceIntervalUpper = $confidenceIntervalLower + $this->faker->randomFloat(2, 20, 50);
        
        $forecastStartTime = $this->faker->dateTimeBetween('-1 month', 'now');
        $forecastEndTime = $this->faker->dateTimeBetween($forecastStartTime, '+6 months');
        $generationTime = $this->faker->dateTimeBetween('-1 week', $forecastStartTime);
        $validFrom = $this->faker->dateTimeBetween($generationTime, $forecastStartTime);
        $validUntil = $this->faker->optional(0.7)->dateTimeBetween($forecastEndTime, '+1 year');
        $expiryTime = $this->faker->optional(0.8)->dateTimeBetween($forecastEndTime, '+2 years');
        
        $totalForecastedValue = $this->faker->randomFloat(2, 100, 10000);
        $baselineValue = $this->faker->randomFloat(2, $totalForecastedValue * 0.8, $totalForecastedValue * 1.2);
        $trendValue = $this->faker->randomFloat(2, -$totalForecastedValue * 0.1, $totalForecastedValue * 0.1);
        $seasonalValue = $this->faker->randomFloat(2, -$totalForecastedValue * 0.15, $totalForecastedValue * 0.15);
        $cyclicalValue = $this->faker->randomFloat(2, -$totalForecastedValue * 0.05, $totalForecastedValue * 0.05);
        $irregularValue = $this->faker->randomFloat(2, -$totalForecastedValue * 0.02, $totalForecastedValue * 0.02);
        
        return [
            'forecast_number' => 'FC-' . $this->faker->unique()->numberBetween(100000, 999999),
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'forecast_type' => $forecastType,
            'forecast_horizon' => $forecastHorizon,
            'forecast_method' => $forecastMethod,
            'forecast_status' => $forecastStatus,
            'accuracy_level' => $accuracyLevel,
            'accuracy_score' => $accuracyScore,
            'confidence_interval_lower' => $confidenceIntervalLower,
            'confidence_interval_upper' => $confidenceIntervalUpper,
            'confidence_level' => $confidenceLevel,
            'source_id' => EnergySource::factory(),
            'source_type' => $this->faker->optional(0.3)->randomElement(['App\Models\EnergySource', 'App\Models\EnergyInstallation']),
            'target_id' => $this->faker->optional(0.6)->numberBetween(1, 100),
            'target_type' => $this->faker->optional(0.6)->randomElement([
                'App\Models\EnergyInstallation',
                'App\Models\ConsumptionPoint',
                'App\Models\EnergyMeter',
                'App\Models\EnergyPool'
            ]),
            'forecast_start_time' => $forecastStartTime,
            'forecast_end_time' => $forecastEndTime,
            'generation_time' => $generationTime,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'expiry_time' => $expiryTime,
            'time_zone' => $this->faker->randomElement(['UTC', 'Europe/Madrid', 'America/New_York', 'Asia/Tokyo']),
            'time_resolution' => $this->faker->randomElement(['1h', '6h', '12h', '24h', '1d', '1w', '1m']),
            'forecast_periods' => $this->faker->numberBetween(1, 100),
            'total_forecasted_value' => $totalForecastedValue,
            'forecast_unit' => $this->faker->randomElement(['kWh', 'MWh', 'GWh', 'MW', 'GW']),
            'baseline_value' => $baselineValue,
            'trend_value' => $trendValue,
            'seasonal_value' => $seasonalValue,
            'cyclical_value' => $cyclicalValue,
            'irregular_value' => $irregularValue,
            'forecast_data' => $this->generateForecastData($forecastHorizon, $totalForecastedValue),
            'baseline_data' => $this->generateBaselineData($forecastHorizon, $baselineValue),
            'trend_data' => $this->generateTrendData($forecastHorizon, $trendValue),
            'seasonal_data' => $this->generateSeasonalData($forecastHorizon, $seasonalValue),
            'cyclical_data' => $this->generateCyclicalData($forecastHorizon, $cyclicalValue),
            'irregular_data' => $this->generateIrregularData($forecastHorizon, $irregularValue),
            'weather_data' => $this->generateWeatherData($forecastHorizon),
            'input_variables' => [
                'temperature' => $this->faker->randomFloat(2, -10, 40),
                'humidity' => $this->faker->randomFloat(2, 30, 90),
                'wind_speed' => $this->faker->randomFloat(2, 0, 25),
                'solar_radiation' => $this->faker->randomFloat(2, 0, 1000),
                'cloud_cover' => $this->faker->randomFloat(2, 0, 100),
                'precipitation' => $this->faker->randomFloat(2, 0, 50),
                'pressure' => $this->faker->randomFloat(2, 980, 1030),
                'visibility' => $this->faker->randomFloat(2, 0, 50)
            ],
            'model_parameters' => [
                'alpha' => $this->faker->randomFloat(4, 0.1, 0.9),
                'beta' => $this->faker->randomFloat(4, 0.1, 0.9),
                'gamma' => $this->faker->randomFloat(4, 0.1, 0.9),
                'seasonal_periods' => $this->faker->numberBetween(4, 52),
                'trend_smoothing' => $this->faker->randomFloat(4, 0.1, 0.9),
                'seasonal_smoothing' => $this->faker->randomFloat(4, 0.1, 0.9),
                'initial_level' => $this->faker->randomFloat(2, 50, 200),
                'initial_trend' => $this->faker->randomFloat(2, -10, 10),
                'initial_seasonals' => $this->faker->randomElements(range(0.8, 1.2), 12)
            ],
            'validation_metrics' => [
                'mae' => $this->faker->randomFloat(4, 0.01, 0.5),
                'mape' => $this->faker->randomFloat(4, 0.1, 5.0),
                'rmse' => $this->faker->randomFloat(4, 0.01, 0.8),
                'r_squared' => $this->faker->randomFloat(4, 0.7, 0.99),
                'adjusted_r_squared' => $this->faker->randomFloat(4, 0.65, 0.98),
                'aic' => $this->faker->randomFloat(2, 100, 1000),
                'bic' => $this->faker->randomFloat(2, 150, 1200),
                'durbin_watson' => $this->faker->randomFloat(4, 1.5, 2.5)
            ],
            'performance_history' => [
                'last_30_days' => [
                    'accuracy' => $this->faker->randomFloat(2, 70, 95),
                    'confidence' => $this->faker->randomFloat(2, 75, 98),
                    'forecasts_generated' => $this->faker->numberBetween(10, 100),
                    'forecasts_validated' => $this->faker->numberBetween(5, 50)
                ],
                'last_90_days' => [
                    'accuracy' => $this->faker->randomFloat(2, 65, 93),
                    'confidence' => $this->faker->randomFloat(2, 70, 96),
                    'forecasts_generated' => $this->faker->numberBetween(30, 300),
                    'forecasts_validated' => $this->faker->numberBetween(15, 150)
                ],
                'last_year' => [
                    'accuracy' => $this->faker->randomFloat(2, 60, 90),
                    'confidence' => $this->faker->randomFloat(2, 65, 94),
                    'forecasts_generated' => $this->faker->numberBetween(100, 1000),
                    'forecasts_validated' => $this->faker->numberBetween(50, 500)
                ]
            ],
            'tags' => $this->faker->randomElements([
                'High Accuracy', 'Machine Learning', 'Statistical', 'Physical Model',
                'Renewable Energy', 'Grid Stability', 'Peak Hours', 'Off-Peak',
                'Weather Dependent', 'Seasonal', 'Long Term', 'Short Term',
                'Real-time', 'Historical Data', 'Expert Judgment', 'Hybrid Model',
                'Solar', 'Wind', 'Hydro', 'Biomass', 'Geothermal', 'Nuclear',
                'Demand Response', 'Load Forecasting', 'Price Prediction'
            ], $this->faker->numberBetween(3, 8)),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional(0.7)->randomElement([User::factory(), null]),
            'approved_at' => $this->faker->optional(0.7)->dateTimeBetween($validFrom, 'now'),
            'validated_by' => $this->faker->optional(0.5)->randomElement([User::factory(), null]),
            'validated_at' => $this->faker->optional(0.5)->dateTimeBetween($validFrom, 'now'),
            'notes' => $this->faker->optional(0.8)->paragraph(),
        ];
    }

    /**
     * Generate forecast data based on horizon and total value
     */
    private function generateForecastData(string $horizon, float $totalValue): array
    {
        $periods = $this->getPeriodsForHorizon($horizon);
        $data = [];
        
        for ($i = 0; $i < $periods; $i++) {
            $baseValue = $totalValue / $periods;
            $variation = $this->faker->randomFloat(4, 0.8, 1.2);
            $data["period_{$i}"] = [
                'timestamp' => now()->addHours($i)->toISOString(),
                'value' => round($baseValue * $variation, 2),
                'confidence' => $this->faker->randomFloat(2, 70, 98),
                'quality_score' => $this->faker->randomFloat(2, 0.7, 1.0)
            ];
        }
        
        return $data;
    }

    /**
     * Generate baseline data
     */
    private function generateBaselineData(string $horizon, float $baselineValue): array
    {
        $periods = $this->getPeriodsForHorizon($horizon);
        $data = [];
        
        for ($i = 0; $i < $periods; $i++) {
            $variation = $this->faker->randomFloat(4, 0.9, 1.1);
            $data["period_{$i}"] = [
                'timestamp' => now()->addHours($i)->toISOString(),
                'value' => round($baselineValue * $variation, 2),
                'source' => 'historical_average'
            ];
        }
        
        return $data;
    }

    /**
     * Generate trend data
     */
    private function generateTrendData(string $horizon, float $trendValue): array
    {
        $periods = $this->getPeriodsForHorizon($horizon);
        $data = [];
        
        for ($i = 0; $i < $periods; $i++) {
            $trendComponent = ($trendValue / $periods) * $i;
            $data["period_{$i}"] = [
                'timestamp' => now()->addHours($i)->toISOString(),
                'value' => round($trendComponent, 2),
                'direction' => $trendValue > 0 ? 'increasing' : 'decreasing'
            ];
        }
        
        return $data;
    }

    /**
     * Generate seasonal data
     */
    private function generateSeasonalData(string $horizon, float $seasonalValue): array
    {
        $periods = $this->getPeriodsForHorizon($horizon);
        $data = [];
        
        for ($i = 0; $i < $periods; $i++) {
            $seasonalComponent = $seasonalValue * sin(2 * pi() * $i / 24); // Daily cycle
            $data["period_{$i}"] = [
                'timestamp' => now()->addHours($i)->toISOString(),
                'value' => round($seasonalComponent, 2),
                'cycle' => 'daily'
            ];
        }
        
        return $data;
    }

    /**
     * Generate cyclical data
     */
    private function generateCyclicalData(string $horizon, float $cyclicalValue): array
    {
        $periods = $this->getPeriodsForHorizon($horizon);
        $data = [];
        
        for ($i = 0; $i < $periods; $i++) {
            $cyclicalComponent = $cyclicalValue * cos(2 * pi() * $i / 168); // Weekly cycle
            $data["period_{$i}"] = [
                'timestamp' => now()->addHours($i)->toISOString(),
                'value' => round($cyclicalComponent, 2),
                'cycle' => 'weekly'
            ];
        }
        
        return $data;
    }

    /**
     * Generate irregular data
     */
    private function generateIrregularData(string $horizon, float $irregularValue): array
    {
        $periods = $this->getPeriodsForHorizon($horizon);
        $data = [];
        
        for ($i = 0; $i < $periods; $i++) {
            $irregularComponent = $this->faker->randomFloat(4, -$irregularValue, $irregularValue);
            $data["period_{$i}"] = [
                'timestamp' => now()->addHours($i)->toISOString(),
                'value' => round($irregularComponent, 2),
                'type' => 'random_noise'
            ];
        }
        
        return $data;
    }

    /**
     * Generate weather data
     */
    private function generateWeatherData(string $horizon): array
    {
        $periods = $this->getPeriodsForHorizon($horizon);
        $data = [];
        
        for ($i = 0; $i < $periods; $i++) {
            $data["period_{$i}"] = [
                'timestamp' => now()->addHours($i)->toISOString(),
                'temperature' => $this->faker->randomFloat(2, -10, 40),
                'humidity' => $this->faker->randomFloat(2, 30, 90),
                'wind_speed' => $this->faker->randomFloat(2, 0, 25),
                'solar_radiation' => $this->faker->randomFloat(2, 0, 1000),
                'cloud_cover' => $this->faker->randomFloat(2, 0, 100),
                'precipitation' => $this->faker->randomFloat(2, 0, 50)
            ];
        }
        
        return $data;
    }

    /**
     * Get number of periods based on horizon
     */
    private function getPeriodsForHorizon(string $horizon): int
    {
        return match($horizon) {
            'hourly' => 24,
            'daily' => 7,
            'weekly' => 4,
            'monthly' => 12,
            'quarterly' => 4,
            'yearly' => 1,
            'long_term' => 5,
            default => 24
        };
    }

    /**
     * Indicate that the forecast is for demand.
     */
    public function demand(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_type' => 'demand',
        ]);
    }

    /**
     * Indicate that the forecast is for generation.
     */
    public function generation(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_type' => 'generation',
        ]);
    }

    /**
     * Indicate that the forecast is for consumption.
     */
    public function consumption(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_type' => 'consumption',
        ]);
    }

    /**
     * Indicate that the forecast is for price.
     */
    public function price(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_type' => 'price',
        ]);
    }

    /**
     * Indicate that the forecast is for weather.
     */
    public function weather(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_type' => 'weather',
        ]);
    }

    /**
     * Indicate that the forecast is hourly.
     */
    public function hourly(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_horizon' => 'hourly',
        ]);
    }

    /**
     * Indicate that the forecast is daily.
     */
    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_horizon' => 'daily',
        ]);
    }

    /**
     * Indicate that the forecast is weekly.
     */
    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_horizon' => 'weekly',
        ]);
    }

    /**
     * Indicate that the forecast is monthly.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_horizon' => 'monthly',
        ]);
    }

    /**
     * Indicate that the forecast is quarterly.
     */
    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_horizon' => 'quarterly',
        ]);
    }

    /**
     * Indicate that the forecast is yearly.
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_horizon' => 'yearly',
        ]);
    }

    /**
     * Indicate that the forecast is long term.
     */
    public function longTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_horizon' => 'long_term',
        ]);
    }

    /**
     * Indicate that the forecast uses statistical method.
     */
    public function statistical(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_method' => 'statistical',
        ]);
    }

    /**
     * Indicate that the forecast uses machine learning method.
     */
    public function machineLearning(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_method' => 'machine_learning',
        ]);
    }

    /**
     * Indicate that the forecast uses physical model method.
     */
    public function physicalModel(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_method' => 'physical_model',
        ]);
    }

    /**
     * Indicate that the forecast uses hybrid method.
     */
    public function hybrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_method' => 'hybrid',
        ]);
    }

    /**
     * Indicate that the forecast uses expert judgment method.
     */
    public function expertJudgment(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_method' => 'expert_judgment',
        ]);
    }

    /**
     * Indicate that the forecast is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_status' => 'active',
        ]);
    }

    /**
     * Indicate that the forecast is validated.
     */
    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_status' => 'validated',
        ]);
    }

    /**
     * Indicate that the forecast is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_status' => 'expired',
            'expiry_time' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Indicate that the forecast has high accuracy.
     */
    public function highAccuracy(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_level' => 'high',
            'accuracy_score' => $this->faker->randomFloat(2, 80, 95),
        ]);
    }

    /**
     * Indicate that the forecast has very high accuracy.
     */
    public function veryHighAccuracy(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_level' => 'very_high',
            'accuracy_score' => $this->faker->randomFloat(2, 90, 99),
        ]);
    }

    /**
     * Indicate that the forecast has medium accuracy.
     */
    public function mediumAccuracy(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_level' => 'medium',
            'accuracy_score' => $this->faker->randomFloat(2, 70, 79),
        ]);
    }

    /**
     * Indicate that the forecast has low accuracy.
     */
    public function lowAccuracy(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_level' => 'low',
            'accuracy_score' => $this->faker->randomFloat(2, 60, 69),
        ]);
    }

    /**
     * Indicate that the forecast is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the forecast is pending approval.
     */
    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the forecast is validated.
     */
    public function validatedStatus(): static
    {
        return $this->state(fn (array $attributes) => [
            'validated_by' => User::factory(),
            'validated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the forecast is pending validation.
     */
    public function pendingValidation(): static
    {
        return $this->state(fn (array $attributes) => [
            'validated_by' => null,
            'validated_at' => null,
        ]);
    }

    /**
     * Indicate that the forecast is expiring soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_time' => $this->faker->dateTimeBetween('now', '+24 hours'),
        ]);
    }

    /**
     * Indicate that the forecast has no source.
     */
    public function noSource(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_id' => null,
            'source_type' => null,
        ]);
    }

    /**
     * Indicate that the forecast has no target.
     */
    public function noTarget(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_id' => null,
            'target_type' => null,
        ]);
    }

    /**
     * Indicate that the forecast has specific tags.
     */
    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $tags,
        ]);
    }
}
