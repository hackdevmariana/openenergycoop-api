<?php

namespace Database\Factories;

use App\Models\EnergyMeter;
use App\Models\Customer;
use App\Models\EnergyInstallation;
use App\Models\ConsumptionPoint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyMeter>
 */
class EnergyMeterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EnergyMeter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $meterType = $this->faker->randomElement(array_keys(EnergyMeter::getMeterTypes()));
        $status = $this->faker->randomElement(array_keys(EnergyMeter::getStatuses()));
        $category = $this->faker->randomElement(array_keys(EnergyMeter::getMeterCategories()));
        
        $isSmartMeter = $meterType === 'smart_meter';
        $installationDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $commissioningDate = $this->faker->optional(0.8)->dateTimeBetween($installationDate, '+1 month');
        $nextCalibrationDate = $this->faker->optional(0.9)->dateTimeBetween('now', '+2 years');
        $warrantyExpiryDate = $this->faker->optional(0.7)->dateTimeBetween($installationDate, '+5 years');
        $lastMaintenanceDate = $this->faker->optional(0.6)->dateTimeBetween($installationDate, 'now');
        $nextMaintenanceDate = $this->faker->optional(0.8)->dateTimeBetween('now', '+1 year');

        return [
            'meter_number' => 'MTR-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->words(2, true) . ' Meter',
            'description' => $this->faker->optional(0.8)->sentence(),
            'meter_type' => $meterType,
            'status' => $status,
            'meter_category' => $category,
            'manufacturer' => $this->faker->optional(0.8)->randomElement(['Siemens', 'ABB', 'Schneider Electric', 'Honeywell', 'Eaton', 'GE', 'Schneider', 'Rockwell']),
            'model' => $this->faker->optional(0.7)->bothify('??-####'),
            'serial_number' => 'SN' . $this->faker->unique()->numberBetween(100000, 999999),
            'installation_id' => EnergyInstallation::factory(),
            'consumption_point_id' => ConsumptionPoint::factory(),
            'customer_id' => Customer::factory(),
            'installation_date' => $installationDate,
            'commissioning_date' => $commissioningDate,
            'next_calibration_date' => $nextCalibrationDate,
            'voltage_rating' => $this->faker->optional(0.8)->randomFloat(1, 110, 480),
            'current_rating' => $this->faker->optional(0.8)->randomFloat(1, 16, 400),
            'accuracy_class' => $this->faker->optional(0.9)->randomFloat(1, 0.1, 2.0),
            'measurement_range_min' => $this->faker->optional(0.7)->randomFloat(2, 0, 100),
            'measurement_range_max' => $this->faker->optional(0.7)->randomFloat(2, 100, 1000),
            'is_smart_meter' => $isSmartMeter,
            'has_remote_reading' => $isSmartMeter ? $this->faker->boolean(0.9) : $this->faker->boolean(0.3),
            'has_two_way_communication' => $isSmartMeter ? $this->faker->boolean(0.8) : $this->faker->boolean(0.2),
            'communication_protocol' => $isSmartMeter ? $this->faker->randomElement(['Modbus', 'DNP3', 'IEC 61850', 'OPC UA', 'MQTT']) : null,
            'firmware_version' => $this->faker->optional(0.6)->bothify('v#.#.#'),
            'hardware_version' => $this->faker->optional(0.5)->bothify('HW-#.#'),
            'warranty_expiry_date' => $warrantyExpiryDate,
            'last_maintenance_date' => $lastMaintenanceDate,
            'next_maintenance_date' => $nextMaintenanceDate,
            'notes' => $this->faker->optional(0.4)->paragraph(),
            'metadata' => $this->faker->optional(0.3)->randomElements([
                'protocol' => 'Modbus',
                'baud_rate' => 9600,
                'parity' => 'None',
                'stop_bits' => 1,
                'timeout' => 1000,
                'retries' => 3,
                'polling_interval' => 60,
                'alarm_thresholds' => [
                    'voltage_min' => 200,
                    'voltage_max' => 250,
                    'current_max' => 80,
                    'temperature_max' => 60
                ]
            ], $this->faker->numberBetween(2, 5)),
            'managed_by' => User::factory(),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional(0.6)->randomElement([User::factory(), null]),
            'approved_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the meter is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the meter is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the meter is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
            'next_maintenance_date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ]);
    }

    /**
     * Indicate that the meter is faulty.
     */
    public function faulty(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'faulty',
        ]);
    }

    /**
     * Indicate that the meter is a smart meter.
     */
    public function smartMeter(): static
    {
        return $this->state(fn (array $attributes) => [
            'meter_type' => 'smart_meter',
            'is_smart_meter' => true,
            'has_remote_reading' => true,
            'has_two_way_communication' => true,
            'communication_protocol' => $this->faker->randomElement(['Modbus', 'DNP3', 'IEC 61850', 'OPC UA', 'MQTT']),
        ]);
    }

    /**
     * Indicate that the meter is a digital meter.
     */
    public function digitalMeter(): static
    {
        return $this->state(fn (array $attributes) => [
            'meter_type' => 'digital_meter',
            'is_smart_meter' => false,
            'has_remote_reading' => $this->faker->boolean(0.5),
            'has_two_way_communication' => false,
        ]);
    }

    /**
     * Indicate that the meter is an analog meter.
     */
    public function analogMeter(): static
    {
        return $this->state(fn (array $attributes) => [
            'meter_type' => 'analog_meter',
            'is_smart_meter' => false,
            'has_remote_reading' => false,
            'has_two_way_communication' => false,
        ]);
    }

    /**
     * Indicate that the meter is for electricity.
     */
    public function electricity(): static
    {
        return $this->state(fn (array $attributes) => [
            'meter_category' => 'electricity',
            'voltage_rating' => $this->faker->randomFloat(1, 110, 480),
            'current_rating' => $this->faker->randomFloat(1, 16, 400),
        ]);
    }

    /**
     * Indicate that the meter is for water.
     */
    public function water(): static
    {
        return $this->state(fn (array $attributes) => [
            'meter_category' => 'water',
            'voltage_rating' => null,
            'current_rating' => null,
        ]);
    }

    /**
     * Indicate that the meter is for gas.
     */
    public function gas(): static
    {
        return $this->state(fn (array $attributes) => [
            'meter_category' => 'gas',
            'voltage_rating' => null,
            'current_rating' => null,
        ]);
    }

    /**
     * Indicate that the meter has high accuracy.
     */
    public function highAccuracy(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_class' => $this->faker->randomFloat(1, 0.1, 0.5),
        ]);
    }

    /**
     * Indicate that the meter has low accuracy.
     */
    public function lowAccuracy(): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_class' => $this->faker->randomFloat(1, 1.0, 2.0),
        ]);
    }

    /**
     * Indicate that the meter needs calibration.
     */
    public function needsCalibration(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_calibration_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the meter needs maintenance.
     */
    public function needsMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the meter is under warranty.
     */
    public function underWarranty(): static
    {
        return $this->state(fn (array $attributes) => [
            'warranty_expiry_date' => $this->faker->dateTimeBetween('now', '+2 years'),
        ]);
    }

    /**
     * Indicate that the meter is out of warranty.
     */
    public function outOfWarranty(): static
    {
        return $this->state(fn (array $attributes) => [
            'warranty_expiry_date' => $this->faker->dateTimeBetween('-2 years', '-1 month'),
        ]);
    }

    /**
     * Indicate that the meter is commissioned.
     */
    public function commissioned(): static
    {
        return $this->state(fn (array $attributes) => [
            'commissioning_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the meter is not commissioned.
     */
    public function notCommissioned(): static
    {
        return $this->state(fn (array $attributes) => [
            'commissioning_date' => null,
        ]);
    }

    /**
     * Indicate that the meter has remote reading capability.
     */
    public function remoteReading(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_remote_reading' => true,
        ]);
    }

    /**
     * Indicate that the meter has two-way communication.
     */
    public function twoWayCommunication(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_two_way_communication' => true,
        ]);
    }

    /**
     * Indicate that the meter is recently installed.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the meter is old.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_date' => $this->faker->dateTimeBetween('-10 years', '-5 years'),
        ]);
    }

    /**
     * Indicate that the meter has high voltage rating.
     */
    public function highVoltage(): static
    {
        return $this->state(fn (array $attributes) => [
            'voltage_rating' => $this->faker->randomFloat(1, 400, 1000),
        ]);
    }

    /**
     * Indicate that the meter has low voltage rating.
     */
    public function lowVoltage(): static
    {
        return $this->state(fn (array $attributes) => [
            'voltage_rating' => $this->faker->randomFloat(1, 110, 240),
        ]);
    }

    /**
     * Indicate that the meter has high current rating.
     */
    public function highCurrent(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_rating' => $this->faker->randomFloat(1, 200, 1000),
        ]);
    }

    /**
     * Indicate that the meter has low current rating.
     */
    public function lowCurrent(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_rating' => $this->faker->randomFloat(1, 16, 63),
        ]);
    }

    /**
     * Indicate that the meter is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the meter is not approved.
     */
    public function notApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the meter has specific manufacturer.
     */
    public function manufacturer(string $manufacturer): static
    {
        return $this->state(fn (array $attributes) => [
            'manufacturer' => $manufacturer,
        ]);
    }

    /**
     * Indicate that the meter has specific model.
     */
    public function model(string $model): static
    {
        return $this->state(fn (array $attributes) => [
            'model' => $model,
        ]);
    }

    /**
     * Indicate that the meter has specific communication protocol.
     */
    public function protocol(string $protocol): static
    {
        return $this->state(fn (array $attributes) => [
            'communication_protocol' => $protocol,
            'is_smart_meter' => true,
        ]);
    }

    /**
     * Indicate that the meter has specific firmware version.
     */
    public function firmware(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'firmware_version' => $version,
        ]);
    }

    /**
     * Indicate that the meter has specific hardware version.
     */
    public function hardware(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'hardware_version' => $version,
        ]);
    }

    /**
     * Indicate that the meter has specific measurement range.
     */
    public function measurementRange(float $min, float $max): static
    {
        return $this->state(fn (array $attributes) => [
            'measurement_range_min' => $min,
            'measurement_range_max' => $max,
        ]);
    }

    /**
     * Indicate that the meter has specific accuracy class.
     */
    public function accuracyClass(float $accuracy): static
    {
        return $this->state(fn (array $attributes) => [
            'accuracy_class' => $accuracy,
        ]);
    }

    /**
     * Indicate that the meter has specific voltage rating.
     */
    public function voltageRating(float $voltage): static
    {
        return $this->state(fn (array $attributes) => [
            'voltage_rating' => $voltage,
        ]);
    }

    /**
     * Indicate that the meter has specific current rating.
     */
    public function currentRating(float $current): static
    {
        return $this->state(fn (array $attributes) => [
            'current_rating' => $current,
        ]);
    }

    /**
     * Indicate that the meter has specific installation date.
     */
    public function installedOn(\DateTime $date): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_date' => $date,
        ]);
    }

    /**
     * Indicate that the meter has specific calibration date.
     */
    public function calibrationDueOn(\DateTime $date): static
    {
        return $this->state(fn (array $attributes) => [
            'next_calibration_date' => $date,
        ]);
    }

    /**
     * Indicate that the meter has specific warranty expiry date.
     */
    public function warrantyExpiresOn(\DateTime $date): static
    {
        return $this->state(fn (array $attributes) => [
            'warranty_expiry_date' => $date,
        ]);
    }

    /**
     * Indicate that the meter has specific maintenance date.
     */
    public function maintenanceDueOn(\DateTime $date): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_date' => $date,
        ]);
    }

    /**
     * Indicate that the meter has specific metadata.
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }

    /**
     * Indicate that the meter has specific notes.
     */
    public function withNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $notes,
        ]);
    }

    /**
     * Indicate that the meter is for a specific customer.
     */
    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }

    /**
     * Indicate that the meter is for a specific installation.
     */
    public function forInstallation(EnergyInstallation $installation): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_id' => $installation->id,
        ]);
    }

    /**
     * Indicate that the meter is for a specific consumption point.
     */
    public function forConsumptionPoint(ConsumptionPoint $consumptionPoint): static
    {
        return $this->state(fn (array $attributes) => [
            'consumption_point_id' => $consumptionPoint->id,
        ]);
    }

    /**
     * Indicate that the meter is managed by a specific user.
     */
    public function managedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'managed_by' => $user->id,
        ]);
    }

    /**
     * Indicate that the meter is created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }

    /**
     * Indicate that the meter is approved by a specific user.
     */
    public function approvedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }
}
