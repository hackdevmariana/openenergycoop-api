<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Region;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\WeatherSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Region $region;
    private Province $province;
    private Municipality $municipality;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Crear estructura geográfica completa
        $this->region = Region::factory()->madrid()->create();
        $this->province = Province::factory()->madrid()->create([
            'region_id' => $this->region->id,
        ]);
        $this->municipality = Municipality::factory()->madrid()->create([
            'province_id' => $this->province->id,
        ]);
    }

    /** @test */
    public function it_can_list_all_regions()
    {
        // Crear regiones adicionales (ya hay 1 de setUp)
        Region::factory()->andalucia()->create();
        Region::factory()->cataluna()->create();

        $response = $this->getJson('/api/v1/regions');
        
        // Verificar que al menos hay 3 regiones (puede que factories creen más)
        $regions = $response->json();
        expect(count($regions))->toBeGreaterThanOrEqual(3);

        $response->assertOk()
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'created_at',
                        'updated_at',
                    ]
                ]);
    }

    /** @test */
    public function it_can_list_regions_with_counts()
    {
        $response = $this->getJson('/api/v1/regions?include_counts=true');

        $response->assertOk()
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'provinces_count',
                        'municipalities_count',
                        'has_weather_data',
                    ]
                ]);

        $regionData = $response->json()[0];
        expect($regionData['provinces_count'])->toBe(1);
        expect($regionData['municipalities_count'])->toBe(1);
    }

    /** @test */
    public function it_can_filter_regions_with_weather_data()
    {
        // Crear región sin datos meteorológicos
        $regionWithoutWeather = Region::factory()->valencia()->create();
        Province::factory()->valencia()->create(['region_id' => $regionWithoutWeather->id]);

        // Añadir datos meteorológicos a Madrid
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->create();

        $response = $this->getJson('/api/v1/regions?with_weather=true');

        $response->assertOk()
                ->assertJsonCount(1);

        $regionData = $response->json()[0];
        expect($regionData['name'])->toBe('Madrid');
    }

    /** @test */
    public function it_can_show_specific_region()
    {
        $response = $this->getJson("/api/v1/regions/{$this->region->id}");

        $response->assertOk()
                ->assertJson([
                    'id' => $this->region->id,
                    'name' => 'Madrid',
                    'slug' => 'madrid',
                ])
                ->assertJsonStructure([
                    'id',
                    'name',
                    'slug',
                    'provinces_count',
                    'municipalities_count',
                ]);

        expect($response->json('provinces_count'))->toBe(1);
        expect($response->json('municipalities_count'))->toBe(1);
    }

    /** @test */
    public function it_can_show_region_with_provinces()
    {
        // Crear provincia adicional
        Province::factory()->create([
            'region_id' => $this->region->id,
            'name' => 'Guadalajara',
            'slug' => 'guadalajara',
        ]);

        $response = $this->getJson("/api/v1/regions/{$this->region->id}?include_provinces=true");

        $response->assertOk()
                ->assertJsonStructure([
                    'id',
                    'name',
                    'provinces' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'municipalities_count',
                        ]
                    ]
                ]);

        expect($response->json('provinces'))->toHaveCount(2);
    }

    /** @test */
    public function it_can_show_region_with_weather_stats()
    {
        // Crear datos meteorológicos
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->optimalSolar()->create();
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->summer()->create();

        $response = $this->getJson("/api/v1/regions/{$this->region->id}?include_weather=true");

        $response->assertOk()
                ->assertJsonStructure([
                    'weather_stats' => [
                        'avg_temperature',
                        'avg_cloud_coverage',
                        'avg_solar_radiation',
                        'data_points',
                    ],
                    'latest_weather',
                ]);

        $weatherStats = $response->json('weather_stats');
        expect($weatherStats['data_points'])->toBeGreaterThan(0);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_region()
    {
        $response = $this->getJson('/api/v1/regions/999');

        $response->assertNotFound();
    }

    /** @test */
    public function it_can_show_region_by_slug()
    {
        $response = $this->getJson('/api/v1/regions/slug/madrid');

        $response->assertOk()
                ->assertJson([
                    'id' => $this->region->id,
                    'name' => 'Madrid',
                    'slug' => 'madrid',
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_slug()
    {
        $response = $this->getJson('/api/v1/regions/slug/nonexistent');

        $response->assertNotFound();
    }

    /** @test */
    public function it_can_get_region_weather_statistics()
    {
        // Crear varios registros meteorológicos
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->count(5)->create();

        $response = $this->getJson("/api/v1/regions/{$this->region->id}/weather");

        $response->assertOk()
                ->assertJsonStructure([
                    'region' => [
                        'id',
                        'name',
                        'slug',
                    ],
                    'period' => [
                        'from',
                        'to',
                    ],
                    'weather_stats' => [
                        'avg_temperature',
                        'avg_cloud_coverage',
                        'avg_solar_radiation',
                        'data_points',
                    ],
                ]);

        $weatherData = $response->json('weather_stats');
        expect($weatherData['data_points'])->toBe(5);
        expect($weatherData['avg_temperature'])->toBeGreaterThan(0);
    }

    /** @test */
    public function it_can_filter_weather_by_date_range()
    {
        // Crear datos meteorológicos en diferentes fechas
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(now()->subDays(10))->create();
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(now()->subDays(5))->create();
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(now())->create();

        $from = now()->subDays(7)->toDateString();
        $to = now()->toDateString();

        $response = $this->getJson("/api/v1/regions/{$this->region->id}/weather?from={$from}&to={$to}");

        $response->assertOk();

        $weatherData = $response->json('weather_stats');
        expect($weatherData['data_points'])->toBe(2); // Solo los últimos 7 días

        $period = $response->json('period');
        expect($period['from'])->toBe($from);
        expect($period['to'])->toBe($to);
    }

    /** @test */
    public function it_handles_regions_without_weather_data()
    {
        $response = $this->getJson("/api/v1/regions/{$this->region->id}/weather");

        $response->assertOk();

        $weatherData = $response->json('weather_stats');
        expect($weatherData['data_points'])->toBe(0);
        expect($weatherData['avg_temperature'])->toBeNull();
    }

    /** @test */
    public function it_requires_authentication()
    {
        // Sin autenticación
        $this->withoutMiddleware();
        auth()->logout();

        $response = $this->getJson('/api/v1/regions');

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_orders_regions_alphabetically()
    {
        // Crear regiones en orden diferente
        Region::factory()->create(['name' => 'Zaragoza', 'slug' => 'zaragoza']);
        Region::factory()->create(['name' => 'Andalucía', 'slug' => 'andalucia']);
        Region::factory()->create(['name' => 'Barcelona', 'slug' => 'barcelona']);

        $response = $this->getJson('/api/v1/regions');

        $response->assertOk();

        $regions = $response->json();
        $names = array_column($regions, 'name');

        // Verificar orden alfabético
        expect($names[0])->toBe('Andalucía');
        expect($names[1])->toBe('Barcelona');
        expect($names[2])->toBe('Madrid');
        expect($names[3])->toBe('Zaragoza');
    }

    /** @test */
    public function it_handles_invalid_date_formats_in_weather_endpoint()
    {
        $response = $this->getJson("/api/v1/regions/{$this->region->id}/weather?from=invalid-date&to=also-invalid");

        // Debería manejar fechas inválidas graciosamente
        $response->assertStatus(500); // O el código de error apropiado según tu manejo de errores
    }

    /** @test */
    public function it_calculates_region_statistics_correctly()
    {
        // Crear estructura más compleja
        $province2 = Province::factory()->create([
            'region_id' => $this->region->id,
            'name' => 'Toledo',
            'slug' => 'toledo',
        ]);
        
        $municipality2 = Municipality::factory()->create([
            'province_id' => $province2->id,
        ]);

        $response = $this->getJson("/api/v1/regions/{$this->region->id}");

        $response->assertOk();
        
        expect($response->json('provinces_count'))->toBe(2);
        expect($response->json('municipalities_count'))->toBe(2);
    }

    /** @test */
    public function it_shows_weather_data_flag_correctly()
    {
        $response = $this->getJson('/api/v1/regions?include_counts=true');
        
        $regionData = $response->json()[0];
        expect($regionData['has_weather_data'])->toBeFalse();

        // Añadir datos meteorológicos
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->create();

        $response = $this->getJson('/api/v1/regions?include_counts=true');
        
        $regionData = $response->json()[0];
        expect($regionData['has_weather_data'])->toBeTrue();
    }

    /** @test */
    public function it_handles_empty_regions_list()
    {
        // Eliminar todas las regiones
        Region::query()->delete();

        $response = $this->getJson('/api/v1/regions');

        $response->assertOk()
                ->assertJsonCount(0);
    }

    /** @test */
    public function it_returns_proper_json_structure_for_weather_endpoint()
    {
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->create([
            'temperature' => 25.5,
            'solar_radiation' => 850.0,
            'cloud_coverage' => 20.0,
        ]);

        $response = $this->getJson("/api/v1/regions/{$this->region->id}/weather");

        $response->assertOk()
                ->assertJsonStructure([
                    'region' => ['id', 'name', 'slug'],
                    'period' => ['from', 'to'],
                    'weather_stats' => [
                        'avg_temperature',
                        'avg_cloud_coverage', 
                        'avg_solar_radiation',
                        'data_points'
                    ]
                ]);

        $stats = $response->json('weather_stats');
        expect((float) $stats['avg_temperature'])->toBe(25.5);
        expect((float) $stats['avg_solar_radiation'])->toBe(850.0);
        expect((float) $stats['avg_cloud_coverage'])->toBe(20.0);
    }
}
