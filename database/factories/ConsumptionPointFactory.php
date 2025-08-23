<?php

namespace Database\Factories;

use App\Models\ConsumptionPoint;
use App\Models\Customer;
use App\Models\EnergyInstallation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsumptionPoint>
 */
class ConsumptionPointFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ConsumptionPoint::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pointType = $this->faker->randomElement(array_keys(ConsumptionPoint::getPointTypes()));
        $status = $this->faker->randomElement(array_keys(ConsumptionPoint::getStatuses()));
        
        // Generar datos realistas basados en el tipo de punto
        $peakDemand = $this->getPeakDemandByType($pointType);
        $averageDemand = $peakDemand * $this->faker->randomFloat(2, 0.4, 0.8);
        $annualConsumption = $averageDemand * 24 * 365 * $this->faker->randomFloat(2, 0.6, 1.2);
        
        return [
            'point_number' => 'CP-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->generateNameByType($pointType),
            'description' => $this->faker->optional(0.8)->sentence(),
            'point_type' => $pointType,
            'status' => $status,
            'customer_id' => Customer::factory(),
            'installation_id' => $this->faker->optional(0.6)->randomElement([EnergyInstallation::factory(), null]),
            'location_address' => $this->faker->optional(0.9)->address(),
            'latitude' => $this->faker->optional(0.8)->latitude(),
            'longitude' => $this->faker->optional(0.8)->longitude(),
            'peak_demand_kw' => $peakDemand,
            'average_demand_kw' => $averageDemand,
            'annual_consumption_kwh' => $annualConsumption,
            'connection_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'disconnection_date' => $status === 'disconnected' ? $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now') : null,
            'meter_number' => $this->faker->optional(0.9)->bothify('MTR-####'),
            'meter_type' => $this->faker->optional(0.8)->randomElement(['smart', 'analog', 'digital', 'prepaid']),
            'meter_installation_date' => $this->faker->optional(0.7)->dateTimeBetween('-2 years', 'now'),
            'meter_next_calibration_date' => $this->faker->optional(0.6)->dateTimeBetween('now', '+2 years'),
            'voltage_level' => $this->faker->optional(0.8)->randomElement([110, 220, 230, 380, 400, 660]),
            'current_rating' => $this->faker->optional(0.7)->randomFloat(1, 16, 400),
            'phase_type' => $this->faker->optional(0.8)->randomElement(['single', 'three', 'split']),
            'connection_type' => $this->faker->optional(0.8)->randomElement(['grid', 'off-grid', 'hybrid', 'microgrid']),
            'service_type' => $this->faker->optional(0.7)->randomElement(['standard', 'premium', 'economic', 'time-of-use']),
            'tariff_type' => $this->faker->optional(0.7)->randomElement(['fixed', 'variable', 'time-of-use', 'demand', 'tiered']),
            'billing_frequency' => $this->faker->optional(0.8)->randomElement(['monthly', 'bimonthly', 'quarterly', 'annually']),
            'is_connected' => $status !== 'disconnected',
            'is_primary' => $this->faker->boolean(0.3),
            'notes' => $this->faker->optional(0.6)->paragraph(),
            'metadata' => $this->faker->optional(0.5)->randomElements([
                'efficiency_rating' => $this->faker->numberBetween(80, 98),
                'power_factor' => $this->faker->randomFloat(2, 0.85, 0.98),
                'load_factor' => $this->faker->randomFloat(2, 0.3, 0.8),
                'peak_hours' => $this->faker->randomElements(['08:00-12:00', '18:00-22:00'], $this->faker->numberBetween(1, 2)),
                'off_peak_hours' => $this->faker->randomElements(['00:00-06:00', '12:00-18:00'], $this->faker->numberBetween(1, 2)),
            ], $this->faker->numberBetween(2, 4), false),
            'managed_by' => User::factory(),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional(0.7)->randomElement([User::factory(), null]),
            'approved_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Generar nombre basado en el tipo de punto
     */
    private function generateNameByType(string $pointType): string
    {
        $names = [
            'residential' => [
                'Residential Point', 'Home Energy Point', 'House Consumption', 'Apartment Energy',
                'Villa Power Point', 'Condo Energy', 'Townhouse Power', 'Residential Grid'
            ],
            'commercial' => [
                'Commercial Center', 'Office Building', 'Retail Store', 'Shopping Mall',
                'Business District', 'Commercial Complex', 'Office Park', 'Retail Plaza'
            ],
            'industrial' => [
                'Industrial Plant', 'Factory Power', 'Manufacturing Center', 'Industrial Complex',
                'Production Facility', 'Industrial Zone', 'Factory Grid', 'Manufacturing Plant'
            ],
            'agricultural' => [
                'Farm Power', 'Agricultural Center', 'Rural Energy', 'Farm Grid',
                'Agricultural Zone', 'Rural Power', 'Farm Complex', 'Agricultural Facility'
            ],
            'public' => [
                'Public Building', 'Government Center', 'Public Facility', 'Municipal Building',
                'Public Service', 'Government Complex', 'Public Zone', 'Municipal Center'
            ],
            'street_lighting' => [
                'Street Lighting', 'Public Lighting', 'Road Illumination', 'Street Lights',
                'Public Street', 'Road Lighting', 'Street Grid', 'Public Illumination'
            ],
            'charging_station' => [
                'EV Charging Station', 'Electric Vehicle Point', 'Charging Hub', 'EV Station',
                'Electric Charging', 'Vehicle Charging', 'EV Hub', 'Charging Point'
            ],
            'other' => [
                'Special Purpose', 'Custom Point', 'Specialized Energy', 'Custom Grid',
                'Special Facility', 'Custom Power', 'Specialized Point', 'Custom Energy'
            ]
        ];

        return $this->faker->randomElement($names[$pointType] ?? ['Energy Point']);
    }

    /**
     * Generar demanda pico basada en el tipo de punto
     */
    private function getPeakDemandByType(string $pointType): float
    {
        $demands = [
            'residential' => $this->faker->randomFloat(1, 3, 25),
            'commercial' => $this->faker->randomFloat(1, 20, 150),
            'industrial' => $this->faker->randomFloat(1, 100, 1000),
            'agricultural' => $this->faker->randomFloat(1, 15, 100),
            'public' => $this->faker->randomFloat(1, 30, 200),
            'street_lighting' => $this->faker->randomFloat(1, 5, 50),
            'charging_station' => $this->faker->randomFloat(1, 22, 350),
            'other' => $this->faker->randomFloat(1, 10, 100)
        ];

        return $demands[$pointType] ?? 15.0;
    }

    /**
     * Estado para puntos residenciales
     */
    public function residential(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'point_type' => 'residential',
                'peak_demand_kw' => $this->faker->randomFloat(1, 3, 25),
                'average_demand_kw' => $this->faker->randomFloat(1, 1.5, 15),
                'annual_consumption_kwh' => $this->faker->randomFloat(0, 1000, 8000),
                'voltage_level' => $this->faker->randomElement([110, 220, 230]),
                'current_rating' => $this->faker->randomFloat(1, 16, 63),
                'phase_type' => 'single',
                'connection_type' => 'grid',
                'service_type' => $this->faker->randomElement(['standard', 'economic']),
                'tariff_type' => $this->faker->randomElement(['fixed', 'time-of-use']),
                'billing_frequency' => 'monthly',
            ];
        });
    }

    /**
     * Estado para puntos comerciales
     */
    public function commercial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'point_type' => 'commercial',
                'peak_demand_kw' => $this->faker->randomFloat(1, 20, 150),
                'average_demand_kw' => $this->faker->randomFloat(1, 10, 100),
                'annual_consumption_kwh' => $this->faker->randomFloat(0, 8000, 50000),
                'voltage_level' => $this->faker->randomElement([220, 230, 380, 400]),
                'current_rating' => $this->faker->randomFloat(1, 63, 400),
                'phase_type' => $this->faker->randomElement(['single', 'three']),
                'connection_type' => 'grid',
                'service_type' => $this->faker->randomElement(['standard', 'premium']),
                'tariff_type' => $this->faker->randomElement(['fixed', 'time-of-use', 'demand']),
                'billing_frequency' => $this->faker->randomElement(['monthly', 'bimonthly']),
            ];
        });
    }

    /**
     * Estado para puntos industriales
     */
    public function industrial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'point_type' => 'industrial',
                'peak_demand_kw' => $this->faker->randomFloat(1, 100, 1000),
                'average_demand_kw' => $this->faker->randomFloat(1, 60, 600),
                'annual_consumption_kwh' => $this->faker->randomFloat(0, 50000, 500000),
                'voltage_level' => $this->faker->randomElement([380, 400, 660]),
                'current_rating' => $this->faker->randomFloat(1, 200, 1000),
                'phase_type' => 'three',
                'connection_type' => $this->faker->randomElement(['grid', 'hybrid']),
                'service_type' => 'premium',
                'tariff_type' => $this->faker->randomElement(['demand', 'time-of-use', 'tiered']),
                'billing_frequency' => 'monthly',
            ];
        });
    }

    /**
     * Estado para puntos activos
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'is_connected' => true,
                'disconnection_date' => null,
            ];
        });
    }

    /**
     * Estado para puntos en mantenimiento
     */
    public function maintenance(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'maintenance',
                'is_connected' => false,
                'disconnection_date' => null,
            ];
        });
    }

    /**
     * Estado para puntos desconectados
     */
    public function disconnected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'disconnected',
                'is_connected' => false,
                'disconnection_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    /**
     * Estado para puntos planificados
     */
    public function planned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'planned',
                'is_connected' => false,
                'connection_date' => $this->faker->dateTimeBetween('now', '+6 months'),
                'disconnection_date' => null,
            ];
        });
    }

    /**
     * Estado para puntos de alto consumo
     */
    public function highConsumption(): static
    {
        return $this->state(function (array $attributes) {
            $peakDemand = $this->faker->randomFloat(1, 100, 500);
            $averageDemand = $peakDemand * $this->faker->randomFloat(2, 0.6, 0.9);
            $annualConsumption = $averageDemand * 24 * 365 * $this->faker->randomFloat(2, 0.8, 1.2);
            
            return [
                'peak_demand_kw' => $peakDemand,
                'average_demand_kw' => $averageDemand,
                'annual_consumption_kwh' => $annualConsumption,
                'status' => 'active',
            ];
        });
    }

    /**
     * Estado para puntos de bajo consumo
     */
    public function lowConsumption(): static
    {
        return $this->state(function (array $attributes) {
            $peakDemand = $this->faker->randomFloat(1, 1, 10);
            $averageDemand = $peakDemand * $this->faker->randomFloat(2, 0.3, 0.6);
            $annualConsumption = $averageDemand * 24 * 365 * $this->faker->randomFloat(2, 0.5, 0.8);
            
            return [
                'peak_demand_kw' => $peakDemand,
                'average_demand_kw' => $averageDemand,
                'annual_consumption_kwh' => $annualConsumption,
            ];
        });
    }

    /**
     * Estado para puntos con medidores inteligentes
     */
    public function smartMeter(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'meter_type' => 'smart',
                'meter_number' => 'SMART-' . $this->faker->unique()->numberBetween(1000, 9999),
                'meter_installation_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
                'meter_next_calibration_date' => $this->faker->dateTimeBetween('now', '+3 years'),
            ];
        });
    }

    /**
     * Estado para puntos con medidores analógicos
     */
    public function analogMeter(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'meter_type' => 'analog',
                'meter_number' => 'ANALOG-' . $this->faker->unique()->numberBetween(1000, 9999),
                'meter_installation_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
                'meter_next_calibration_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            ];
        });
    }

    /**
     * Estado para puntos conectados a la red
     */
    public function gridConnected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'connection_type' => 'grid',
                'is_connected' => true,
                'status' => 'active',
            ];
        });
    }

    /**
     * Estado para puntos fuera de la red
     */
    public function offGrid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'connection_type' => 'off-grid',
                'is_connected' => true,
                'status' => 'active',
            ];
        });
    }

    /**
     * Estado para puntos híbridos
     */
    public function hybrid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'connection_type' => 'hybrid',
                'is_connected' => true,
                'status' => 'active',
            ];
        });
    }

    /**
     * Estado para puntos principales
     */
    public function primary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_primary' => true,
                'status' => 'active',
            ];
        });
    }

    /**
     * Estado para puntos secundarios
     */
    public function secondary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_primary' => false,
                'status' => 'active',
            ];
        });
    }

    /**
     * Estado para puntos recientes
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'connection_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
                'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ];
        });
    }

    /**
     * Estado para puntos antiguos
     */
    public function old(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'connection_date' => $this->faker->dateTimeBetween('-10 years', '-5 years'),
                'created_at' => $this->faker->dateTimeBetween('-10 years', '-5 years'),
            ];
        });
    }

    /**
     * Estado para puntos que necesitan calibración
     */
    public function needsCalibration(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'meter_next_calibration_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'status' => 'active',
            ];
        });
    }

    /**
     * Estado para puntos con calibración próxima
     */
    public function calibrationDue(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'meter_next_calibration_date' => $this->faker->dateTimeBetween('now', '+3 months'),
                'status' => 'active',
            ];
        });
    }

    /**
     * Estado para puntos con alta eficiencia
     */
    public function highEfficiency(): static
    {
        return $this->state(function (array $attributes) {
            $peakDemand = $this->faker->randomFloat(1, 20, 100);
            $averageDemand = $peakDemand * $this->faker->randomFloat(2, 0.7, 0.9);
            
            return [
                'peak_demand_kw' => $peakDemand,
                'average_demand_kw' => $averageDemand,
                'metadata' => [
                    'efficiency_rating' => $this->faker->numberBetween(90, 98),
                    'power_factor' => $this->faker->randomFloat(2, 0.95, 0.98),
                    'load_factor' => $this->faker->randomFloat(2, 0.7, 0.9),
                ],
            ];
        });
    }

    /**
     * Estado para puntos con baja eficiencia
     */
    public function lowEfficiency(): static
    {
        return $this->state(function (array $attributes) {
            $peakDemand = $this->faker->randomFloat(1, 20, 100);
            $averageDemand = $peakDemand * $this->faker->randomFloat(2, 0.3, 0.6);
            
            return [
                'peak_demand_kw' => $peakDemand,
                'average_demand_kw' => $averageDemand,
                'metadata' => [
                    'efficiency_rating' => $this->faker->numberBetween(60, 80),
                    'power_factor' => $this->faker->randomFloat(2, 0.85, 0.92),
                    'load_factor' => $this->faker->randomFloat(2, 0.3, 0.5),
                ],
            ];
        });
    }

    /**
     * Estado para puntos con tarifa de tiempo de uso
     */
    public function timeOfUse(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tariff_type' => 'time-of-use',
                'metadata' => [
                    'peak_hours' => ['08:00-12:00', '18:00-22:00'],
                    'off_peak_hours' => ['00:00-06:00', '12:00-18:00'],
                    'shoulder_hours' => ['06:00-08:00', '22:00-00:00'],
                ],
            ];
        });
    }

    /**
     * Estado para puntos con tarifa de demanda
     */
    public function demandTariff(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tariff_type' => 'demand',
                'metadata' => [
                    'demand_charge_per_kw' => $this->faker->randomFloat(2, 5, 20),
                    'energy_charge_per_kwh' => $this->faker->randomFloat(2, 0.08, 0.15),
                ],
            ];
        });
    }

    /**
     * Estado para puntos con tarifa escalonada
     */
    public function tieredTariff(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tariff_type' => 'tiered',
                'metadata' => [
                    'tier_1_limit' => 1000,
                    'tier_1_rate' => $this->faker->randomFloat(2, 0.08, 0.12),
                    'tier_2_limit' => 2000,
                    'tier_2_rate' => $this->faker->randomFloat(2, 0.12, 0.18),
                    'tier_3_rate' => $this->faker->randomFloat(2, 0.18, 0.25),
                ],
            ];
        });
    }
}
