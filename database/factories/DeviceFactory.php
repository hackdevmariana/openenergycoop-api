<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Device::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'type' => $this->faker->randomElement([
                Device::TYPE_SMART_METER,
                Device::TYPE_BATTERY,
                Device::TYPE_EV_CHARGER,
                Device::TYPE_SOLAR_PANEL,
                Device::TYPE_WIND_TURBINE,
                Device::TYPE_HEAT_PUMP,
                Device::TYPE_THERMOSTAT,
                Device::TYPE_SMART_PLUG,
                Device::TYPE_ENERGY_MONITOR,
                Device::TYPE_GRID_CONNECTION,
                Device::TYPE_OTHER
            ]),
            'user_id' => User::factory(),
            'consumption_point_id' => null,
            'api_endpoint' => $this->faker->optional()->url(),
            'api_credentials' => $this->faker->optional()->randomElements([
                'username' => $this->faker->userName(),
                'password' => $this->faker->password(),
                'api_key' => $this->faker->uuid(),
                'token' => $this->faker->sha1()
            ], 2),
            'device_config' => $this->faker->optional()->randomElements([
                'polling_interval' => $this->faker->randomElement([30, 60, 300, 600]),
                'timezone' => $this->faker->timezone(),
                'language' => $this->faker->randomElement(['es', 'en', 'fr', 'de']),
                'units' => $this->faker->randomElement(['metric', 'imperial']),
                'alerts_enabled' => $this->faker->boolean(),
                'data_retention_days' => $this->faker->randomElement([30, 60, 90, 365])
            ], 3),
            'active' => $this->faker->boolean(80), // 80% de probabilidad de estar activo
            'model' => $this->faker->optional()->randomElement([
                'SmartMeter Pro X1',
                'BatteryPack 5000',
                'EV Charger Plus',
                'SolarPanel Max',
                'WindTurbine GT',
                'HeatPump Eco',
                'Thermostat Smart',
                'SmartPlug WiFi',
                'EnergyMonitor Pro',
                'GridConnector 1000'
            ]),
            'manufacturer' => $this->faker->optional()->randomElement([
                'EnergyTech',
                'GreenPower',
                'SmartEnergy',
                'EcoSolutions',
                'RenewableTech',
                'PowerSystems',
                'SmartHome',
                'IoT Energy',
                'GridTech',
                'SolarCorp'
            ]),
            'serial_number' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{8}'),
            'firmware_version' => $this->faker->optional()->randomElement([
                '1.0.0', '1.2.3', '2.0.1', '2.1.0', '3.0.0'
            ]),
            'last_communication' => $this->faker->optional()->dateTimeBetween('-1 hour', 'now'),
            'capabilities' => $this->faker->optional()->randomElements([
                'energy_monitoring',
                'remote_control',
                'data_logging',
                'alerts',
                'scheduling',
                'integration',
                'analytics',
                'maintenance'
            ], $this->faker->numberBetween(2, 5)),
            'location' => $this->faker->optional()->randomElement([
                'Sala de máquinas',
                'Techo',
                'Jardín',
                'Garaje',
                'Sótano',
                'Ático',
                'Exterior',
                'Interior',
                'Sala de control',
                'Almacén'
            ]),
            'notes' => $this->faker->optional()->sentence()
        ];
    }

    /**
     * Indica que el dispositivo está activo.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Indica que el dispositivo está inactivo.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Dispositivo de tipo contador inteligente.
     */
    public function smartMeter(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_SMART_METER,
            'name' => 'Contador Inteligente ' . $this->faker->randomNumber(4),
            'capabilities' => ['energy_monitoring', 'data_logging', 'integration'],
            'model' => 'SmartMeter Pro X1',
            'manufacturer' => 'EnergyTech'
        ]);
    }

    /**
     * Dispositivo de tipo batería.
     */
    public function battery(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_BATTERY,
            'name' => 'Batería ' . $this->faker->randomNumber(4),
            'capabilities' => ['energy_monitoring', 'remote_control', 'alerts'],
            'model' => 'BatteryPack 5000',
            'manufacturer' => 'GreenPower'
        ]);
    }

    /**
     * Dispositivo de tipo cargador EV.
     */
    public function evCharger(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_EV_CHARGER,
            'name' => 'Cargador EV ' . $this->faker->randomNumber(4),
            'capabilities' => ['energy_monitoring', 'remote_control', 'scheduling'],
            'model' => 'EV Charger Plus',
            'manufacturer' => 'SmartEnergy'
        ]);
    }

    /**
     * Dispositivo de tipo panel solar.
     */
    public function solarPanel(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_SOLAR_PANEL,
            'name' => 'Panel Solar ' . $this->faker->randomNumber(4),
            'capabilities' => ['energy_monitoring', 'analytics', 'maintenance'],
            'model' => 'SolarPanel Max',
            'manufacturer' => 'SolarCorp'
        ]);
    }

    /**
     * Dispositivo de tipo turbina eólica.
     */
    public function windTurbine(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_WIND_TURBINE,
            'name' => 'Turbina Eólica ' . $this->faker->randomNumber(4),
            'capabilities' => ['energy_monitoring', 'analytics', 'maintenance'],
            'model' => 'WindTurbine GT',
            'manufacturer' => 'RenewableTech'
        ]);
    }

    /**
     * Dispositivo de tipo bomba de calor.
     */
    public function heatPump(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_HEAT_PUMP,
            'name' => 'Bomba de Calor ' . $this->faker->randomNumber(4),
            'capabilities' => ['energy_monitoring', 'remote_control', 'scheduling'],
            'model' => 'HeatPump Eco',
            'manufacturer' => 'EcoSolutions'
        ]);
    }

    /**
     * Dispositivo de tipo termostato.
     */
    public function thermostat(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_THERMOSTAT,
            'name' => 'Termostato ' . $this->faker->randomNumber(4),
            'capabilities' => ['remote_control', 'scheduling', 'integration'],
            'model' => 'Thermostat Smart',
            'manufacturer' => 'SmartHome'
        ]);
    }

    /**
     * Dispositivo de tipo enchufe inteligente.
     */
    public function smartPlug(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_SMART_PLUG,
            'name' => 'Enchufe Inteligente ' . $this->faker->randomNumber(4),
            'capabilities' => ['energy_monitoring', 'remote_control', 'scheduling'],
            'model' => 'SmartPlug WiFi',
            'manufacturer' => 'IoT Energy'
        ]);
    }

    /**
     * Dispositivo de tipo monitor de energía.
     */
    public function energyMonitor(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_ENERGY_MONITOR,
            'name' => 'Monitor de Energía ' . $this->faker->randomNumber(4),
            'capabilities' => ['energy_monitoring', 'data_logging', 'analytics'],
            'model' => 'EnergyMonitor Pro',
            'manufacturer' => 'PowerSystems'
        ]);
    }

    /**
     * Dispositivo de tipo conexión a red.
     */
    public function gridConnection(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Device::TYPE_GRID_CONNECTION,
            'name' => 'Conexión a Red ' . $this->faker->randomNumber(4),
            'capabilities' => ['energy_monitoring', 'integration', 'alerts'],
            'model' => 'GridConnector 1000',
            'manufacturer' => 'GridTech'
        ]);
    }

    /**
     * Dispositivo en línea (comunicación reciente).
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
            'last_communication' => $this->faker->dateTimeBetween('-5 minutes', 'now'),
        ]);
    }

    /**
     * Dispositivo offline (sin comunicación reciente).
     */
    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
            'last_communication' => $this->faker->dateTimeBetween('-1 day', '-1 hour'),
        ]);
    }

    /**
     * Dispositivo con capacidades avanzadas.
     */
    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'capabilities' => [
                'energy_monitoring',
                'remote_control',
                'data_logging',
                'alerts',
                'scheduling',
                'integration',
                'analytics',
                'maintenance'
            ],
            'device_config' => [
                'polling_interval' => 30,
                'timezone' => 'Europe/Madrid',
                'language' => 'es',
                'units' => 'metric',
                'alerts_enabled' => true,
                'data_retention_days' => 365
            ]
        ]);
    }

    /**
     * Dispositivo básico con capacidades mínimas.
     */
    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'capabilities' => ['energy_monitoring'],
            'device_config' => [
                'polling_interval' => 300,
                'timezone' => 'UTC',
                'language' => 'en',
                'units' => 'metric',
                'alerts_enabled' => false,
                'data_retention_days' => 30
            ]
        ]);
    }

    /**
     * Dispositivo con configuración de producción.
     */
    public function production(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
            'capabilities' => [
                'energy_monitoring',
                'remote_control',
                'data_logging',
                'alerts',
                'analytics'
            ],
            'device_config' => [
                'polling_interval' => 60,
                'timezone' => 'Europe/Madrid',
                'language' => 'es',
                'units' => 'metric',
                'alerts_enabled' => true,
                'data_retention_days' => 90
            ]
        ]);
    }

    /**
     * Dispositivo con configuración de desarrollo.
     */
    public function development(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
            'capabilities' => ['energy_monitoring', 'data_logging'],
            'device_config' => [
                'polling_interval' => 300,
                'timezone' => 'UTC',
                'language' => 'en',
                'units' => 'metric',
                'alerts_enabled' => false,
                'data_retention_days' => 7
            ]
        ]);
    }
}
