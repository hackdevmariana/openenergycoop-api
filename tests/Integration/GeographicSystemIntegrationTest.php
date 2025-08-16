<?php

namespace Tests\Integration;

use App\Models\Region;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\WeatherSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Carbon\Carbon;

class GeographicSystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_create_complete_geographic_hierarchy()
    {
        // Crear jerarquía completa: Región -> Provincia -> Municipio -> Datos meteorológicos
        $madrid = Region::factory()->madrid()->create();
        $madridProv = Province::factory()->madrid()->create(['region_id' => $madrid->id]);
        $madridMuni = Municipality::factory()->madrid()->create(['province_id' => $madridProv->id]);
        $weather = WeatherSnapshot::factory()->forMunicipality($madridMuni)->optimalSolar()->create();

        // Verificar relaciones funcionan correctamente
        expect($weather->municipality->name)->toBe('Madrid');
        expect($weather->municipality->province->name)->toBe('Madrid');
        expect($weather->municipality->province->region->name)->toBe('Madrid');
        
        // Verificar relaciones inversas
        expect($madrid->provinces)->toHaveCount(1);
        expect($madrid->municipalities)->toHaveCount(1);
        expect($madridMuni->weatherSnapshots)->toHaveCount(1);
    }

    /** @test */
    public function it_can_build_realistic_spanish_geography()
    {
        // Crear varias regiones con sus provincias y municipios
        $madrid = Region::factory()->madrid()->create();
        $andalucia = Region::factory()->andalucia()->create();
        $cataluna = Region::factory()->cataluna()->create();

        // Madrid
        $madridProv = Province::factory()->madrid()->create(['region_id' => $madrid->id]);
        $madridMuni = Municipality::factory()->madrid()->create(['province_id' => $madridProv->id]);
        $alcalaMuni = Municipality::factory()->create([
            'province_id' => $madridProv->id,
            'name' => 'Alcalá de Henares',
            'slug' => 'alcala-de-henares-madrid',
        ]);

        // Andalucía
        $sevillaProv = Province::factory()->sevilla()->create(['region_id' => $andalucia->id]);
        $malagaProv = Province::factory()->malaga()->create(['region_id' => $andalucia->id]);
        $sevillaMuni = Municipality::factory()->sevilla()->create(['province_id' => $sevillaProv->id]);
        $malagaMuni = Municipality::factory()->create([
            'province_id' => $malagaProv->id,
            'name' => 'Málaga',
            'slug' => 'malaga-malaga',
        ]);

        // Cataluña
        $barcelonaProv = Province::factory()->barcelona()->create(['region_id' => $cataluna->id]);
        $barcelonaMuni = Municipality::factory()->barcelona()->create(['province_id' => $barcelonaProv->id]);

        // Verificar estructura
        expect(Region::count())->toBe(3);
        expect(Province::count())->toBe(4);
        expect(Municipality::count())->toBe(5);

        // Verificar conteos por región
        expect($madrid->getProvincesCount())->toBe(1);
        expect($madrid->getMunicipalitiesCount())->toBe(2);
        expect($andalucia->getProvincesCount())->toBe(2);
        expect($andalucia->getMunicipalitiesCount())->toBe(2);
    }

    /** @test */
    public function it_can_handle_complex_weather_data_analysis()
    {
        // Crear estructura geográfica
        $andalucia = Region::factory()->andalucia()->create();
        $sevilla = Province::factory()->sevilla()->create(['region_id' => $andalucia->id]);
        $malaga = Province::factory()->malaga()->create(['region_id' => $andalucia->id]);
        
        $sevillaMuni = Municipality::factory()->sevilla()->create(['province_id' => $sevilla->id]);
        $malagaMuni = Municipality::factory()->create([
            'province_id' => $malaga->id,
            'name' => 'Málaga',
            'slug' => 'malaga-malaga',
        ]);

        // Crear datos meteorológicos variados
        // Sevilla - Condiciones excelentes para solar
        WeatherSnapshot::factory()->forMunicipality($sevillaMuni)->count(5)->create([
            'solar_radiation' => 950.0,
            'cloud_coverage' => 10.0,
            'temperature' => 28.0,
        ]);

        // Málaga - Condiciones buenas
        WeatherSnapshot::factory()->forMunicipality($malagaMuni)->count(3)->create([
            'solar_radiation' => 650.0,
            'cloud_coverage' => 35.0,
            'temperature' => 25.0,
        ]);

        // Análisis regional
        $regionWeather = $andalucia->getAverageWeatherData();
        expect($regionWeather['data_points'])->toBe(8);
        expect($regionWeather['avg_solar_radiation'])->toBeGreaterThan(700); // Promedio de ambas ciudades

        // Análisis municipal
        $sevillaPotential = $sevillaMuni->getSolarEnergyPotential();
        $malagaPotential = $malagaMuni->getSolarEnergyPotential();
        
        expect($sevillaPotential)->toBeGreaterThan($malagaPotential); // Sevilla debe tener mejor potencial
        expect($sevillaPotential)->toBeGreaterThan(80); // Excelente potencial
    }

    /** @test */
    public function it_can_perform_cross_regional_solar_analysis()
    {
        // Crear múltiples regiones
        $regions = [
            'Madrid' => Region::factory()->madrid()->create(),
            'Andalucía' => Region::factory()->andalucia()->create(),
            'Valencia' => Region::factory()->valencia()->create(),
        ];

        $municipalities = [];

        foreach ($regions as $regionName => $region) {
            $province = Province::factory()->create([
                'region_id' => $region->id,
                'name' => $regionName,
                'slug' => strtolower($regionName),
            ]);
            
            $municipality = Municipality::factory()->create([
                'province_id' => $province->id,
                'name' => $regionName,
                'slug' => strtolower($regionName) . '-' . strtolower($regionName),
            ]);
            
            $municipalities[$regionName] = $municipality;
        }

        // Simular diferentes condiciones climáticas
        // Madrid - Condiciones moderadas
        WeatherSnapshot::factory()->forMunicipality($municipalities['Madrid'])->count(10)->create([
            'solar_radiation' => 600.0,
            'cloud_coverage' => 40.0,
        ]);

        // Andalucía - Excelentes condiciones
        WeatherSnapshot::factory()->forMunicipality($municipalities['Andalucía'])->count(10)->create([
            'solar_radiation' => 900.0,
            'cloud_coverage' => 15.0,
        ]);

        // Valencia - Buenas condiciones
        WeatherSnapshot::factory()->forMunicipality($municipalities['Valencia'])->count(10)->create([
            'solar_radiation' => 750.0,
            'cloud_coverage' => 25.0,
        ]);

        // Análisis comparativo a través de API
        $response = $this->getJson('/api/v1/regions?include_counts=true');
        $response->assertOk();

        $regionsData = collect($response->json());
        
        foreach ($regionsData as $regionData) {
            expect($regionData['has_weather_data'])->toBeTrue();
            expect($regionData['municipalities_count'])->toBe(1);
        }

        // Verificar que podemos obtener el mejor municipio para solar
        $andaluciaMuni = $municipalities['Andalucía'];
        $andaluciaPotential = $andaluciaMuni->getSolarEnergyPotential();
        
        $madridMuni = $municipalities['Madrid'];
        $madridPotential = $madridMuni->getSolarEnergyPotential();

        expect($andaluciaPotential)->toBeGreaterThan($madridPotential);
    }

    /** @test */
    public function it_can_track_temporal_weather_patterns()
    {
        // Crear municipio
        $region = Region::factory()->create();
        $province = Province::factory()->create(['region_id' => $region->id]);
        $municipality = Municipality::factory()->operating()->create(['province_id' => $province->id]);

        // Simular datos meteorológicos a lo largo del tiempo
        $baseDate = Carbon::parse('2024-08-01');
        
        for ($day = 0; $day < 30; $day++) {
            $currentDate = $baseDate->copy()->addDays($day);
            
            // Simular patrón diario: mejor radiación en verano, empeora gradualmente
            $solarRadiation = 900 - ($day * 10); // Decrece con el tiempo
            $cloudCoverage = 10 + ($day * 1.5); // Aumenta con el tiempo
            
            WeatherSnapshot::factory()->forMunicipality($municipality)->create([
                'solar_radiation' => max($solarRadiation, 200),
                'cloud_coverage' => min($cloudCoverage, 80),
                'timestamp' => $currentDate->setHour(12),
            ]);
        }

        // Análisis de primeros 15 días vs últimos 15 días
        $firstHalfFrom = $baseDate->copy();
        $firstHalfTo = $baseDate->copy()->addDays(14);
        $firstHalf = $municipality->getAverageWeatherConditions($firstHalfFrom, $firstHalfTo);

        $secondHalfFrom = $baseDate->copy()->addDays(15);
        $secondHalfTo = $baseDate->copy()->addDays(29);
        $secondHalf = $municipality->getAverageWeatherConditions($secondHalfFrom, $secondHalfTo);

        // Primera mitad debe tener mejor radiación solar
        expect($firstHalf['avg_solar_radiation'])->toBeGreaterThan($secondHalf['avg_solar_radiation']);
        expect($firstHalf['avg_cloud_coverage'])->toBeLessThan($secondHalf['avg_cloud_coverage']);

        // Verificar a través de API
        $response = $this->getJson("/api/v1/municipalities/{$municipality->id}/weather?days=30");
        $response->assertOk();
        
        expect($response->json('average_conditions.weather_readings'))->toBe(30);
    }

    /** @test */
    public function it_can_handle_cooperative_expansion_analysis()
    {
        // Simular análisis de expansión de cooperativa
        $regions = [];
        $municipalities = [];

        // Crear 3 regiones con diferentes perfiles
        for ($i = 1; $i <= 3; $i++) {
            $region = Region::factory()->create(['name' => "Región $i"]);
            $province = Province::factory()->create([
                'region_id' => $region->id,
                'name' => "Provincia $i",
            ]);
            
            $regions[] = $region;
            
            // Cada región tiene 2 municipios
            for ($j = 1; $j <= 2; $j++) {
                $isOperating = ($i <= 2); // Solo regiones 1 y 2 operan actualmente
                
                $municipality = Municipality::factory()->create([
                    'province_id' => $province->id,
                    'name' => "Municipio $i-$j",
                    'text' => $isOperating ? 'Cooperativa opera aquí' : null,
                ]);
                
                $municipalities[] = $municipality;
                
                // Región 3 tiene mejor potencial solar pero no opera aún
                $solarQuality = ($i === 3) ? 'excellent' : 'good';
                $radiation = ($i === 3) ? 950.0 : 650.0;
                $clouds = ($i === 3) ? 10.0 : 35.0;
                
                WeatherSnapshot::factory()->forMunicipality($municipality)->count(5)->create([
                    'solar_radiation' => $radiation,
                    'cloud_coverage' => $clouds,
                ]);
            }
        }

        // API: Municipios donde opera actualmente
        $response = $this->getJson('/api/v1/municipalities?operating_only=true');
        $response->assertOk();
        expect($response->json())->toHaveCount(4); // 2 regiones × 2 municipios

        // API: Todos los municipios con datos meteorológicos
        $response = $this->getJson('/api/v1/municipalities?with_weather=true');
        $response->assertOk();
        expect($response->json())->toHaveCount(6); // 3 regiones × 2 municipios

        // Análisis: Encontrar oportunidades de expansión (bueno solar + no opera)
        $expansionOpportunities = Municipality::query()
            ->whereNull('text') // No opera
            ->withWeatherData() // Tiene datos meteorológicos
            ->with(['province.region'])
            ->get()
            ->filter(function ($municipality) {
                return $municipality->getSolarEnergyPotential() > 70;
            });

        expect($expansionOpportunities)->toHaveCount(2); // Los 2 municipios de región 3
    }

    /** @test */
    public function it_handles_api_error_scenarios_gracefully()
    {
        // Región inexistente
        $response = $this->getJson('/api/v1/regions/999');
        $response->assertNotFound();

        // Municipio inexistente
        $response = $this->getJson('/api/v1/municipalities/999');
        $response->assertNotFound();

        // Slug inexistente
        $response = $this->getJson('/api/v1/regions/slug/inexistente');
        $response->assertNotFound();

        // Datos meteorológicos de región sin datos
        $region = Region::factory()->create();
        $response = $this->getJson("/api/v1/regions/{$region->id}/weather");
        $response->assertOk();
        expect($response->json('weather_stats.data_points'))->toBe(0);
    }

    /** @test */
    public function it_can_perform_real_time_monitoring_simulation()
    {
        // Simular monitoreo en tiempo real de una red de municipios
        $region = Region::factory()->create(['name' => 'Red de Monitoreo']);
        $province = Province::factory()->create(['region_id' => $region->id]);
        
        $municipalities = [];
        for ($i = 1; $i <= 5; $i++) {
            $municipalities[] = Municipality::factory()->operating()->create([
                'province_id' => $province->id,
                'name' => "Estación $i",
            ]);
        }

        // Simular lecturas cada hora durante las últimas 24 horas
        $now = Carbon::now();
        
        for ($hour = 23; $hour >= 0; $hour--) {
            $timestamp = $now->copy()->subHours($hour);
            
            foreach ($municipalities as $index => $municipality) {
                // Cada municipio tiene condiciones ligeramente diferentes
                $baseRadiation = $this->getSolarRadiationForHour($timestamp->hour);
                $radiation = $baseRadiation + ($index * 50); // Variación por ubicación
                $clouds = 20 + ($index * 10); // Diferentes niveles de nubes
                
                if ($radiation > 0) { // Solo crear datos diurnos
                    WeatherSnapshot::factory()->forMunicipality($municipality)->create([
                        'solar_radiation' => $radiation,
                        'cloud_coverage' => min($clouds, 80),
                        'temperature' => 20 + ($index * 2),
                        'timestamp' => $timestamp,
                    ]);
                }
            }
        }

        // Análisis del sistema
        $response = $this->getJson("/api/v1/regions/{$region->id}/weather");
        $response->assertOk();
        
        $weatherStats = $response->json('weather_stats');
        expect($weatherStats['data_points'])->toBeGreaterThan(50); // Múltiples lecturas por municipio

        // Verificar que cada municipio tiene datos recientes
        foreach ($municipalities as $municipality) {
            expect($municipality->hasRecentWeatherData())->toBeTrue();
            
            $response = $this->getJson("/api/v1/municipalities/{$municipality->id}/weather?days=1");
            $response->assertOk();
            
            $currentWeather = $response->json('current_weather');
            expect($currentWeather)->not->toBeNull();
        }
    }

    /** @test */
    public function it_can_calculate_regional_solar_rankings()
    {
        // Crear múltiples regiones para ranking
        $regionData = [
            'Sevilla' => ['radiation' => 950, 'clouds' => 10], // Mejor
            'Valencia' => ['radiation' => 750, 'clouds' => 25], // Medio
            'Asturias' => ['radiation' => 450, 'clouds' => 60], // Peor
        ];

        $regions = [];
        
        foreach ($regionData as $name => $weather) {
            $region = Region::factory()->create(['name' => $name]);
            $province = Province::factory()->create(['region_id' => $region->id]);
            $municipality = Municipality::factory()->operating()->create(['province_id' => $province->id]);
            
            // Crear datos consistentes para cada región
            WeatherSnapshot::factory()->forMunicipality($municipality)->count(10)->create([
                'solar_radiation' => $weather['radiation'],
                'cloud_coverage' => $weather['clouds'],
            ]);
            
            $regions[$name] = [
                'region' => $region,
                'municipality' => $municipality,
                'expected_potential' => $this->calculateExpectedPotential($weather['radiation'], $weather['clouds']),
            ];
        }

        // Verificar ranking a través de cálculos
        $sevillaPotential = $regions['Sevilla']['municipality']->getSolarEnergyPotential();
        $valenciaPotential = $regions['Valencia']['municipality']->getSolarEnergyPotential();
        $asturiasPotential = $regions['Asturias']['municipality']->getSolarEnergyPotential();

        expect($sevillaPotential)->toBeGreaterThan($valenciaPotential);
        expect($valenciaPotential)->toBeGreaterThan($asturiasPotential);
        expect($sevillaPotential)->toBeGreaterThan(80); // Excelente
        expect($asturiasPotential)->toBeLessThan(50); // Pobre
    }

    /**
     * Helper para simular radiación solar por hora
     */
    private function getSolarRadiationForHour(int $hour): float
    {
        if ($hour < 6 || $hour > 20) {
            return 0; // Noche
        }
        
        // Curva solar simplificada
        $peak = 13; // 1 PM
        $distance = abs($hour - $peak);
        $maxRadiation = 1000;
        
        return max(0, $maxRadiation * (1 - ($distance / 7)));
    }

    /**
     * Helper para calcular potencial esperado
     */
    private function calculateExpectedPotential(float $radiation, float $clouds): float
    {
        $solarScore = min($radiation / 1000 * 100, 100);
        $cloudPenalty = $clouds / 100 * 30;
        
        return max($solarScore - $cloudPenalty, 0);
    }
}
