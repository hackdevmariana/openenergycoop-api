<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EnergyInterest;
use App\Models\User;
use App\Models\EnergyZoneSummary;
use Carbon\Carbon;

class EnergyInterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $zones = EnergyZoneSummary::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Ejecutando UserSeeder primero...');
            $this->call(UserSeeder::class);
            $users = User::all();
        }

        if ($zones->isEmpty()) {
            $this->command->warn('⚠️ No hay zonas energéticas disponibles. Ejecutando EnergyZoneSummarySeeder primero...');
            $this->call(EnergyZoneSummarySeeder::class);
            $zones = EnergyZoneSummary::all();
        }

        $energyInterests = [
            // Intereses de usuarios registrados
            [
                'user_id' => $users->random()->id,
                'zone_name' => 'Centro Madrid',
                'postal_code' => '28001',
                'type' => 'consumer',
                'estimated_production_kwh_day' => null,
                'requested_kwh_day' => 1500.00,
                'contact_name' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'notes' => 'Interés en consumir energía renovable para vivienda',
                'status' => 'approved',
            ],
            [
                'user_id' => $users->random()->id,
                'zone_name' => 'Chamartín',
                'postal_code' => '28036',
                'type' => 'producer',
                'estimated_production_kwh_day' => 2500.00,
                'requested_kwh_day' => null,
                'contact_name' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'notes' => 'Instalación solar en azotea de edificio',
                'status' => 'active',
            ],
            [
                'user_id' => $users->random()->id,
                'zone_name' => 'Eixample',
                'postal_code' => '08001',
                'type' => 'mixed',
                'estimated_production_kwh_day' => 1800.00,
                'requested_kwh_day' => 1200.00,
                'contact_name' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'notes' => 'Vivienda con paneles solares y consumo adicional',
                'status' => 'approved',
            ],

            // Intereses de usuarios no registrados
            [
                'user_id' => null,
                'zone_name' => 'Centro Zaragoza',
                'postal_code' => '50001',
                'type' => 'consumer',
                'estimated_production_kwh_day' => null,
                'requested_kwh_day' => 2000.00,
                'contact_name' => 'María García López',
                'contact_email' => 'maria.garcia@email.com',
                'contact_phone' => '+34 976 123 456',
                'notes' => 'Interés en energía renovable para comunidad de vecinos',
                'status' => 'pending',
            ],
            [
                'user_id' => null,
                'zone_name' => 'Cuarte de Huerva',
                'postal_code' => '50410',
                'type' => 'producer',
                'estimated_production_kwh_day' => 3200.00,
                'requested_kwh_day' => null,
                'contact_name' => 'Carlos Ruiz Martín',
                'contact_email' => 'carlos.ruiz@empresa.com',
                'contact_phone' => '+34 976 789 012',
                'notes' => 'Empresa con tejado solar disponible',
                'status' => 'approved',
            ],
            [
                'user_id' => null,
                'zone_name' => 'Abando',
                'postal_code' => '48001',
                'type' => 'mixed',
                'estimated_production_kwh_day' => 1500.00,
                'requested_kwh_day' => 1000.00,
                'contact_name' => 'Ana Fernández Silva',
                'contact_email' => 'ana.fernandez@hotmail.com',
                'contact_phone' => '+34 944 345 678',
                'notes' => 'Vivienda unifamiliar con paneles y consumo adicional',
                'status' => 'active',
            ],
            [
                'user_id' => null,
                'zone_name' => 'Centro Málaga',
                'postal_code' => '29001',
                'type' => 'consumer',
                'estimated_production_kwh_day' => null,
                'requested_kwh_day' => 1200.00,
                'contact_name' => 'José Antonio Pérez',
                'contact_email' => 'jose.perez@gmail.com',
                'contact_phone' => '+34 952 567 890',
                'notes' => 'Consumo para restaurante en centro',
                'status' => 'pending',
            ],
            [
                'user_id' => null,
                'zone_name' => 'Centro Murcia',
                'postal_code' => '30001',
                'type' => 'producer',
                'estimated_production_kwh_day' => 4000.00,
                'requested_kwh_day' => null,
                'contact_name' => 'Isabel Moreno Torres',
                'contact_email' => 'isabel.moreno@yahoo.es',
                'contact_phone' => '+34 968 901 234',
                'notes' => 'Instalación solar en nave industrial',
                'status' => 'rejected',
            ],
        ];

        $this->command->info('🌱 Creando intereses energéticos...');

        foreach ($energyInterests as $interestData) {
            // Asegurar que la zona existe
            $zone = $zones->firstWhere('zone_name', $interestData['zone_name']);
            if (!$zone) {
                $this->command->warn("⚠️ Zona '{$interestData['zone_name']}' no encontrada. Saltando interés...");
                continue;
            }

            // Asegurar que el usuario existe si se especifica
            if ($interestData['user_id'] && !$users->contains('id', $interestData['user_id'])) {
                $interestData['user_id'] = $users->random()->id;
            }

            EnergyInterest::create($interestData);

            $contactInfo = $interestData['user_id'] 
                ? "Usuario ID: {$interestData['user_id']}"
                : "Contacto: {$interestData['contact_name']} ({$interestData['contact_email']})";

            $this->command->line("✅ Interés '{$interestData['type']}' en '{$interestData['zone_name']}' - {$contactInfo}");
        }

        // Mostrar resumen
        $totalInterests = EnergyInterest::count();
        $byType = EnergyInterest::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
        $byStatus = EnergyInterest::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $this->command->info("🎉 Seeder completado:");
        $this->command->line("   📊 Total de intereses: {$totalInterests}");
        $this->command->line("   👥 Con usuario: " . EnergyInterest::withUser()->count());
        $this->command->line("   📧 Sin usuario: " . EnergyInterest::withoutUser()->count());
        $this->command->line("   🔋 Consumidores: " . ($byType['consumer'] ?? 0));
        $this->command->line("   ⚡ Productores: " . ($byType['producer'] ?? 0));
        $this->command->line("   🔄 Mixtos: " . ($byType['mixed'] ?? 0));
        $this->command->line("   ⏳ Pendientes: " . ($byStatus['pending'] ?? 0));
        $this->command->line("   ✅ Aprobados: " . ($byStatus['approved'] ?? 0));
        $this->command->line("   🟢 Activos: " . ($byStatus['active'] ?? 0));
        $this->command->line("   ❌ Rechazados: " . ($byStatus['rejected'] ?? 0));
    }
}