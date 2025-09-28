<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EnergyZoneSummary;
use App\Models\Municipality;
use Carbon\Carbon;

class EnergyZoneSummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $municipalities = Municipality::all();
        
        if ($municipalities->isEmpty()) {
            $this->command->warn('⚠️ No hay municipios disponibles. Ejecutando MunicipalitySeeder primero...');
            $this->call(MunicipalitySeeder::class);
            $municipalities = Municipality::all();
        }

        $energyZones = [
            // Madrid
            [
                'zone_name' => 'Centro Madrid',
                'postal_code' => '28001',
                'municipality_name' => 'Madrid',
                'estimated_production_kwh_day' => 15000.00,
                'reserved_kwh_day' => 8500.00,
                'requested_kwh_day' => 12000.00,
                'status' => 'naranja',
                'notes' => 'Zona centro con alta demanda y producción solar en edificios públicos'
            ],
            [
                'zone_name' => 'Chamartín',
                'postal_code' => '28036',
                'municipality_name' => 'Madrid',
                'estimated_production_kwh_day' => 8500.00,
                'reserved_kwh_day' => 4200.00,
                'requested_kwh_day' => 6800.00,
                'status' => 'verde',
                'notes' => 'Zona residencial con instalaciones solares en viviendas'
            ],
            [
                'zone_name' => 'Alcalá de Henares Centro',
                'postal_code' => '28801',
                'municipality_name' => 'Alcalá de Henares',
                'estimated_production_kwh_day' => 12000.00,
                'reserved_kwh_day' => 11000.00,
                'requested_kwh_day' => 13500.00,
                'status' => 'rojo',
                'notes' => 'Zona histórica con alta demanda y producción limitada'
            ],
            [
                'zone_name' => 'Getafe Industrial',
                'postal_code' => '28901',
                'municipality_name' => 'Getafe',
                'estimated_production_kwh_day' => 25000.00,
                'reserved_kwh_day' => 18000.00,
                'requested_kwh_day' => 22000.00,
                'status' => 'naranja',
                'notes' => 'Zona industrial con gran potencial de energía renovable'
            ],

            // Barcelona
            [
                'zone_name' => 'Eixample',
                'postal_code' => '08001',
                'municipality_name' => 'Barcelona',
                'estimated_production_kwh_day' => 18000.00,
                'reserved_kwh_day' => 12000.00,
                'requested_kwh_day' => 16000.00,
                'status' => 'verde',
                'notes' => 'Distrito con instalaciones solares en azoteas'
            ],
            [
                'zone_name' => 'Hospitalet Centro',
                'postal_code' => '08901',
                'municipality_name' => 'Hospitalet de Llobregat',
                'estimated_production_kwh_day' => 9500.00,
                'reserved_kwh_day' => 8800.00,
                'requested_kwh_day' => 10200.00,
                'status' => 'rojo',
                'notes' => 'Zona densamente poblada con alta demanda'
            ],

            // Valencia
            [
                'zone_name' => 'Ciutat Vella',
                'postal_code' => '46001',
                'municipality_name' => 'Valencia',
                'estimated_production_kwh_day' => 8000.00,
                'reserved_kwh_day' => 4500.00,
                'requested_kwh_day' => 7200.00,
                'status' => 'verde',
                'notes' => 'Centro histórico con proyectos de eficiencia energética'
            ],
            [
                'zone_name' => 'Paterna Industrial',
                'postal_code' => '46980',
                'municipality_name' => 'Paterna',
                'estimated_production_kwh_day' => 22000.00,
                'reserved_kwh_day' => 15000.00,
                'requested_kwh_day' => 19000.00,
                'status' => 'verde',
                'notes' => 'Polígono industrial con instalaciones fotovoltaicas'
            ],

            // Sevilla
            [
                'zone_name' => 'Centro Histórico',
                'postal_code' => '41001',
                'municipality_name' => 'Sevilla',
                'estimated_production_kwh_day' => 7500.00,
                'reserved_kwh_day' => 6800.00,
                'requested_kwh_day' => 8200.00,
                'status' => 'rojo',
                'notes' => 'Zona histórica con limitaciones para instalaciones solares'
            ],
            [
                'zone_name' => 'Dos Hermanas',
                'postal_code' => '41700',
                'municipality_name' => 'Dos Hermanas',
                'estimated_production_kwh_day' => 16000.00,
                'reserved_kwh_day' => 9800.00,
                'requested_kwh_day' => 12000.00,
                'status' => 'verde',
                'notes' => 'Municipio con gran potencial solar y eólico'
            ],

            // Zaragoza
            [
                'zone_name' => 'Centro Zaragoza',
                'postal_code' => '50001',
                'municipality_name' => 'Zaragoza',
                'estimated_production_kwh_day' => 11000.00,
                'reserved_kwh_day' => 7500.00,
                'requested_kwh_day' => 9500.00,
                'status' => 'verde',
                'notes' => 'Capital aragonesa con proyectos de energía renovable'
            ],
            [
                'zone_name' => 'Cuarte de Huerva',
                'postal_code' => '50410',
                'municipality_name' => 'Cuarte de Huerva',
                'estimated_production_kwh_day' => 14000.00,
                'reserved_kwh_day' => 8500.00,
                'requested_kwh_day' => 11000.00,
                'status' => 'verde',
                'notes' => 'Municipio con instalaciones solares comunitarias'
            ],

            // Bilbao
            [
                'zone_name' => 'Abando',
                'postal_code' => '48001',
                'municipality_name' => 'Bilbao',
                'estimated_production_kwh_day' => 9000.00,
                'reserved_kwh_day' => 6200.00,
                'requested_kwh_day' => 7800.00,
                'status' => 'verde',
                'notes' => 'Distrito financiero con proyectos de eficiencia energética'
            ],

            // Málaga
            [
                'zone_name' => 'Centro Málaga',
                'postal_code' => '29001',
                'municipality_name' => 'Málaga',
                'estimated_production_kwh_day' => 13000.00,
                'reserved_kwh_day' => 9200.00,
                'requested_kwh_day' => 11500.00,
                'status' => 'verde',
                'notes' => 'Ciudad costera con excelente potencial solar'
            ],

            // Murcia
            [
                'zone_name' => 'Centro Murcia',
                'postal_code' => '30001',
                'municipality_name' => 'Murcia',
                'estimated_production_kwh_day' => 10500.00,
                'reserved_kwh_day' => 7800.00,
                'requested_kwh_day' => 9200.00,
                'status' => 'verde',
                'notes' => 'Región con gran potencial de energía solar'
            ],

            // Palma de Mallorca
            [
                'zone_name' => 'Centro Palma',
                'postal_code' => '07001',
                'municipality_name' => 'Palma',
                'estimated_production_kwh_day' => 12000.00,
                'reserved_kwh_day' => 8500.00,
                'requested_kwh_day' => 10500.00,
                'status' => 'verde',
                'notes' => 'Isla con proyectos de energía renovable y almacenamiento'
            ],

            // Las Palmas
            [
                'zone_name' => 'Vegueta',
                'postal_code' => '35001',
                'municipality_name' => 'Las Palmas de Gran Canaria',
                'estimated_production_kwh_day' => 8500.00,
                'reserved_kwh_day' => 6200.00,
                'requested_kwh_day' => 7800.00,
                'status' => 'verde',
                'notes' => 'Zona histórica con proyectos de energía eólica y solar'
            ],

            // Santa Cruz de Tenerife
            [
                'zone_name' => 'Centro Santa Cruz',
                'postal_code' => '38001',
                'municipality_name' => 'Santa Cruz de Tenerife',
                'estimated_production_kwh_day' => 9500.00,
                'reserved_kwh_day' => 6800.00,
                'requested_kwh_day' => 8200.00,
                'status' => 'verde',
                'notes' => 'Capital canaria con proyectos de energía renovable'
            ]
        ];

        $this->command->info('🌱 Creando resúmenes de zonas energéticas...');

        foreach ($energyZones as $zoneData) {
            $municipality = $municipalities->firstWhere('name', $zoneData['municipality_name']);
            
            if (!$municipality) {
                $this->command->warn("⚠️ Municipio '{$zoneData['municipality_name']}' no encontrado. Saltando zona '{$zoneData['zone_name']}'");
                continue;
            }

            // Calcular energía disponible
            $available_kwh_day = max(0, $zoneData['estimated_production_kwh_day'] - $zoneData['reserved_kwh_day']);

            EnergyZoneSummary::create([
                'zone_name' => $zoneData['zone_name'],
                'postal_code' => $zoneData['postal_code'],
                'municipality_id' => $municipality->id,
                'estimated_production_kwh_day' => $zoneData['estimated_production_kwh_day'],
                'reserved_kwh_day' => $zoneData['reserved_kwh_day'],
                'requested_kwh_day' => $zoneData['requested_kwh_day'],
                'available_kwh_day' => $available_kwh_day,
                'status' => $zoneData['status'],
                'last_updated_at' => Carbon::now()->subHours(rand(1, 48)),
                'notes' => $zoneData['notes'],
            ]);

            $this->command->line("✅ Zona '{$zoneData['zone_name']}' ({$zoneData['postal_code']}) creada");
        }

        // Mostrar resumen
        $totalZones = EnergyZoneSummary::count();
        $totalProduction = EnergyZoneSummary::sum('estimated_production_kwh_day');
        $totalAvailable = EnergyZoneSummary::sum('available_kwh_day');
        $zonesByStatus = EnergyZoneSummary::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $this->command->info("🎉 Seeder completado:");
        $this->command->line("   📊 Total de zonas: {$totalZones}");
        $this->command->line("   ⚡ Producción total: " . number_format($totalProduction, 2) . " kWh/día");
        $this->command->line("   🔋 Energía disponible: " . number_format($totalAvailable, 2) . " kWh/día");
        $this->command->line("   🟢 Zonas verdes: " . ($zonesByStatus['verde'] ?? 0));
        $this->command->line("   🟠 Zonas naranjas: " . ($zonesByStatus['naranja'] ?? 0));
        $this->command->line("   🔴 Zonas rojas: " . ($zonesByStatus['rojo'] ?? 0));
    }
}
