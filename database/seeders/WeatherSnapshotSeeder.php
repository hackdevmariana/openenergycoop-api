<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WeatherSnapshot;
use App\Models\Municipality;
use Carbon\Carbon;

class WeatherSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $municipalities = Municipality::take(20)->get();
        if ($municipalities->isEmpty()) {
            $this->command->warn('⚠️ No hay municipios disponibles. Ejecutando MunicipalitySeeder primero...');
            $this->call(MunicipalitySeeder::class);
            $municipalities = Municipality::take(20)->get();
        }

        $weatherSnapshots = [];

        // Generar datos meteorológicos para los últimos 30 días
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);
            
            foreach ($municipalities as $municipality) {
                // Generar datos cada 3 horas del día
                for ($hour = 0; $hour < 24; $hour += 3) {
                    $timestamp = $date->copy()->setHour($hour)->setMinute(0)->setSecond(0);
                    
                    // Simular condiciones meteorológicas realistas
                    $baseTemp = $this->getBaseTemperature($municipality->name, $date->month);
                    $temperature = $baseTemp + rand(-5, 5) + ($hour - 12) * 0.5; // Variación diurna
                    
                    $cloudCoverage = $this->getCloudCoverage($municipality->name, $date->month, $hour);
                    $solarRadiation = $this->getSolarRadiation($municipality->name, $date->month, $hour, $cloudCoverage);
                    
                    $weatherSnapshots[] = [
                        'municipality_id' => $municipality->id,
                        'temperature' => round($temperature, 1),
                        'cloud_coverage' => round($cloudCoverage, 1),
                        'solar_radiation' => round($solarRadiation, 1),
                        'timestamp' => $timestamp,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insertar usando firstOrCreate para evitar duplicados
        foreach ($weatherSnapshots as $snapshot) {
            WeatherSnapshot::firstOrCreate(
                [
                    'municipality_id' => $snapshot['municipality_id'],
                    'timestamp' => $snapshot['timestamp'],
                ],
                $snapshot
            );
        }

        $this->command->info('✅ WeatherSnapshotSeeder ejecutado correctamente');
        $this->command->info('📊 Snapshots meteorológicos creados: ' . count($weatherSnapshots));
        $this->command->info('🌡️ Datos de temperatura, cobertura de nubes y radiación solar');
        $this->command->info('📅 Período: Últimos 30 días con lecturas cada 3 horas');
        $this->command->info('🏙️ Municipios incluidos: ' . $municipalities->count());
        $this->command->info('🌞 Datos realistas basados en ubicación geográfica y estacionalidad');
    }

    private function getBaseTemperature(string $municipality, int $month): float
    {
        // Temperaturas base por municipio y mes (promedio anual)
        $temperatures = [
            'Madrid' => [8, 10, 14, 16, 20, 25, 28, 27, 23, 17, 12, 9],
            'Barcelona' => [10, 11, 13, 15, 18, 22, 25, 25, 22, 18, 14, 11],
            'Valencia' => [12, 13, 15, 17, 20, 24, 27, 27, 24, 20, 16, 13],
            'Sevilla' => [12, 14, 17, 19, 23, 28, 32, 32, 28, 22, 17, 13],
            'Málaga' => [13, 14, 16, 18, 21, 25, 28, 28, 25, 21, 17, 14],
            'Alicante' => [13, 14, 16, 18, 21, 25, 28, 28, 25, 21, 17, 14],
            'Murcia' => [12, 13, 15, 17, 20, 24, 27, 27, 24, 20, 16, 13],
            'Palma' => [12, 13, 15, 17, 20, 24, 27, 27, 24, 20, 16, 13],
            'Las Palmas de Gran Canaria' => [18, 18, 19, 20, 21, 23, 24, 25, 24, 23, 21, 19],
            'Santa Cruz de Tenerife' => [18, 18, 19, 20, 21, 23, 24, 25, 24, 23, 21, 19],
            'Bilbao' => [9, 10, 12, 13, 16, 19, 21, 21, 19, 16, 12, 10],
            'A Coruña' => [10, 10, 12, 13, 15, 18, 20, 20, 18, 15, 12, 10],
            'Vigo' => [10, 11, 13, 14, 16, 19, 21, 21, 19, 16, 13, 11],
            'Zaragoza' => [7, 9, 13, 15, 19, 24, 27, 26, 22, 16, 11, 8],
            'Toledo' => [8, 10, 14, 16, 20, 25, 28, 27, 23, 17, 12, 9],
            'Valladolid' => [6, 8, 12, 14, 18, 23, 26, 25, 21, 15, 10, 7],
            'Salamanca' => [6, 8, 12, 14, 18, 23, 26, 25, 21, 15, 10, 7],
            'Burgos' => [5, 7, 11, 13, 17, 22, 25, 24, 20, 14, 9, 6],
            'León' => [6, 8, 12, 14, 18, 23, 26, 25, 21, 15, 10, 7],
            'Badajoz' => [10, 12, 16, 18, 22, 27, 30, 29, 25, 19, 14, 11],
        ];

        return $temperatures[$municipality][$month - 1] ?? 15.0;
    }

    private function getCloudCoverage(string $municipality, int $month, int $hour): float
    {
        // Cobertura de nubes base por municipio y mes
        $cloudBase = [
            'Madrid' => [60, 55, 50, 45, 40, 30, 25, 30, 40, 50, 60, 65],
            'Barcelona' => [50, 45, 40, 35, 30, 25, 20, 25, 35, 45, 50, 55],
            'Valencia' => [45, 40, 35, 30, 25, 20, 15, 20, 30, 40, 45, 50],
            'Sevilla' => [40, 35, 30, 25, 20, 15, 10, 15, 25, 35, 40, 45],
            'Málaga' => [45, 40, 35, 30, 25, 20, 15, 20, 30, 40, 45, 50],
            'Alicante' => [45, 40, 35, 30, 25, 20, 15, 20, 30, 40, 45, 50],
            'Murcia' => [40, 35, 30, 25, 20, 15, 10, 15, 25, 35, 40, 45],
            'Palma' => [50, 45, 40, 35, 30, 25, 20, 25, 35, 45, 50, 55],
            'Las Palmas de Gran Canaria' => [30, 25, 20, 15, 10, 5, 5, 10, 15, 20, 25, 30],
            'Santa Cruz de Tenerife' => [30, 25, 20, 15, 10, 5, 5, 10, 15, 20, 25, 30],
            'Bilbao' => [70, 65, 60, 55, 50, 45, 40, 45, 55, 65, 70, 75],
            'A Coruña' => [75, 70, 65, 60, 55, 50, 45, 50, 60, 70, 75, 80],
            'Vigo' => [75, 70, 65, 60, 55, 50, 45, 50, 60, 70, 75, 80],
            'Zaragoza' => [55, 50, 45, 40, 35, 30, 25, 30, 40, 50, 55, 60],
            'Toledo' => [60, 55, 50, 45, 40, 30, 25, 30, 40, 50, 60, 65],
            'Valladolid' => [65, 60, 55, 50, 45, 35, 30, 35, 45, 55, 65, 70],
            'Salamanca' => [65, 60, 55, 50, 45, 35, 30, 35, 45, 55, 65, 70],
            'Burgos' => [70, 65, 60, 55, 50, 40, 35, 40, 50, 60, 70, 75],
            'León' => [70, 65, 60, 55, 50, 40, 35, 40, 50, 60, 70, 75],
            'Badajoz' => [50, 45, 40, 35, 30, 25, 20, 25, 35, 45, 50, 55],
        ];

        $baseCloud = $cloudBase[$municipality][$month - 1] ?? 50.0;
        
        // Variación horaria (menos nubes durante el día)
        $hourlyVariation = 0;
        if ($hour >= 6 && $hour <= 18) {
            $hourlyVariation = -20 + ($hour - 6) * 1.67; // Menos nubes durante el día
        } else {
            $hourlyVariation = 10; // Más nubes por la noche
        }
        
        return max(0, min(100, $baseCloud + $hourlyVariation + rand(-10, 10)));
    }

    private function getSolarRadiation(string $municipality, int $month, int $hour, float $cloudCoverage): float
    {
        // Radiación solar máxima teórica por mes (W/m²)
        $maxRadiation = [
            1 => 400, 2 => 500, 3 => 700, 4 => 900, 5 => 1000, 6 => 1100,
            7 => 1100, 8 => 1000, 9 => 800, 10 => 600, 11 => 400, 12 => 350
        ];

        $maxRad = $maxRadiation[$month] ?? 800;
        
        // Factor horario (0 durante la noche, máximo al mediodía)
        $hourlyFactor = 0;
        if ($hour >= 6 && $hour <= 18) {
            $hourlyFactor = sin(($hour - 6) * M_PI / 12);
        }
        
        // Factor de cobertura de nubes (menos radiación con más nubes)
        $cloudFactor = (100 - $cloudCoverage) / 100;
        
        // Factor geográfico (más radiación en el sur)
        $geographicFactor = [
            'Madrid' => 0.9, 'Barcelona' => 0.85, 'Valencia' => 0.95, 'Sevilla' => 1.0,
            'Málaga' => 1.0, 'Alicante' => 0.95, 'Murcia' => 0.95, 'Palma' => 0.9,
            'Las Palmas de Gran Canaria' => 1.1, 'Santa Cruz de Tenerife' => 1.1,
            'Bilbao' => 0.7, 'A Coruña' => 0.7, 'Vigo' => 0.7, 'Zaragoza' => 0.9,
            'Toledo' => 0.9, 'Valladolid' => 0.8, 'Salamanca' => 0.8, 'Burgos' => 0.8,
            'León' => 0.8, 'Badajoz' => 0.95
        ];
        
        $geoFactor = $geographicFactor[$municipality] ?? 0.85;
        
        return $maxRad * $hourlyFactor * $cloudFactor * $geoFactor;
    }
}
