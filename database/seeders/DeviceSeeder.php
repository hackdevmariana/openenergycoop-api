<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\User;
use App\Models\ConsumptionPoint;
use Carbon\Carbon;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📱 Creando dispositivos...');

        $users = User::all();
        $consumptionPoints = ConsumptionPoint::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar dispositivos existentes
        Device::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("⚡ Puntos de consumo disponibles: {$consumptionPoints->count()}");

        // Crear diferentes tipos de dispositivos
        $this->createSmartMeters($users, $consumptionPoints);
        $this->createBatteries($users, $consumptionPoints);
        $this->createEVChargers($users, $consumptionPoints);
        $this->createSolarPanels($users, $consumptionPoints);
        $this->createWindTurbines($users, $consumptionPoints);
        $this->createHeatPumps($users, $consumptionPoints);
        $this->createThermostats($users, $consumptionPoints);
        $this->createSmartPlugs($users, $consumptionPoints);
        $this->createEnergyMonitors($users, $consumptionPoints);
        $this->createGridConnections($users, $consumptionPoints);
        $this->createOtherDevices($users, $consumptionPoints);

        $this->command->info('✅ DeviceSeeder completado. Se crearon ' . Device::count() . ' dispositivos.');
    }

    private function createDevice($user, $consumptionPoint, $factoryMethod, $name, $serialPrefix, $location, $additionalData = []): void
    {
        Device::factory()
            ->$factoryMethod()
            ->production()
            ->online()
            ->state([
                'api_credentials' => null,
                'api_endpoint' => null
            ])
            ->create(array_merge([
                'user_id' => $user->id,
                'consumption_point_id' => $consumptionPoint ? $consumptionPoint->id : null,
                'name' => $name,
                'serial_number' => $serialPrefix,
                'location' => $location,
                'last_communication' => Carbon::now()->subMinutes(rand(1, 60)),
                'created_at' => Carbon::now()->subDays(rand(30, 365)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ], $additionalData));
    }

    private function createSmartMeters($users, $consumptionPoints): void
    {
        $this->command->info('🔌 Creando contadores inteligentes...');

        // Crear 2-3 contadores por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(2, 3); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                $this->createDevice(
                    $user,
                    $consumptionPoint,
                    'smartMeter',
                    'Contador Principal ' . ($i + 1),
                    'SM-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    fake()->randomElement(['Entrada principal', 'Cuadro eléctrico', 'Sala de máquinas'])
                );
            }
        }

        $this->command->info("   ✅ Contadores inteligentes creados");
    }

    private function createBatteries($users, $consumptionPoints): void
    {
        $this->command->info('🔋 Creando baterías...');

        // Crear 1-2 baterías por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(1, 2); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                $this->createDevice(
                    $user,
                    $consumptionPoint,
                    'battery',
                    'Batería de Almacenamiento ' . ($i + 1),
                    'BAT-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    fake()->randomElement(['Sala de baterías', 'Garaje', 'Sótano', 'Exterior'])
                );
            }
        }

        $this->command->info("   ✅ Baterías creadas");
    }

    private function createEVChargers($users, $consumptionPoints): void
    {
        $this->command->info('⚡ Creando cargadores EV...');

        // Crear 1-2 cargadores por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(1, 2); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                $this->createDevice(
                    $user,
                    $consumptionPoint,
                    'evCharger',
                    'Cargador EV ' . ($i + 1),
                    'EV-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    fake()->randomElement(['Garaje', 'Exterior', 'Parking', 'Entrada'])
                );
            }
        }

        $this->command->info("   ✅ Cargadores EV creados");
    }

    private function createSolarPanels($users, $consumptionPoints): void
    {
        $this->command->info('☀️ Creando paneles solares...');

        // Crear 4-8 paneles por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(4, 8); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                $this->createDevice(
                    $user,
                    $consumptionPoint,
                    'solarPanel',
                    'Panel Solar ' . ($i + 1),
                    'SP-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    fake()->randomElement(['Techo', 'Terraza', 'Jardín', 'Exterior'])
                );
            }
        }

        $this->command->info("   ✅ Paneles solares creados");
    }

    private function createWindTurbines($users, $consumptionPoints): void
    {
        $this->command->info('💨 Creando turbinas eólicas...');

        // Crear 1-3 turbinas por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(1, 3); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                $this->createDevice(
                    $user,
                    $consumptionPoint,
                    'windTurbine',
                    'Turbina Eólica ' . ($i + 1),
                    'WT-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    fake()->randomElement(['Exterior', 'Campo', 'Monte', 'Jardín'])
                );
            }
        }

        $this->command->info("   ✅ Turbinas eólicas creadas");
    }

    private function createHeatPumps($users, $consumptionPoints): void
    {
        $this->command->info('🔥 Creando bombas de calor...');

        // Crear 1-2 bombas por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(1, 2); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                $this->createDevice(
                    $user,
                    $consumptionPoint,
                    'heatPump',
                    'Bomba de Calor ' . ($i + 1),
                    'HP-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    fake()->randomElement(['Sala de máquinas', 'Exterior', 'Garaje', 'Sótano'])
                );
            }
        }

        $this->command->info("   ✅ Bombas de calor creadas");
    }

    private function createThermostats($users, $consumptionPoints): void
    {
        $this->command->info('🌡️ Creando termostatos...');

        // Crear 2-4 termostatos por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(2, 4); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                $this->createDevice(
                    $user,
                    $consumptionPoint,
                    'thermostat',
                    'Termostato ' . ($i + 1),
                    'TH-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    fake()->randomElement(['Salón', 'Dormitorio', 'Cocina', 'Oficina'])
                );
            }
        }

        $this->command->info("   ✅ Termostatos creados");
    }

    private function createSmartPlugs($users, $consumptionPoints): void
    {
        $this->command->info('🔌 Creando enchufes inteligentes...');

        // Crear 3-6 enchufes por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(3, 6); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                $this->createDevice(
                    $user,
                    $consumptionPoint,
                    'smartPlug',
                    'Enchufe Inteligente ' . ($i + 1),
                    'SP-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    fake()->randomElement(['Salón', 'Cocina', 'Dormitorio', 'Oficina', 'Garaje'])
                );
            }
        }

        $this->command->info("   ✅ Enchufes inteligentes creados");
    }

    private function createEnergyMonitors($users, $consumptionPoints): void
    {
        $this->command->info('📊 Creando monitores de energía...');

        // Crear 1-2 monitores por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(1, 2); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                $this->createDevice(
                    $user,
                    $consumptionPoint,
                    'energyMonitor',
                    'Monitor de Energía ' . ($i + 1),
                    'EM-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    fake()->randomElement(['Sala de control', 'Oficina', 'Cuadro eléctrico', 'Sala de máquinas'])
                );
            }
        }

        $this->command->info("   ✅ Monitores de energía creados");
    }

    private function createGridConnections($users, $consumptionPoints): void
    {
        $this->command->info('🔗 Creando conexiones a red...');

        // Crear 1 conexión por usuario
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
            
            $this->createDevice(
                $user,
                $consumptionPoint,
                'gridConnection',
                'Conexión a Red Principal',
                'GC-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-01',
                fake()->randomElement(['Cuadro principal', 'Entrada de red', 'Sala de máquinas'])
            );
        }

        $this->command->info("   ✅ Conexiones a red creadas");
    }

    private function createOtherDevices($users, $consumptionPoints): void
    {
        $this->command->info('🔧 Creando otros dispositivos...');

        // Crear algunos dispositivos adicionales
        foreach ($users as $user) {
            $userConsumptionPoints = $consumptionPoints->where('user_id', $user->id);
            
            for ($i = 0; $i < rand(1, 3); $i++) {
                $consumptionPoint = $userConsumptionPoints->isNotEmpty() ? $userConsumptionPoints->random() : null;
                
                Device::factory()
                    ->production()
                    ->online()
                    ->state([
                        'api_credentials' => null,
                        'api_endpoint' => null,
                        'type' => Device::TYPE_OTHER,
                        'name' => 'Dispositivo ' . fake()->words(2, true),
                        'serial_number' => 'OT-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                        'model' => fake()->randomElement(['Generic Device', 'Custom Sensor', 'IoT Module']),
                        'manufacturer' => fake()->randomElement(['Generic', 'Custom', 'IoT Solutions']),
                        'location' => fake()->randomElement(['Sala de máquinas', 'Oficina', 'Exterior', 'Interior']),
                        'user_id' => $user->id,
                        'consumption_point_id' => $consumptionPoint ? $consumptionPoint->id : null,
                        'last_communication' => Carbon::now()->subMinutes(rand(1, 120)),
                        'created_at' => Carbon::now()->subDays(rand(30, 180)),
                        'updated_at' => Carbon::now()->subDays(rand(0, 30))
                    ])
                    ->create();
            }
        }

        $this->command->info("   ✅ Otros dispositivos creados");
    }
}
