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

class MunicipalityControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Region $madrid;
    private Region $andalucia;
    private Province $madridProv;
    private Province $sevillaProv;
    private Municipality $madridMuni;
    private Municipality $sevillaMuni;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Crear estructura geográfica
        $this->madrid = Region::factory()->madrid()->create();
        $this->andalucia = Region::factory()->andalucia()->create();
        
        $this->madridProv = Province::factory()->madrid()->create([
            'region_id' => $this->madrid->id,
        ]);
        $this->sevillaProv = Province::factory()->sevilla()->create([
            'region_id' => $this->andalucia->id,
        ]);
        
        $this->madridMuni = Municipality::factory()->madrid()->create([
            'province_id' => $this->madridProv->id,
        ]);
        $this->sevillaMuni = Municipality::factory()->sevilla()->create([
            'province_id' => $this->sevillaProv->id,
        ]);
    }

    /** @test */
    public function it_can_list_all_municipalities()
    {
        $response = $this->getJson('/api/v1/municipalities');

        $response->assertOk()
                ->assertJsonCount(2)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'text',
                        'province',
                        'is_operating',
                        'full_name',
                    ]
                ]);
    }

    /** @test */
    public function it_can_filter_municipalities_by_province()
    {
        $response = $this->getJson("/api/v1/municipalities?province_id={$this->madridProv->id}");

        $response->assertOk()
                ->assertJsonCount(1);

        $municipalityData = $response->json()[0];
        expect($municipalityData['name'])->toBe('Madrid');
        expect($municipalityData['province']['id'])->toBe($this->madridProv->id);
    }

    /** @test */
    public function it_can_filter_municipalities_by_region()
    {
        $response = $this->getJson("/api/v1/municipalities?region_id={$this->andalucia->id}");

        $response->assertOk()
                ->assertJsonCount(1);

        $municipalityData = $response->json()[0];
        expect($municipalityData['name'])->toBe('Sevilla');
    }

    /** @test */
    public function it_can_filter_operating_municipalities_only()
    {
        // Crear municipio sin operación (sin texto)
        Municipality::factory()->notOperating()->create([
            'province_id' => $this->madridProv->id,
            'name' => 'Alcalá de Henares',
            'slug' => 'alcala-de-henares-madrid',
        ]);

        $response = $this->getJson('/api/v1/municipalities?operating_only=true');

        $response->assertOk();

        $municipalities = $response->json();
        foreach ($municipalities as $municipality) {
            expect($municipality['is_operating'])->toBeTrue();
            expect($municipality['text'])->not->toBeNull();
        }
    }

    /** @test */
    public function it_can_filter_municipalities_with_weather_data()
    {
        // Añadir datos meteorológicos solo a Madrid
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->create();

        $response = $this->getJson('/api/v1/municipalities?with_weather=true');

        $response->assertOk()
                ->assertJsonCount(1);

        $municipalityData = $response->json()[0];
        expect($municipalityData['name'])->toBe('Madrid');
    }

    /** @test */
    public function it_can_search_municipalities()
    {
        $response = $this->getJson('/api/v1/municipalities?search=madrid');

        $response->assertOk()
                ->assertJsonCount(1);

        $municipalityData = $response->json()[0];
        expect($municipalityData['name'])->toBe('Madrid');
    }

    /** @test */
    public function it_respects_limit_parameter()
    {
        // Crear municipios adicionales
        Municipality::factory()->count(5)->create([
            'province_id' => $this->madridProv->id,
        ]);

        $response = $this->getJson('/api/v1/municipalities?limit=3');

        $response->assertOk()
                ->assertJsonCount(3);
    }

    /** @test */
    public function it_enforces_maximum_limit()
    {
        $response = $this->getJson('/api/v1/municipalities?limit=500');

        // Debería limitar a 100 máximo
        $response->assertOk();
        expect(count($response->json()))->toBeLessThanOrEqual(100);
    }

    /** @test */
    public function it_adds_solar_potential_for_municipalities_with_weather_data()
    {
        // Crear datos meteorológicos con buena radiación solar
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->optimalSolar()->create();

        $response = $this->getJson('/api/v1/municipalities');

        $response->assertOk();

        $madridData = collect($response->json())->firstWhere('name', 'Madrid');
        expect($madridData)->toHaveKey('solar_potential');
        expect($madridData['solar_potential'])->toBeGreaterThan(0);
    }

    /** @test */
    public function it_can_show_specific_municipality()
    {
        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}");

        $response->assertOk()
                ->assertJson([
                    'id' => $this->madridMuni->id,
                    'name' => 'Madrid',
                    'slug' => 'madrid-madrid',
                ])
                ->assertJsonStructure([
                    'id',
                    'name',
                    'slug',
                    'text',
                    'province',
                    'is_operating',
                    'full_name',
                    'solar_potential',
                    'peak_solar_hours',
                    'has_recent_weather',
                ]);
    }

    /** @test */
    public function it_can_show_municipality_with_weather_summary()
    {
        // Crear datos meteorológicos
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->optimalSolar()->create();

        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}?include_weather=true");

        $response->assertOk()
                ->assertJsonStructure([
                    'weather_summary' => [
                        'current',
                        'monthly_avg',
                        'solar_potential',
                        'peak_solar_hours',
                        'has_recent_data',
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_municipality()
    {
        $response = $this->getJson('/api/v1/municipalities/999');

        $response->assertNotFound();
    }

    /** @test */
    public function it_can_get_municipality_weather_data()
    {
        // Crear varios registros meteorológicos
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->count(10)->create();

        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}/weather");

        $response->assertOk()
                ->assertJsonStructure([
                    'municipality' => [
                        'id',
                        'name',
                        'full_name',
                    ],
                    'current_weather',
                    'average_conditions' => [
                        'avg_temperature',
                        'avg_cloud_coverage',
                        'avg_solar_radiation',
                        'weather_readings',
                    ],
                    'solar_potential',
                ]);

        $avgConditions = $response->json('average_conditions');
        expect($avgConditions['weather_readings'])->toBe(10);
    }

    /** @test */
    public function it_can_limit_weather_data_by_days()
    {
        // Crear datos meteorológicos en diferentes fechas
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->atTime(now()->subDays(40))->create();
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->atTime(now()->subDays(15))->create();
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->atTime(now()->subDays(5))->create();

        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}/weather?days=20");

        $response->assertOk();

        $period = $response->json('period');
        expect($period['days'])->toBe(20);

        $avgConditions = $response->json('average_conditions');
        expect($avgConditions['weather_readings'])->toBe(2); // Solo los últimos 20 días
    }

    /** @test */
    public function it_enforces_maximum_days_limit()
    {
        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}/weather?days=500");

        $response->assertOk();

        $period = $response->json('period');
        expect($period['days'])->toBe(365); // Máximo 365 días
    }

    /** @test */
    public function it_handles_municipality_without_weather_data()
    {
        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}/weather");

        $response->assertOk();

        $currentWeather = $response->json('current_weather');
        expect($currentWeather)->toBeNull();

        $avgConditions = $response->json('average_conditions');
        expect($avgConditions['weather_readings'])->toBe(0);
    }

    /** @test */
    public function it_includes_current_weather_when_available()
    {
        $weather = WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->create([
            'temperature' => 22.5,
            'solar_radiation' => 750.0,
            'cloud_coverage' => 30.0,
            'timestamp' => now(),
        ]);

        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}/weather");

        $response->assertOk();

        $currentWeather = $response->json('current_weather');
        expect($currentWeather)->not->toBeNull();
        expect((float) $currentWeather['temperature'])->toBe(22.5);
        expect((float) $currentWeather['solar_radiation'])->toBe(750.0);
    }

    /** @test */
    public function it_calculates_solar_potential_correctly()
    {
        // Crear condiciones óptimas para solar
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->create([
            'solar_radiation' => 900.0,
            'cloud_coverage' => 10.0,
        ]);

        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}");

        $response->assertOk();

        $solarPotential = $response->json('solar_potential');
        expect($solarPotential)->toBeGreaterThan(70); // Debería ser alto con estas condiciones
    }

    /** @test */
    public function it_orders_municipalities_alphabetically()
    {
        // Crear municipios adicionales
        Municipality::factory()->create([
            'province_id' => $this->madridProv->id,
            'name' => 'Alcalá de Henares',
            'slug' => 'alcala-de-henares-madrid',
        ]);

        $response = $this->getJson('/api/v1/municipalities');

        $response->assertOk();

        $municipalities = $response->json();
        $names = array_column($municipalities, 'name');

        // Verificar orden alfabético
        expect($names[0])->toBe('Alcalá de Henares');
        expect($names[1])->toBe('Madrid');
        expect($names[2])->toBe('Sevilla');
    }

    /** @test */
    public function it_shows_full_name_correctly()
    {
        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}");

        $response->assertOk();

        expect($response->json('full_name'))->toBe('Madrid, Madrid');
    }

    /** @test */
    public function it_identifies_operating_status_correctly()
    {
        // Municipio operativo (con texto)
        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}");
        $response->assertOk();
        expect($response->json('is_operating'))->toBeTrue();

        // Crear municipio no operativo
        $nonOperatingMuni = Municipality::factory()->notOperating()->create([
            'province_id' => $this->madridProv->id,
        ]);

        $response = $this->getJson("/api/v1/municipalities/{$nonOperatingMuni->id}");
        $response->assertOk();
        expect($response->json('is_operating'))->toBeFalse();
    }

    /** @test */
    public function it_requires_authentication()
    {
        // Sin autenticación
        $this->withoutMiddleware();
        auth()->logout();

        $response = $this->getJson('/api/v1/municipalities');

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_includes_nested_province_and_region_data()
    {
        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}");

        $response->assertOk()
                ->assertJsonStructure([
                    'province' => [
                        'id',
                        'name',
                        'slug',
                        'region' => [
                            'id',
                            'name',
                            'slug',
                        ]
                    ]
                ]);

        $province = $response->json('province');
        expect($province['name'])->toBe('Madrid');
        expect($province['region']['name'])->toBe('Madrid');
    }

    /** @test */
    public function it_handles_recent_weather_data_flag()
    {
        // Sin datos recientes
        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}");
        $response->assertOk();
        expect($response->json('has_recent_weather'))->toBeFalse();

        // Con datos recientes (últimas 24 horas)
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->recent()->create();

        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}");
        $response->assertOk();
        expect($response->json('has_recent_weather'))->toBeTrue();
    }

    /** @test */
    public function it_calculates_peak_solar_hours()
    {
        // Crear datos con radiación solar específica
        WeatherSnapshot::factory()->forMunicipality($this->madridMuni)->create([
            'solar_radiation' => 500.0, // 0.5 horas pico
        ]);

        $response = $this->getJson("/api/v1/municipalities/{$this->madridMuni->id}");

        $response->assertOk();

        $peakSolarHours = $response->json('peak_solar_hours');
        expect($peakSolarHours)->toBe(0.5);
    }

    /** @test */
    public function it_handles_empty_municipalities_list()
    {
        Municipality::query()->delete();

        $response = $this->getJson('/api/v1/municipalities');

        $response->assertOk()
                ->assertJsonCount(0);
    }

    /** @test */
    public function it_handles_multiple_filters_combined()
    {
        // Crear municipios adicionales para hacer el filtro más interesante
        $operatingMuni = Municipality::factory()->operating()->create([
            'province_id' => $this->madridProv->id,
        ]);
        WeatherSnapshot::factory()->forMunicipality($operatingMuni)->create();

        Municipality::factory()->notOperating()->create([
            'province_id' => $this->madridProv->id,
        ]);

        $response = $this->getJson("/api/v1/municipalities?province_id={$this->madridProv->id}&operating_only=true&with_weather=true");

        $response->assertOk();

        $municipalities = $response->json();
        foreach ($municipalities as $municipality) {
            expect($municipality['is_operating'])->toBeTrue();
            expect($municipality['province']['id'])->toBe($this->madridProv->id);
        }
    }
}
