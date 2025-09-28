<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EnergyRightPreSale;
use App\Models\User;
use App\Models\EnergyInstallation;
use Carbon\Carbon;

class EnergyRightPreSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $installations = EnergyInstallation::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Ejecutando UserSeeder primero...');
            $this->call(UserSeeder::class);
            $users = User::all();
        }

        if ($installations->isEmpty()) {
            $this->command->warn('⚠️ No hay instalaciones disponibles. Ejecutando EnergyInstallationSeeder primero...');
            $this->call(EnergyInstallationSeeder::class);
            $installations = EnergyInstallation::all();
        }

        $preSales = [
            // Preventas con instalación específica
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => $installations->random()->id,
                'zone_name' => null,
                'postal_code' => null,
                'kwh_per_month_reserved' => 500.00,
                'price_per_kwh' => 0.0850,
                'status' => 'confirmed',
                'signed_at' => Carbon::now()->subDays(15),
                'expires_at' => Carbon::now()->addMonths(12),
                'notes' => 'Preventa confirmada para instalación solar residencial',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => $installations->random()->id,
                'zone_name' => null,
                'postal_code' => null,
                'kwh_per_month_reserved' => 1200.00,
                'price_per_kwh' => 0.0820,
                'status' => 'pending',
                'signed_at' => null,
                'expires_at' => Carbon::now()->addDays(30),
                'notes' => 'Preventa pendiente para instalación industrial',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => $installations->random()->id,
                'zone_name' => null,
                'postal_code' => null,
                'kwh_per_month_reserved' => 800.00,
                'price_per_kwh' => 0.0880,
                'status' => 'confirmed',
                'signed_at' => Carbon::now()->subDays(30),
                'expires_at' => Carbon::now()->addMonths(18),
                'notes' => 'Preventa confirmada para instalación comercial',
            ],

            // Preventas por zona (sin instalación específica)
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Centro Madrid',
                'postal_code' => '28001',
                'kwh_per_month_reserved' => 300.00,
                'price_per_kwh' => 0.0900,
                'status' => 'pending',
                'signed_at' => null,
                'expires_at' => Carbon::now()->addDays(45),
                'notes' => 'Preventa por zona - proyecto futuro en centro de Madrid',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Eixample',
                'postal_code' => '08001',
                'kwh_per_month_reserved' => 750.00,
                'price_per_kwh' => 0.0875,
                'status' => 'confirmed',
                'signed_at' => Carbon::now()->subDays(20),
                'expires_at' => Carbon::now()->addMonths(15),
                'notes' => 'Preventa confirmada para zona Eixample - Barcelona',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Centro Zaragoza',
                'postal_code' => '50001',
                'kwh_per_month_reserved' => 400.00,
                'price_per_kwh' => 0.0830,
                'status' => 'cancelled',
                'signed_at' => null,
                'expires_at' => Carbon::now()->subDays(10),
                'notes' => 'Preventa cancelada por cambio de planes del cliente',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Abando',
                'postal_code' => '48001',
                'kwh_per_month_reserved' => 600.00,
                'price_per_kwh' => 0.0860,
                'status' => 'pending',
                'signed_at' => null,
                'expires_at' => Carbon::now()->addDays(20),
                'notes' => 'Preventa pendiente para zona Abando - Bilbao',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Centro Málaga',
                'postal_code' => '29001',
                'kwh_per_month_reserved' => 900.00,
                'price_per_kwh' => 0.0840,
                'status' => 'confirmed',
                'signed_at' => Carbon::now()->subDays(10),
                'expires_at' => Carbon::now()->addMonths(24),
                'notes' => 'Preventa confirmada para proyecto en Málaga centro',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Centro Murcia',
                'postal_code' => '30001',
                'kwh_per_month_reserved' => 350.00,
                'price_per_kwh' => 0.0890,
                'status' => 'pending',
                'signed_at' => null,
                'expires_at' => Carbon::now()->addDays(15),
                'notes' => 'Preventa pendiente para zona centro de Murcia',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Centro Palma',
                'postal_code' => '07001',
                'kwh_per_month_reserved' => 1100.00,
                'price_per_kwh' => 0.0815,
                'status' => 'confirmed',
                'signed_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addMonths(20),
                'notes' => 'Preventa confirmada para proyecto en Palma de Mallorca',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Vegueta',
                'postal_code' => '35001',
                'kwh_per_month_reserved' => 250.00,
                'price_per_kwh' => 0.0920,
                'status' => 'cancelled',
                'signed_at' => null,
                'expires_at' => Carbon::now()->subDays(5),
                'notes' => 'Preventa cancelada por el cliente',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Centro Santa Cruz',
                'postal_code' => '38001',
                'kwh_per_month_reserved' => 700.00,
                'price_per_kwh' => 0.0855,
                'status' => 'pending',
                'signed_at' => null,
                'expires_at' => Carbon::now()->addDays(25),
                'notes' => 'Preventa pendiente para Santa Cruz de Tenerife',
            ],
            [
                'user_id' => $users->random()->id,
                'energy_installation_id' => null,
                'zone_name' => 'Centro Histórico',
                'postal_code' => '41001',
                'kwh_per_month_reserved' => 450.00,
                'price_per_kwh' => 0.0885,
                'status' => 'confirmed',
                'signed_at' => Carbon::now()->subDays(8),
                'expires_at' => Carbon::now()->addMonths(16),
                'notes' => 'Preventa confirmada para centro histórico de Sevilla',
            ],
        ];

        $this->command->info('🌱 Creando preventas de derechos energéticos...');

        foreach ($preSales as $preSaleData) {
            // Asegurar que el usuario existe
            if (!$users->contains('id', $preSaleData['user_id'])) {
                $preSaleData['user_id'] = $users->random()->id;
            }

            // Asegurar que la instalación existe si se especifica
            if ($preSaleData['energy_installation_id'] && !$installations->contains('id', $preSaleData['energy_installation_id'])) {
                $preSaleData['energy_installation_id'] = $installations->random()->id;
            }

            EnergyRightPreSale::create($preSaleData);

            $locationInfo = $preSaleData['energy_installation_id'] 
                ? "Instalación ID: {$preSaleData['energy_installation_id']}"
                : "Zona: {$preSaleData['zone_name']} ({$preSaleData['postal_code']})";

            $this->command->line("✅ Preventa '{$preSaleData['status']}' - {$preSaleData['kwh_per_month_reserved']} kWh/mes - {$locationInfo}");
        }

        // Mostrar resumen
        $totalPreSales = EnergyRightPreSale::count();
        $byStatus = EnergyRightPreSale::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $withInstallation = EnergyRightPreSale::withInstallation()->count();
        $withoutInstallation = EnergyRightPreSale::withoutInstallation()->count();
        $activePreSales = EnergyRightPreSale::active()->count();
        $expiredPreSales = EnergyRightPreSale::expired()->count();
        $expiringSoon = EnergyRightPreSale::expiringSoon()->count();

        $totalKwhReserved = EnergyRightPreSale::confirmed()->sum('kwh_per_month_reserved');
        $totalValueReserved = EnergyRightPreSale::confirmed()->get()->sum('total_value');
        $averagePrice = EnergyRightPreSale::confirmed()->avg('price_per_kwh');

        $this->command->info("🎉 Seeder completado:");
        $this->command->line("   📊 Total de preventas: {$totalPreSales}");
        $this->command->line("   ⏳ Pendientes: " . ($byStatus['pending'] ?? 0));
        $this->command->line("   ✅ Confirmadas: " . ($byStatus['confirmed'] ?? 0));
        $this->command->line("   ❌ Canceladas: " . ($byStatus['cancelled'] ?? 0));
        $this->command->line("   🏭 Con instalación: {$withInstallation}");
        $this->command->line("   🌍 Por zona: {$withoutInstallation}");
        $this->command->line("   🟢 Activas: {$activePreSales}");
        $this->command->line("   ⏰ Expiradas: {$expiredPreSales}");
        $this->command->line("   ⚠️ Expiran pronto: {$expiringSoon}");
        $this->command->line("   ⚡ Total kWh reservados: " . number_format($totalKwhReserved, 2) . " kWh/mes");
        $this->command->line("   💰 Valor total reservado: " . number_format($totalValueReserved, 2) . " €/mes");
        $this->command->line("   📈 Precio promedio: " . number_format($averagePrice, 4) . " €/kWh");
    }
}