<?php

namespace Tests\Unit\Models;

use App\Models\Region;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\WeatherSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class WeatherSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private Municipality $municipality;
    private WeatherSnapshot $weather;

    protected function setUp(): void
    {
        parent::setUp();
        
        $region = Region::factory()->madrid()->create();
        $province = Province::factory()->madrid()->create(['region_id' => $region->id]);
        $this->municipality = Municipality::factory()->madrid()->create(['province_id' => $province->id]);
    }

    /** @test */
    public function it_can_create_weather_snapshot()
    {
        $weather = WeatherSnapshot::factory()->forMunicipality($this->municipality)->create([
            'temperature' => 25.5,
            'cloud_coverage' => 30.0,
            'solar_radiation' => 850.0,
        ]);

        expect($weather->temperature)->toBe('25.50');
        expect($weather->cloud_coverage)->toBe('30.00');
        expect($weather->solar_radiation)->toBe('850.00');
        expect($weather->municipality_id)->toBe($this->municipality->id);
    }

    /** @test */
    public function it_casts_values_correctly()
    {
        $weather = WeatherSnapshot::factory()->create([
            'temperature' => 25.5,
            'cloud_coverage' => 30.25,
            'solar_radiation' => 850.75,
            'timestamp' => '2024-08-16 12:00:00',
        ]);

        expect($weather->temperature)->toBeInstanceOf(\Illuminate\Contracts\Database\Eloquent\Castable::class);
        expect($weather->timestamp)->toBeInstanceOf(Carbon::class);
    }

    /** @test */
    public function it_belongs_to_municipality()
    {
        $weather = WeatherSnapshot::factory()->forMunicipality($this->municipality)->create();

        expect($weather->municipality)->toBeInstanceOf(Municipality::class);
        expect($weather->municipality->id)->toBe($this->municipality->id);
    }

    /** @test */
    public function it_can_access_province_through_municipality()
    {
        $weather = WeatherSnapshot::factory()->forMunicipality($this->municipality)->create();

        expect($weather->municipality->province)->toBeInstanceOf(Province::class);
        expect($weather->municipality->province->name)->toBe('Madrid');
    }

    /** @test */
    public function it_can_access_region_through_municipality()
    {
        $weather = WeatherSnapshot::factory()->forMunicipality($this->municipality)->create();

        expect($weather->municipality->province->region)->toBeInstanceOf(Region::class);
        expect($weather->municipality->province->region->name)->toBe('Madrid');
    }

    /** @test */
    public function it_can_scope_by_municipality()
    {
        $municipality2 = Municipality::factory()->create();
        
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->count(3)->create();
        WeatherSnapshot::factory()->forMunicipality($municipality2)->count(2)->create();

        $weatherForMuni1 = WeatherSnapshot::forMunicipality($this->municipality->id)->get();
        $weatherForMuni2 = WeatherSnapshot::forMunicipality($municipality2->id)->get();

        expect($weatherForMuni1)->toHaveCount(3);
        expect($weatherForMuni2)->toHaveCount(2);
    }

    /** @test */
    public function it_can_scope_by_date_range()
    {
        $startDate = Carbon::parse('2024-08-01');
        $endDate = Carbon::parse('2024-08-15');

        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime($startDate->copy()->addDays(5))->create();
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime($startDate->copy()->addDays(10))->create();
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime($startDate->copy()->addDays(20))->create(); // Fuera del rango

        $weatherInRange = WeatherSnapshot::inDateRange($startDate, $endDate)->get();

        expect($weatherInRange)->toHaveCount(2);
    }

    /** @test */
    public function it_can_scope_today()
    {
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(today()->setHour(12))->create();
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(yesterday())->create();

        $todayWeather = WeatherSnapshot::today()->get();

        expect($todayWeather)->toHaveCount(1);
    }

    /** @test */
    public function it_can_scope_this_week()
    {
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(now()->startOfWeek()->addDays(2))->create();
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(now()->subWeeks(1))->create();

        $thisWeekWeather = WeatherSnapshot::thisWeek()->get();

        expect($thisWeekWeather)->toHaveCount(1);
    }

    /** @test */
    public function it_can_scope_this_month()
    {
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(now()->startOfMonth()->addDays(5))->create();
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(now()->subMonths(1))->create();

        $thisMonthWeather = WeatherSnapshot::thisMonth()->get();

        expect($thisMonthWeather)->toHaveCount(1);
    }

    /** @test */
    public function it_can_scope_with_good_solar_conditions()
    {
        // Condiciones buenas (>=500 W/m², <=50% nubes)
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->create([
            'solar_radiation' => 600.0,
            'cloud_coverage' => 30.0,
        ]);

        // Condiciones pobres
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->create([
            'solar_radiation' => 300.0,
            'cloud_coverage' => 80.0,
        ]);

        $goodConditions = WeatherSnapshot::withGoodSolarConditions()->get();

        expect($goodConditions)->toHaveCount(1);
    }

    /** @test */
    public function it_can_scope_optimal_solar_conditions()
    {
        // Condiciones óptimas (>=800 W/m², <=20% nubes)
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->create([
            'solar_radiation' => 900.0,
            'cloud_coverage' => 15.0,
        ]);

        // Condiciones buenas pero no óptimas
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->create([
            'solar_radiation' => 600.0,
            'cloud_coverage' => 30.0,
        ]);

        $optimalConditions = WeatherSnapshot::optimalSolarConditions()->get();

        expect($optimalConditions)->toHaveCount(1);
    }

    /** @test */
    public function it_can_scope_recent_readings()
    {
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(now()->subHours(2))->create();
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->atTime(now()->subHours(30))->create();

        $recentReadings = WeatherSnapshot::recentReadings(24)->get();

        expect($recentReadings)->toHaveCount(1);
    }

    /** @test */
    public function it_provides_display_attributes()
    {
        $weather = WeatherSnapshot::factory()->create([
            'temperature' => 25.5,
            'cloud_coverage' => 30.0,
            'solar_radiation' => 850.0,
        ]);

        expect($weather->temperature_display)->toBe('25.50°C');
        expect($weather->cloud_coverage_display)->toBe('30.00%');
        expect($weather->solar_radiation_display)->toBe('850.00 W/m²');
    }

    /** @test */
    public function it_handles_null_values_in_display_attributes()
    {
        $weather = WeatherSnapshot::factory()->create([
            'temperature' => null,
            'cloud_coverage' => null,
            'solar_radiation' => null,
        ]);

        expect($weather->temperature_display)->toBe('N/A');
        expect($weather->cloud_coverage_display)->toBe('N/A');
        expect($weather->solar_radiation_display)->toBe('N/A');
    }

    /** @test */
    public function it_calculates_age_correctly()
    {
        $weather = WeatherSnapshot::factory()->create([
            'timestamp' => now()->subHours(2),
        ]);

        expect($weather->age)->toContain('hours ago');
    }

    /** @test */
    public function it_identifies_recent_snapshots()
    {
        $recentWeather = WeatherSnapshot::factory()->create([
            'timestamp' => now()->subHours(2),
        ]);

        $oldWeather = WeatherSnapshot::factory()->create([
            'timestamp' => now()->subHours(10),
        ]);

        expect($recentWeather->is_recent)->toBeTrue();
        expect($oldWeather->is_recent)->toBeFalse();
    }

    /** @test */
    public function it_identifies_optimal_solar_conditions()
    {
        $optimalWeather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 900.0,
            'cloud_coverage' => 15.0,
        ]);

        $poorWeather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 300.0,
            'cloud_coverage' => 80.0,
        ]);

        expect($optimalWeather->isOptimalForSolar())->toBeTrue();
        expect($poorWeather->isOptimalForSolar())->toBeFalse();
    }

    /** @test */
    public function it_identifies_good_solar_conditions()
    {
        $goodWeather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 600.0,
            'cloud_coverage' => 40.0,
        ]);

        $poorWeather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 200.0,
            'cloud_coverage' => 80.0,
        ]);

        expect($goodWeather->isGoodForSolar())->toBeTrue();
        expect($poorWeather->isGoodForSolar())->toBeFalse();
    }

    /** @test */
    public function it_rates_solar_conditions()
    {
        $excellentWeather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 950.0,
            'cloud_coverage' => 10.0,
        ]);

        $goodWeather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 650.0,
            'cloud_coverage' => 35.0,
        ]);

        $fairWeather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 400.0,
            'cloud_coverage' => 60.0,
        ]);

        $poorWeather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 150.0,
            'cloud_coverage' => 90.0,
        ]);

        expect($excellentWeather->getSolarConditionRating())->toBe('excellent');
        expect($goodWeather->getSolarConditionRating())->toBe('good');
        expect($fairWeather->getSolarConditionRating())->toBe('fair');
        expect($poorWeather->getSolarConditionRating())->toBe('poor');
    }

    /** @test */
    public function it_categorizes_temperature_ranges()
    {
        $veryCold = WeatherSnapshot::factory()->create(['temperature' => -5.0]);
        $cold = WeatherSnapshot::factory()->create(['temperature' => 5.0]);
        $cool = WeatherSnapshot::factory()->create(['temperature' => 15.0]);
        $warm = WeatherSnapshot::factory()->create(['temperature' => 25.0]);
        $hot = WeatherSnapshot::factory()->create(['temperature' => 35.0]);
        $veryHot = WeatherSnapshot::factory()->create(['temperature' => 45.0]);

        expect($veryCold->getTemperatureRange())->toBe('very_cold');
        expect($cold->getTemperatureRange())->toBe('cold');
        expect($cool->getTemperatureRange())->toBe('cool');
        expect($warm->getTemperatureRange())->toBe('warm');
        expect($hot->getTemperatureRange())->toBe('hot');
        expect($veryHot->getTemperatureRange())->toBe('very_hot');
    }

    /** @test */
    public function it_calculates_estimated_solar_generation()
    {
        $weather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 800.0, // 0.8 * 1.0 * 0.75 = 0.6 kWh
        ]);

        $generation = $weather->getEstimatedSolarGeneration(1.0);

        expect($generation)->toBe(0.6);
    }

    /** @test */
    public function it_calculates_estimated_solar_generation_for_different_system_sizes()
    {
        $weather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 1000.0, // Condiciones perfectas
        ]);

        $generation5kw = $weather->getEstimatedSolarGeneration(5.0);
        $generation10kw = $weather->getEstimatedSolarGeneration(10.0);

        expect($generation5kw)->toBe(3.75); // 1.0 * 5.0 * 0.75
        expect($generation10kw)->toBe(7.5);  // 1.0 * 10.0 * 0.75
    }

    /** @test */
    public function it_handles_zero_solar_radiation()
    {
        $weather = WeatherSnapshot::factory()->create([
            'solar_radiation' => 0.0,
        ]);

        expect($weather->getEstimatedSolarGeneration())->toBe(0.0);
        expect($weather->isOptimalForSolar())->toBeFalse();
        expect($weather->isGoodForSolar())->toBeFalse();
        expect($weather->getSolarConditionRating())->toBe('poor');
    }

    /** @test */
    public function it_provides_comprehensive_condition_summary()
    {
        $weather = WeatherSnapshot::factory()->create([
            'temperature' => 25.5,
            'cloud_coverage' => 20.0,
            'solar_radiation' => 850.0,
            'timestamp' => now()->subHours(1),
        ]);

        $summary = $weather->getConditionSummary();

        expect($summary)->toHaveKey('temperature');
        expect($summary)->toHaveKey('solar');
        expect($summary)->toHaveKey('clouds');
        expect($summary)->toHaveKey('timestamp');

        expect($summary['temperature']['value'])->toBe('25.50');
        expect($summary['temperature']['range'])->toBe('warm');
        expect($summary['solar']['rating'])->toBe('excellent');
        expect($summary['solar']['optimal'])->toBeTrue();
        expect($summary['timestamp']['is_recent'])->toBeTrue();
    }

    /** @test */
    public function it_can_get_regional_statistics()
    {
        $region = Region::factory()->create();
        $province = Province::factory()->create(['region_id' => $region->id]);
        $municipality1 = Municipality::factory()->create(['province_id' => $province->id]);
        $municipality2 = Municipality::factory()->create(['province_id' => $province->id]);

        // Crear datos meteorológicos para ambos municipios
        WeatherSnapshot::factory()->forMunicipality($municipality1)->count(3)->create();
        WeatherSnapshot::factory()->forMunicipality($municipality2)->count(2)->create();

        $stats = WeatherSnapshot::getRegionalStats($region->id);

        expect($stats['total_readings'])->toBe(5);
        expect($stats)->toHaveKey('avg_temperature');
        expect($stats)->toHaveKey('avg_solar_radiation');
        expect($stats)->toHaveKey('optimal_solar_hours');
    }

    /** @test */
    public function it_can_filter_regional_stats_by_date()
    {
        $region = Region::factory()->create();
        $province = Province::factory()->create(['region_id' => $region->id]);
        $municipality = Municipality::factory()->create(['province_id' => $province->id]);

        // Crear datos en diferentes fechas
        WeatherSnapshot::factory()->forMunicipality($municipality)->atTime(now()->subDays(10))->create();
        WeatherSnapshot::factory()->forMunicipality($municipality)->atTime(now()->subDays(5))->create();
        WeatherSnapshot::factory()->forMunicipality($municipality)->atTime(now())->create();

        $from = now()->subDays(7);
        $to = now();

        $stats = WeatherSnapshot::getRegionalStats($region->id, $from, $to);

        expect($stats['total_readings'])->toBe(2); // Solo los últimos 7 días
    }

    /** @test */
    public function it_enforces_unique_constraint_on_municipality_and_timestamp()
    {
        $timestamp = now();
        
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->create([
            'timestamp' => $timestamp,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        WeatherSnapshot::factory()->forMunicipality($this->municipality)->create([
            'timestamp' => $timestamp,
        ]);
    }
}
