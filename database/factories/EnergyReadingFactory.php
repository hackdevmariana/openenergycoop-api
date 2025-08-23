<?php

namespace Database\Factories;

use App\Models\EnergyReading;
use App\Models\EnergyMeter;
use App\Models\EnergyInstallation;
use App\Models\ConsumptionPoint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyReading>
 */
class EnergyReadingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EnergyReading::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $readingTypes = array_keys(EnergyReading::getReadingTypes());
        $readingSources = array_keys(EnergyReading::getReadingSources());
        $readingStatuses = array_keys(EnergyReading::getReadingStatuses());
        
        $readingType = $this->faker->randomElement($readingTypes);
        $readingTimestamp = $this->faker->dateTimeBetween('-1 year', 'now');
        
        // Calcular valores basados en el tipo de lectura
        $readingValue = $this->calculateReadingValue($readingType);
        $previousReadingValue = $readingType === 'cumulative' ? $readingValue - $this->faker->randomFloat(4, 1, 50) : null;
        $consumptionValue = $readingType === 'cumulative' && $previousReadingValue ? $readingValue - $previousReadingValue : null;
        
        return [
            'reading_number' => 'RDG-' . $this->faker->unique()->numberBetween(1000, 9999),
            'meter_id' => EnergyMeter::factory(),
            'installation_id' => $this->faker->optional(0.7)->randomElement([EnergyInstallation::factory(), null]),
            'consumption_point_id' => $this->faker->optional(0.6)->randomElement([ConsumptionPoint::factory(), null]),
            'customer_id' => User::factory(),
            'reading_type' => $readingType,
            'reading_source' => $this->faker->randomElement($readingSources),
            'reading_status' => $this->faker->randomElement($readingStatuses),
            'reading_timestamp' => $readingTimestamp,
            'reading_period' => $this->faker->optional()->randomElement(['hourly', 'daily', 'weekly', 'monthly']),
            'reading_value' => $readingValue,
            'reading_unit' => $this->faker->randomElement(['kWh', 'kW', 'kVA', 'A', 'V', 'Hz']),
            'previous_reading_value' => $previousReadingValue,
            'consumption_value' => $consumptionValue,
            'consumption_unit' => $this->faker->optional()->randomElement(['kWh', 'kW', 'kVA']),
            'demand_value' => $this->faker->optional(0.8)->randomFloat(4, 0, 1000),
            'demand_unit' => $this->faker->optional()->randomElement(['kW', 'kVA']),
            'power_factor' => $this->faker->optional(0.7)->randomFloat(2, -1, 1),
            'voltage_value' => $this->faker->optional(0.8)->randomFloat(2, 110, 480),
            'voltage_unit' => $this->faker->optional()->randomElement(['V', 'kV']),
            'current_value' => $this->faker->optional(0.8)->randomFloat(2, 0, 1000),
            'current_unit' => $this->faker->optional()->randomElement(['A', 'kA']),
            'frequency_value' => $this->faker->optional(0.6)->randomFloat(2, 50, 60),
            'frequency_unit' => $this->faker->optional()->randomElement(['Hz']),
            'temperature' => $this->faker->optional(0.7)->randomFloat(2, -20, 60),
            'temperature_unit' => $this->faker->optional()->randomElement(['°C', '°F']),
            'humidity' => $this->faker->optional(0.6)->randomFloat(2, 0, 100),
            'humidity_unit' => $this->faker->optional()->randomElement(['%']),
            'quality_score' => $this->faker->optional(0.8)->randomFloat(2, 0, 100),
            'quality_notes' => $this->faker->optional(0.6)->sentence(),
            'validation_notes' => $this->faker->optional(0.4)->sentence(),
            'correction_notes' => $this->faker->optional(0.3)->sentence(),
            'raw_data' => $this->faker->optional(0.7)->randomElements([
                'timestamp' => $readingTimestamp->format('Y-m-d H:i:s'),
                'raw_value' => $readingValue,
                'checksum' => $this->faker->md5(),
                'device_id' => $this->faker->uuid(),
                'protocol_version' => $this->faker->randomElement(['1.0', '2.0', '2.1'])
            ], $this->faker->numberBetween(3, 5)),
            'processed_data' => $this->faker->optional(0.6)->randomElements([
                'calibrated_value' => $readingValue,
                'correction_factor' => $this->faker->randomFloat(4, 0.95, 1.05),
                'uncertainty' => $this->faker->randomFloat(4, 0, 0.1),
                'processing_algorithm' => $this->faker->randomElement(['linear', 'polynomial', 'exponential'])
            ], $this->faker->numberBetween(2, 4)),
            'alarms' => $this->faker->optional(0.3)->randomElements([
                'high_voltage' => $this->faker->boolean(20),
                'low_voltage' => $this->faker->boolean(15),
                'overcurrent' => $this->faker->boolean(10),
                'power_failure' => $this->faker->boolean(5),
                'communication_error' => $this->faker->boolean(8)
            ], $this->faker->numberBetween(1, 3)),
            'events' => $this->faker->optional(0.5)->randomElements([
                'maintenance_start' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'calibration_due' => $this->faker->dateTimeBetween('now', '+6 months'),
                'battery_low' => $this->faker->boolean(30),
                'sensor_fault' => $this->faker->boolean(15)
            ], $this->faker->numberBetween(1, 4)),
            'tags' => $this->faker->optional(0.6)->randomElements([
                'critical',
                'monitoring',
                'billing',
                'quality_check',
                'backup',
                'primary',
                'secondary'
            ], $this->faker->numberBetween(1, 4)),
            'read_by' => User::factory(),
            'validated_by' => $this->faker->optional(0.6)->randomElement([User::factory(), null]),
            'validated_at' => $this->faker->optional(0.6)->dateTimeBetween($readingTimestamp, 'now'),
            'corrected_by' => $this->faker->optional(0.2)->randomElement([User::factory(), null]),
            'corrected_at' => $this->faker->optional(0.2)->dateTimeBetween($readingTimestamp, 'now'),
            'notes' => $this->faker->optional(0.7)->sentence(),
        ];
    }

    /**
     * Calcular el valor de lectura basado en el tipo
     */
    private function calculateReadingValue(string $readingType): float
    {
        return match($readingType) {
            'instantaneous' => $this->faker->randomFloat(4, 0, 1000),
            'cumulative' => $this->faker->randomFloat(4, 1000, 100000),
            'demand' => $this->faker->randomFloat(4, 0, 500),
            'energy' => $this->faker->randomFloat(4, 0, 10000),
            'power' => $this->faker->randomFloat(4, 0, 1000),
            default => $this->faker->randomFloat(4, 0, 1000),
        };
    }

    /**
     * Estado para lecturas instantáneas
     */
    public function instantaneous(): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_type' => 'instantaneous',
            'reading_value' => $this->faker->randomFloat(4, 0, 1000),
            'previous_reading_value' => null,
            'consumption_value' => null,
        ]);
    }

    /**
     * Estado para lecturas acumulativas
     */
    public function cumulative(): static
    {
        $readingValue = $this->faker->randomFloat(4, 1000, 100000);
        $previousValue = $readingValue - $this->faker->randomFloat(4, 1, 50);
        
        return $this->state(fn (array $attributes) => [
            'reading_type' => 'cumulative',
            'reading_value' => $readingValue,
            'previous_reading_value' => $previousValue,
            'consumption_value' => $readingValue - $previousValue,
        ]);
    }

    /**
     * Estado para lecturas de demanda
     */
    public function demand(): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_type' => 'demand',
            'reading_value' => $this->faker->randomFloat(4, 0, 500),
            'demand_value' => $this->faker->randomFloat(4, 0, 500),
        ]);
    }

    /**
     * Estado para lecturas automáticas
     */
    public function automatic(): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_source' => 'automatic',
            'quality_score' => $this->faker->randomFloat(2, 85, 100),
        ]);
    }

    /**
     * Estado para lecturas manuales
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_source' => 'manual',
            'quality_score' => $this->faker->randomFloat(2, 70, 95),
        ]);
    }

    /**
     * Estado para lecturas válidas
     */
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_status' => 'valid',
            'validated_by' => User::factory(),
            'validated_at' => $this->faker->dateTimeBetween($attributes['reading_timestamp'], 'now'),
        ]);
    }

    /**
     * Estado para lecturas inválidas
     */
    public function invalid(): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_status' => 'invalid',
            'quality_score' => $this->faker->randomFloat(2, 0, 70),
            'validation_notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Estado para lecturas corregidas
     */
    public function corrected(): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_status' => 'corrected',
            'corrected_by' => User::factory(),
            'corrected_at' => $this->faker->dateTimeBetween($attributes['reading_timestamp'], 'now'),
            'correction_notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Estado para lecturas de alta calidad
     */
    public function highQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'quality_score' => $this->faker->randomFloat(2, 90, 100),
            'reading_source' => 'automatic',
        ]);
    }

    /**
     * Estado para lecturas de baja calidad
     */
    public function lowQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'quality_score' => $this->faker->randomFloat(2, 0, 70),
            'reading_source' => 'manual',
        ]);
    }

    /**
     * Estado para lecturas con datos eléctricos completos
     */
    public function withElectricalData(): static
    {
        return $this->state(fn (array $attributes) => [
            'power_factor' => $this->faker->randomFloat(2, -1, 1),
            'voltage_value' => $this->faker->randomFloat(2, 110, 480),
            'voltage_unit' => 'V',
            'current_value' => $this->faker->randomFloat(2, 0, 1000),
            'current_unit' => 'A',
            'frequency_value' => $this->faker->randomFloat(2, 50, 60),
            'frequency_unit' => 'Hz',
        ]);
    }

    /**
     * Estado para lecturas con datos ambientales
     */
    public function withEnvironmentalData(): static
    {
        return $this->state(fn (array $attributes) => [
            'temperature' => $this->faker->randomFloat(2, -20, 60),
            'temperature_unit' => '°C',
            'humidity' => $this->faker->randomFloat(2, 0, 100),
            'humidity_unit' => '%',
        ]);
    }

    /**
     * Estado para lecturas con alarmas
     */
    public function withAlarms(): static
    {
        return $this->state(fn (array $attributes) => [
            'alarms' => [
                'high_voltage' => true,
                'overcurrent' => $this->faker->boolean(30),
                'power_failure' => $this->faker->boolean(10),
                'communication_error' => $this->faker->boolean(20),
            ],
        ]);
    }

    /**
     * Estado para lecturas recientes (últimas 24 horas)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_timestamp' => $this->faker->dateTimeBetween('-24 hours', 'now'),
        ]);
    }

    /**
     * Estado para lecturas históricas (más de 1 mes)
     */
    public function historical(): static
    {
        return $this->state(fn (array $attributes) => [
            'reading_timestamp' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }

    /**
     * Estado para lecturas de medidor específico
     */
    public function forMeter(EnergyMeter $meter): static
    {
        return $this->state(fn (array $attributes) => [
            'meter_id' => $meter->id,
        ]);
    }

    /**
     * Estado para lecturas de instalación específica
     */
    public function forInstallation(EnergyInstallation $installation): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_id' => $installation->id,
        ]);
    }

    /**
     * Estado para lecturas de punto de consumo específico
     */
    public function forConsumptionPoint(ConsumptionPoint $point): static
    {
        return $this->state(fn (array $attributes) => [
            'consumption_point_id' => $point->id,
        ]);
    }

    /**
     * Estado para lecturas de cliente específico
     */
    public function forCustomer(User $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }

    /**
     * Estado para lecturas con datos JSON completos
     */
    public function withFullData(): static
    {
        return $this->state(fn (array $attributes) => [
            'raw_data' => [
                'timestamp' => $attributes['reading_timestamp']->format('Y-m-d H:i:s'),
                'raw_value' => $attributes['reading_value'],
                'checksum' => $this->faker->md5(),
                'device_id' => $this->faker->uuid(),
                'protocol_version' => '2.1',
                'firmware_version' => '1.2.3',
                'hardware_version' => '2.0',
                'serial_number' => $this->faker->bothify('SN-####-????'),
                'manufacturer' => $this->faker->randomElement(['Schneider', 'Siemens', 'ABB', 'GE']),
                'model' => $this->faker->bothify('MTR-####'),
            ],
            'processed_data' => [
                'calibrated_value' => $attributes['reading_value'],
                'correction_factor' => $this->faker->randomFloat(4, 0.98, 1.02),
                'uncertainty' => $this->faker->randomFloat(4, 0, 0.05),
                'processing_algorithm' => 'polynomial',
                'calibration_date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                'next_calibration_date' => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
                'calibration_certificate' => $this->faker->bothify('CAL-####-????'),
            ],
            'tags' => [
                'critical',
                'monitoring',
                'billing',
                'quality_check',
                'primary',
                'automated',
                'verified',
            ],
        ]);
    }
}
