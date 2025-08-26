<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CooperativePlantConfig;
use App\Models\EnergyCooperative;
use App\Models\Plant;
use App\Models\Organization;

class CooperativePlantConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Creando configuraciones de plantas para cooperativas energéticas...');

        // Obtener cooperativas, plantas y organizaciones disponibles
        $cooperatives = EnergyCooperative::all();
        $plants = Plant::all();
        $organizations = Organization::all();

        if ($cooperatives->isEmpty()) {
            $this->command->error('❌ No hay cooperativas energéticas disponibles. Ejecuta EnergyCooperativeSeeder primero.');
            return;
        }

        if ($plants->isEmpty()) {
            $this->command->error('❌ No hay plantas disponibles. Ejecuta PlantSeeder primero.');
            return;
        }

        if ($organizations->isEmpty()) {
            $this->command->error('❌ No hay organizaciones disponibles. Ejecuta AppSettingSeeder primero.');
            return;
        }

        // Limpiar configuraciones existentes
        CooperativePlantConfig::query()->delete();

        // Crear configuraciones simples para cada cooperativa
        foreach ($cooperatives as $cooperative) {
            $this->createSimpleConfigs($cooperative, $plants, $organizations);
        }

        $this->command->info('✅ CooperativePlantConfigSeeder completado. Se crearon ' . CooperativePlantConfig::count() . ' configuraciones de plantas.');
    }

    /**
     * Crear configuraciones simples para una cooperativa
     */
    private function createSimpleConfigs($cooperative, $plants, $organizations): void
    {
        $this->command->info("🏭 Creando configuración para: {$cooperative->name}");

        // Seleccionar solo 1 planta para esta cooperativa
        $selectedPlant = $plants->random();
        $organization = $organizations->random();

        CooperativePlantConfig::create([
            'cooperative_id' => $cooperative->id,
            'plant_id' => $selectedPlant->id,
            'default' => true, // Siempre será la planta por defecto
            'active' => true,
            'organization_id' => $organization->id,
        ]);

        $this->command->info("  ✅ Vinculada planta: {$selectedPlant->name} (por defecto)");
    }
}
