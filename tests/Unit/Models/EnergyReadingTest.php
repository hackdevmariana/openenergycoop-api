<?php

namespace Tests\Unit\Models;

use App\Models\EnergyReading;
use App\Models\EnergyMeter;
use App\Models\EnergyInstallation;
use App\Models\ConsumptionPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyReadingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'reading_number',
            'meter_id',
            'installation_id',
            'consumption_point_id',
            'customer_id',
            'reading_type',
            'reading_source',
            'reading_status',
            'reading_timestamp',
            'reading_period',
            'reading_value',
            'reading_unit',
            'previous_reading_value',
            'consumption_value',
            'consumption_unit',
            'demand_value',
            'demand_unit',
            'power_factor',
            'voltage_value',
            'voltage_unit',
            'current_value',
            'current_unit',
            'frequency_value',
            'frequency_unit',
            'temperature',
            'temperature_unit',
            'humidity',
            'humidity_unit',
            'quality_score',
            'quality_notes',
            'validation_notes',
            'correction_notes',
            'raw_data',
            'processed_data',
            'alarms',
            'events',
            'tags',
            'read_by',
            'validated_by',
            'validated_at',
            'corrected_by',
            'corrected_at',
            'notes',
        ];

        $this->assertEquals($fillable, (new EnergyReading())->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $casts = [
            'reading_timestamp' => 'datetime',
            'validated_at' => 'datetime',
            'corrected_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'reading_value' => 'decimal:4',
            'previous_reading_value' => 'decimal:4',
            'consumption_value' => 'decimal:4',
            'demand_value' => 'decimal:4',
            'power_factor' => 'decimal:2',
            'voltage_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'frequency_value' => 'decimal:2',
            'temperature' => 'decimal:2',
            'humidity' => 'decimal:2',
            'quality_score' => 'decimal:2',
            'raw_data' => 'array',
            'processed_data' => 'array',
            'alarms' => 'array',
            'events' => 'array',
            'tags' => 'array',
        ];

        $this->assertEquals($casts, (new EnergyReading())->getCasts());
    }

    /** @test */
    public function it_has_correct_reading_types()
    {
        $types = EnergyReading::getReadingTypes();
        
        $this->assertIsArray($types);
        $this->assertArrayHasKey('instantaneous', $types);
        $this->assertArrayHasKey('cumulative', $types);
        $this->assertArrayHasKey('demand', $types);
        $this->assertArrayHasKey('energy', $types);
        $this->assertArrayHasKey('power', $types);
    }

    /** @test */
    public function it_has_correct_reading_sources()
    {
        $sources = EnergyReading::getReadingSources();
        
        $this->assertIsArray($sources);
        $this->assertArrayHasKey('automatic', $sources);
        $this->assertArrayHasKey('manual', $sources);
        $this->assertArrayHasKey('estimated', $sources);
        $this->assertArrayHasKey('calculated', $sources);
        $this->assertArrayHasKey('imported', $sources);
    }

    /** @test */
    public function it_has_correct_reading_statuses()
    {
        $statuses = EnergyReading::getReadingStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('pending', $statuses);
        $this->assertArrayHasKey('valid', $statuses);
        $this->assertArrayHasKey('invalid', $statuses);
        $this->assertArrayHasKey('corrected', $statuses);
        $this->assertArrayHasKey('flagged', $statuses);
    }

    /** @test */
    public function it_belongs_to_meter()
    {
        $meter = EnergyMeter::factory()->create();
        $reading = EnergyReading::factory()->create(['meter_id' => $meter->id]);

        $this->assertInstanceOf(EnergyMeter::class, $reading->meter);
        $this->assertEquals($meter->id, $reading->meter->id);
    }

    /** @test */
    public function it_belongs_to_installation()
    {
        $installation = EnergyInstallation::factory()->create();
        $reading = EnergyReading::factory()->create(['installation_id' => $installation->id]);

        $this->assertInstanceOf(EnergyInstallation::class, $reading->installation);
        $this->assertEquals($installation->id, $reading->installation->id);
    }

    /** @test */
    public function it_belongs_to_consumption_point()
    {
        $point = ConsumptionPoint::factory()->create();
        $reading = EnergyReading::factory()->create(['consumption_point_id' => $point->id]);

        $this->assertInstanceOf(ConsumptionPoint::class, $reading->consumptionPoint);
        $this->assertEquals($point->id, $reading->consumptionPoint->id);
    }

    /** @test */
    public function it_belongs_to_customer()
    {
        $customer = User::factory()->create();
        $reading = EnergyReading::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(User::class, $reading->customer);
        $this->assertEquals($customer->id, $reading->customer->id);
    }

    /** @test */
    public function it_belongs_to_read_by_user()
    {
        $user = User::factory()->create();
        $reading = EnergyReading::factory()->create(['read_by' => $user->id]);

        $this->assertInstanceOf(User::class, $reading->readBy);
        $this->assertEquals($user->id, $reading->readBy->id);
    }

    /** @test */
    public function it_belongs_to_validated_by_user()
    {
        $user = User::factory()->create();
        $reading = EnergyReading::factory()->create(['validated_by' => $user->id]);

        $this->assertInstanceOf(User::class, $reading->validatedBy);
        $this->assertEquals($user->id, $reading->validatedBy->id);
    }

    /** @test */
    public function it_belongs_to_corrected_by_user()
    {
        $user = User::factory()->create();
        $reading = EnergyReading::factory()->create(['corrected_by' => $user->id]);

        $this->assertInstanceOf(User::class, $reading->correctedBy);
        $this->assertEquals($user->id, $reading->correctedBy->id);
    }

    /** @test */
    public function it_belongs_to_created_by_user()
    {
        $user = User::factory()->create();
        $reading = EnergyReading::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $reading->createdBy);
        $this->assertEquals($user->id, $reading->createdBy->id);
    }

    /** @test */
    public function it_has_valid_status_scope()
    {
        EnergyReading::factory()->create(['reading_status' => 'valid']);
        EnergyReading::factory()->create(['reading_status' => 'invalid']);
        EnergyReading::factory()->create(['reading_status' => 'pending']);

        $validReadings = EnergyReading::valid()->get();

        $this->assertEquals(1, $validReadings->count());
        $this->assertEquals('valid', $validReadings->first()->reading_status);
    }

    /** @test */
    public function it_has_by_type_scope()
    {
        EnergyReading::factory()->create(['reading_type' => 'instantaneous']);
        EnergyReading::factory()->create(['reading_type' => 'cumulative']);
        EnergyReading::factory()->create(['reading_type' => 'instantaneous']);

        $instantaneousReadings = EnergyReading::byType('instantaneous')->get();

        $this->assertEquals(2, $instantaneousReadings->count());
        $this->assertEquals('instantaneous', $instantaneousReadings->first()->reading_type);
    }

    /** @test */
    public function it_has_by_source_scope()
    {
        EnergyReading::factory()->create(['reading_source' => 'automatic']);
        EnergyReading::factory()->create(['reading_source' => 'manual']);
        EnergyReading::factory()->create(['reading_source' => 'automatic']);

        $automaticReadings = EnergyReading::bySource('automatic')->get();

        $this->assertEquals(2, $automaticReadings->count());
        $this->assertEquals('automatic', $automaticReadings->first()->reading_source);
    }

    /** @test */
    public function it_has_by_meter_scope()
    {
        $meter1 = EnergyMeter::factory()->create();
        $meter2 = EnergyMeter::factory()->create();
        
        EnergyReading::factory()->create(['meter_id' => $meter1->id]);
        EnergyReading::factory()->create(['meter_id' => $meter2->id]);
        EnergyReading::factory()->create(['meter_id' => $meter1->id]);

        $meter1Readings = EnergyReading::byMeter($meter1->id)->get();

        $this->assertEquals(2, $meter1Readings->count());
        $this->assertEquals($meter1->id, $meter1Readings->first()->meter_id);
    }

    /** @test */
    public function it_has_by_customer_scope()
    {
        $customer1 = User::factory()->create();
        $customer2 = User::factory()->create();
        
        EnergyReading::factory()->create(['customer_id' => $customer1->id]);
        EnergyReading::factory()->create(['customer_id' => $customer2->id]);
        EnergyReading::factory()->create(['customer_id' => $customer1->id]);

        $customer1Readings = EnergyReading::byCustomer($customer1->id)->get();

        $this->assertEquals(2, $customer1Readings->count());
        $this->assertEquals($customer1->id, $customer1Readings->first()->customer_id);
    }

    /** @test */
    public function it_has_high_quality_scope()
    {
        EnergyReading::factory()->create(['quality_score' => 95.0]);
        EnergyReading::factory()->create(['quality_score' => 85.0]);
        EnergyReading::factory()->create(['quality_score' => 75.0]);

        $highQualityReadings = EnergyReading::highQuality()->get();

        $this->assertEquals(1, $highQualityReadings->count());
        $this->assertEquals(95.0, $highQualityReadings->first()->quality_score);
    }

    /** @test */
    public function it_has_today_scope()
    {
        EnergyReading::factory()->create(['reading_timestamp' => now()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subDay()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subDays(2)]);

        $todayReadings = EnergyReading::today()->get();

        $this->assertEquals(1, $todayReadings->count());
        $this->assertTrue($todayReadings->first()->reading_timestamp->isToday());
    }

    /** @test */
    public function it_has_this_month_scope()
    {
        EnergyReading::factory()->create(['reading_timestamp' => now()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subMonth()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subMonths(2)]);

        $thisMonthReadings = EnergyReading::thisMonth()->get();

        $this->assertEquals(1, $thisMonthReadings->count());
        $this->assertTrue($thisMonthReadings->first()->reading_timestamp->isCurrentMonth());
    }

    /** @test */
    public function it_has_date_range_scope()
    {
        $startDate = now()->subDays(5);
        $endDate = now()->subDays(2);
        
        EnergyReading::factory()->create(['reading_timestamp' => now()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subDays(3)]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subDays(6)]);

        $rangeReadings = EnergyReading::dateRange($startDate, $endDate)->get();

        $this->assertEquals(1, $rangeReadings->count());
        $this->assertTrue($rangeReadings->first()->reading_timestamp->between($startDate, $endDate));
    }

    /** @test */
    public function it_has_quality_range_scope()
    {
        EnergyReading::factory()->create(['quality_score' => 90.0]);
        EnergyReading::factory()->create(['quality_score' => 80.0]);
        EnergyReading::factory()->create(['quality_score' => 70.0]);

        $qualityRangeReadings = EnergyReading::qualityRange(75.0, 85.0)->get();

        $this->assertEquals(1, $qualityRangeReadings->count());
        $this->assertEquals(80.0, $qualityRangeReadings->first()->quality_score);
    }

    /** @test */
    public function it_checks_if_valid()
    {
        $validReading = EnergyReading::factory()->create(['reading_status' => 'valid']);
        $invalidReading = EnergyReading::factory()->create(['reading_status' => 'invalid']);

        $this->assertTrue($validReading->isValid());
        $this->assertFalse($invalidReading->isValid());
    }

    /** @test */
    public function it_checks_if_corrected()
    {
        $correctedReading = EnergyReading::factory()->create(['corrected_by' => 1]);
        $uncorrectedReading = EnergyReading::factory()->create(['corrected_by' => null]);

        $this->assertTrue($correctedReading->isCorrected());
        $this->assertFalse($uncorrectedReading->isCorrected());
    }

    /** @test */
    public function it_checks_if_high_quality()
    {
        $highQualityReading = EnergyReading::factory()->create(['quality_score' => 95.0]);
        $lowQualityReading = EnergyReading::factory()->create(['quality_score' => 75.0]);

        $this->assertTrue($highQualityReading->isHighQuality());
        $this->assertFalse($lowQualityReading->isHighQuality());
    }

    /** @test */
    public function it_gets_hour_of_day()
    {
        $reading = EnergyReading::factory()->create(['reading_timestamp' => '2024-01-15 14:30:00']);

        $this->assertEquals(14, $reading->getHourOfDay());
    }

    /** @test */
    public function it_gets_day_of_week()
    {
        $reading = EnergyReading::factory()->create(['reading_timestamp' => '2024-01-15 14:30:00']); // Monday

        $this->assertEquals(1, $reading->getDayOfWeek());
    }

    /** @test */
    public function it_gets_season()
    {
        $winterReading = EnergyReading::factory()->create(['reading_timestamp' => '2024-01-15 14:30:00']);
        $summerReading = EnergyReading::factory()->create(['reading_timestamp' => '2024-07-15 14:30:00']);

        $this->assertEquals('winter', $winterReading->getSeason());
        $this->assertEquals('summer', $summerReading->getSeason());
    }

    /** @test */
    public function it_gets_consumption_delta()
    {
        $reading = EnergyReading::factory()->create([
            'reading_value' => 100.0,
            'previous_reading_value' => 80.0
        ]);

        $this->assertEquals(20.0, $reading->getConsumptionDelta());
    }

    /** @test */
    public function it_gets_formatted_reading_value()
    {
        $reading = EnergyReading::factory()->create([
            'reading_value' => 123.4567,
            'reading_unit' => 'kWh'
        ]);

        $this->assertEquals('123.46 kWh', $reading->getFormattedReadingValue());
    }

    /** @test */
    public function it_gets_formatted_consumption_value()
    {
        $reading = EnergyReading::factory()->create([
            'consumption_value' => 45.6789,
            'consumption_unit' => 'kWh'
        ]);

        $this->assertEquals('45.68 kWh', $reading->getFormattedConsumptionValue());
    }

    /** @test */
    public function it_gets_formatted_demand_value()
    {
        $reading = EnergyReading::factory()->create([
            'demand_value' => 12.3456,
            'demand_unit' => 'kW'
        ]);

        $this->assertEquals('12.35 kW', $reading->getFormattedDemandValue());
    }

    /** @test */
    public function it_gets_formatted_power_factor()
    {
        $reading = EnergyReading::factory()->create(['power_factor' => 0.8567]);

        $this->assertEquals('0.86', $reading->getFormattedPowerFactor());
    }

    /** @test */
    public function it_gets_formatted_quality_score()
    {
        $reading = EnergyReading::factory()->create(['quality_score' => 87.6543]);

        $this->assertEquals('87.65%', $reading->getFormattedQualityScore());
    }

    /** @test */
    public function it_gets_formatted_temperature()
    {
        $reading = EnergyReading::factory()->create([
            'temperature' => 23.4567,
            'temperature_unit' => '°C'
        ]);

        $this->assertEquals('23.46°C', $reading->getFormattedTemperature());
    }

    /** @test */
    public function it_gets_formatted_humidity()
    {
        $reading = EnergyReading::factory()->create([
            'humidity' => 65.4321,
            'humidity_unit' => '%'
        ]);

        $this->assertEquals('65.43%', $reading->getFormattedHumidity());
    }

    /** @test */
    public function it_gets_status_badge_class()
    {
        $validReading = EnergyReading::factory()->create(['reading_status' => 'valid']);
        $invalidReading = EnergyReading::factory()->create(['reading_status' => 'invalid']);

        $this->assertEquals('badge-success', $validReading->getStatusBadgeClass());
        $this->assertEquals('badge-danger', $invalidReading->getStatusBadgeClass());
    }

    /** @test */
    public function it_gets_type_badge_class()
    {
        $instantaneousReading = EnergyReading::factory()->create(['reading_type' => 'instantaneous']);
        $cumulativeReading = EnergyReading::factory()->create(['reading_type' => 'cumulative']);

        $this->assertEquals('badge-info', $instantaneousReading->getTypeBadgeClass());
        $this->assertEquals('badge-primary', $cumulativeReading->getTypeBadgeClass());
    }

    /** @test */
    public function it_gets_source_badge_class()
    {
        $automaticReading = EnergyReading::factory()->create(['reading_source' => 'automatic']);
        $manualReading = EnergyReading::factory()->create(['reading_source' => 'manual']);

        $this->assertEquals('badge-success', $automaticReading->getSourceBadgeClass());
        $this->assertEquals('badge-warning', $manualReading->getSourceBadgeClass());
    }

    /** @test */
    public function it_gets_quality_badge_class()
    {
        $highQualityReading = EnergyReading::factory()->create(['quality_score' => 95.0]);
        $mediumQualityReading = EnergyReading::factory()->create(['quality_score' => 80.0]);
        $lowQualityReading = EnergyReading::factory()->create(['quality_score' => 60.0]);

        $this->assertEquals('badge-success', $highQualityReading->getQualityBadgeClass());
        $this->assertEquals('badge-warning', $mediumQualityReading->getQualityBadgeClass());
        $this->assertEquals('badge-danger', $lowQualityReading->getQualityBadgeClass());
    }

    /** @test */
    public function it_gets_formatted_reading_type()
    {
        $reading = EnergyReading::factory()->create(['reading_type' => 'instantaneous']);

        $this->assertEquals('Instantánea', $reading->getFormattedReadingType());
    }

    /** @test */
    public function it_gets_formatted_reading_source()
    {
        $reading = EnergyReading::factory()->create(['reading_source' => 'automatic']);

        $this->assertEquals('Automática', $reading->getFormattedReadingSource());
    }

    /** @test */
    public function it_gets_formatted_reading_status()
    {
        $reading = EnergyReading::factory()->create(['reading_status' => 'valid']);

        $this->assertEquals('Válida', $reading->getFormattedReadingStatus());
    }

    /** @test */
    public function it_gets_summary_statistics()
    {
        EnergyReading::factory()->create(['reading_value' => 100.0, 'quality_score' => 90.0]);
        EnergyReading::factory()->create(['reading_value' => 200.0, 'quality_score' => 85.0]);
        EnergyReading::factory()->create(['reading_value' => 150.0, 'quality_score' => 95.0]);

        $stats = EnergyReading::getSummaryStatistics();

        $this->assertEquals(3, $stats['total_readings']);
        $this->assertEquals(450.0, $stats['total_value']);
        $this->assertEquals(150.0, $stats['average_value']);
        $this->assertEquals(90.0, $stats['average_quality']);
    }

    /** @test */
    public function it_gets_readings_by_period()
    {
        EnergyReading::factory()->create(['reading_timestamp' => now()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subDay()]);
        EnergyReading::factory()->create(['reading_timestamp' => now()->subWeek()]);

        $dailyReadings = EnergyReading::getReadingsByPeriod('day');
        $weeklyReadings = EnergyReading::getReadingsByPeriod('week');

        $this->assertEquals(1, $dailyReadings->count());
        $this->assertEquals(2, $weeklyReadings->count());
    }
}
