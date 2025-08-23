<?php

namespace Tests\Unit\Models;

use App\Models\EnergyForecast;
use App\Models\User;
use App\Models\EnergySource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyForecastTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $forecast = new EnergyForecast();
        $fillable = $forecast->getFillable();
        
        $this->assertContains('forecast_number', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('forecast_type', $fillable);
        $this->assertContains('forecast_horizon', $fillable);
        $this->assertContains('forecast_method', $fillable);
    }

    public function test_casts()
    {
        $forecast = new EnergyForecast();
        $casts = $forecast->getCasts();
        
        $this->assertEquals('decimal:2', $casts['accuracy_score']);
        $this->assertEquals('datetime', $casts['forecast_start_time']);
        $this->assertEquals('array', $casts['forecast_data']);
        $this->assertEquals('array', $casts['tags']);
    }

    public function test_static_enum_methods()
    {
        $types = EnergyForecast::getForecastTypes();
        $this->assertIsArray($types);
        $this->assertArrayHasKey('demand', $types);
        $this->assertArrayHasKey('generation', $types);

        $horizons = EnergyForecast::getForecastHorizons();
        $this->assertIsArray($horizons);
        $this->assertArrayHasKey('hourly', $horizons);
        $this->assertArrayHasKey('daily', $horizons);

        $methods = EnergyForecast::getForecastMethods();
        $this->assertIsArray($methods);
        $this->assertArrayHasKey('statistical', $methods);
        $this->assertArrayHasKey('machine_learning', $methods);

        $statuses = EnergyForecast::getForecastStatuses();
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('draft', $statuses);
        $this->assertArrayHasKey('active', $statuses);

        $accuracyLevels = EnergyForecast::getAccuracyLevels();
        $this->assertIsArray($accuracyLevels);
        $this->assertArrayHasKey('low', $accuracyLevels);
        $this->assertArrayHasKey('high', $accuracyLevels);
    }

    public function test_relationships()
    {
        $forecast = EnergyForecast::factory()->create();
        
        $this->assertInstanceOf(EnergySource::class, $forecast->source);
        $this->assertInstanceOf(User::class, $forecast->createdBy);
        $this->assertInstanceOf(User::class, $forecast->approvedBy);
        $this->assertInstanceOf(User::class, $forecast->validatedBy);
    }

    public function test_scopes()
    {
        EnergyForecast::factory()->create(['forecast_type' => 'demand']);
        EnergyForecast::factory()->create(['forecast_type' => 'generation']);
        
        $demandForecasts = EnergyForecast::demand()->get();
        $this->assertEquals(1, $demandForecasts->count());
        $this->assertEquals('demand', $demandForecasts->first()->forecast_type);

        $activeForecasts = EnergyForecast::active()->get();
        $this->assertEquals(0, $activeForecasts->count());

        $draftForecasts = EnergyForecast::draft()->get();
        $this->assertEquals(2, $draftForecasts->count());
    }

    public function test_boolean_status_checks()
    {
        $forecast = EnergyForecast::factory()->create([
            'forecast_type' => 'demand',
            'forecast_horizon' => 'daily',
            'forecast_method' => 'statistical',
            'accuracy_level' => 'high'
        ]);

        $this->assertTrue($forecast->isDemand());
        $this->assertFalse($forecast->isGeneration());
        $this->assertTrue($forecast->isDaily());
        $this->assertTrue($forecast->isStatistical());
        $this->assertTrue($forecast->isHighAccuracy());
        $this->assertFalse($forecast->isLowAccuracy());
    }

    public function test_calculation_methods()
    {
        $forecast = EnergyForecast::factory()->create([
            'forecast_start_time' => now(),
            'forecast_end_time' => now()->addHours(24),
            'expiry_time' => now()->addDays(7)
        ]);

        $duration = $forecast->getForecastDuration();
        $this->assertEquals(24, $duration);

        $timeToExpiry = $forecast->getTimeToExpiry();
        $this->assertGreaterThan(0, $timeToExpiry);

        $this->assertFalse($forecast->isExpired());
        $this->assertFalse($forecast->isExpiringSoon());
    }

    public function test_formatting_methods()
    {
        $forecast = EnergyForecast::factory()->create([
            'forecast_type' => 'demand',
            'forecast_horizon' => 'daily',
            'accuracy_score' => 85.5,
            'confidence_level' => 90.0
        ]);

        $this->assertEquals('Demanda', $forecast->getFormattedForecastType());
        $this->assertEquals('Diario', $forecast->getFormattedForecastHorizon());
        $this->assertEquals('85.50%', $forecast->getFormattedAccuracyScore());
        $this->assertEquals('90.00%', $forecast->getFormattedConfidenceLevel());
    }

    public function test_badge_classes()
    {
        $forecast = EnergyForecast::factory()->create([
            'forecast_status' => 'active',
            'forecast_type' => 'demand',
            'accuracy_level' => 'high'
        ]);

        $this->assertStringContainsString('bg-green-100', $forecast->getForecastStatusBadgeClass());
        $this->assertStringContainsString('bg-blue-100', $forecast->getForecastTypeBadgeClass());
        $this->assertStringContainsString('bg-blue-100', $forecast->getAccuracyLevelBadgeClass());
    }

    public function test_confidence_interval_methods()
    {
        $forecast = EnergyForecast::factory()->create([
            'confidence_interval_lower' => 80.0,
            'confidence_interval_upper' => 120.0
        ]);

        $interval = $forecast->getConfidenceInterval();
        $this->assertEquals(80.0, $interval['lower']);
        $this->assertEquals(120.0, $interval['upper']);

        $range = $forecast->getConfidenceRange();
        $this->assertEquals(40.0, $range);
    }

    public function test_data_period_methods()
    {
        $forecast = EnergyForecast::factory()->create([
            'forecast_data' => ['period1' => ['value' => 100]],
            'baseline_data' => ['period1' => ['value' => 90]]
        ]);

        $forecastData = $forecast->getForecastDataForPeriod('period1');
        $this->assertEquals(['value' => 100], $forecastData);

        $baselineData = $forecast->getBaselineDataForPeriod('period1');
        $this->assertEquals(['value' => 90], $baselineData);
    }

    public function test_term_classification()
    {
        $hourlyForecast = EnergyForecast::factory()->create(['forecast_horizon' => 'hourly']);
        $monthlyForecast = EnergyForecast::factory()->create(['forecast_horizon' => 'monthly']);
        $yearlyForecast = EnergyForecast::factory()->create(['forecast_horizon' => 'yearly']);

        $this->assertTrue($hourlyForecast->isShortTerm());
        $this->assertTrue($monthlyForecast->isMediumTerm());
        $this->assertTrue($yearlyForecast->isLongTermHorizon());
    }

    public function test_approval_and_validation_status()
    {
        $forecast = EnergyForecast::factory()->create([
            'approved_at' => null,
            'validated_at' => null
        ]);

        $this->assertFalse($forecast->isApproved());
        $this->assertFalse($forecast->isValidated());

        $forecast->update(['approved_at' => now()]);
        $this->assertTrue($forecast->isApproved());

        $forecast->update(['validated_at' => now()]);
        $this->assertTrue($forecast->isValidated());
    }
}
