<?php

namespace Tests\Unit\Unit\Models;

use Tests\TestCase;
use App\Models\ImpactMetrics;
use App\Models\User;
use App\Models\PlantGroup;
use Illuminate\Foundation\Testing\WithFaker;

class ImpactMetricsTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'total_kwh_produced',
            'total_co2_avoided_kg',
            'plant_group_id',
            'generated_at'
        ];

        $this->assertEquals($fillable, (new ImpactMetrics())->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $metrics = new ImpactMetrics();
        
        $this->assertTrue($metrics->hasCast('total_kwh_produced', 'decimal'));
        $this->assertTrue($metrics->hasCast('total_co2_avoided_kg', 'decimal'));
        $this->assertTrue($metrics->hasCast('generated_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('created_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('updated_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('deleted_at', 'datetime'));
    }

    /** @test */
    public function it_has_correct_dates()
    {
        $metrics = new ImpactMetrics();
        
        // En Laravel 10+, las fechas se manejan automáticamente a través de $casts
        // Solo verificamos que los campos estén en $casts como datetime
        $this->assertTrue($metrics->hasCast('generated_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('created_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('updated_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('deleted_at', 'datetime'));
    }

    /** @test */
    public function it_can_calculate_co2_avoided()
    {
        $metrics = new ImpactMetrics();
        
        $co2Avoided = $metrics->calculateCo2Avoided(1000, 0.6);
        
        $this->assertEquals(600, $co2Avoided);
    }

    /** @test */
    public function it_can_calculate_co2_avoided_with_default_factor()
    {
        $metrics = new ImpactMetrics();
        
        $co2Avoided = $metrics->calculateCo2Avoided(1000);
        
        $this->assertEquals(500, $co2Avoided); // 0.5 por defecto
    }

    /** @test */
    public function it_has_formatted_kwh_accessor()
    {
        $metrics = new ImpactMetrics();
        $metrics->total_kwh_produced = 1234.56;

        $this->assertEquals('1,234.56 kWh', $metrics->formatted_kwh);
    }

    /** @test */
    public function it_has_formatted_co2_accessor()
    {
        $metrics = new ImpactMetrics();
        $metrics->total_co2_avoided_kg = 567.89;

        $this->assertEquals('567.89 kg CO₂', $metrics->formatted_co2);
    }

    /** @test */
    public function it_has_formatted_date_accessor()
    {
        $metrics = new ImpactMetrics();
        $metrics->generated_at = '2024-01-15 10:30:00';

        $this->assertEquals('15/01/2024 10:30', $metrics->formatted_date);
    }

    /** @test */
    public function it_has_is_global_accessor()
    {
        $globalMetrics = new ImpactMetrics();
        $globalMetrics->user_id = null;

        $individualMetrics = new ImpactMetrics();
        $individualMetrics->user_id = 1;

        $this->assertTrue($globalMetrics->is_global);
        $this->assertFalse($individualMetrics->is_global);
    }

    /** @test */
    public function it_has_is_individual_accessor()
    {
        $globalMetrics = new ImpactMetrics();
        $globalMetrics->user_id = null;

        $individualMetrics = new ImpactMetrics();
        $individualMetrics->user_id = 1;

        $this->assertFalse($globalMetrics->is_individual);
        $this->assertTrue($individualMetrics->is_individual);
    }

    /** @test */
    public function it_has_efficiency_accessor()
    {
        $metrics = new ImpactMetrics();
        $metrics->total_kwh_produced = 1000;
        $metrics->total_co2_avoided_kg = 500;

        $this->assertEquals(0.5, $metrics->efficiency);
    }

    /** @test */
    public function it_returns_zero_efficiency_when_no_production()
    {
        $metrics = new ImpactMetrics();
        $metrics->total_kwh_produced = 0;
        $metrics->total_co2_avoided_kg = 500;

        $this->assertEquals(0, $metrics->efficiency);
    }

    /** @test */
    public function it_has_formatted_efficiency_accessor()
    {
        $metrics = new ImpactMetrics();
        $metrics->total_kwh_produced = 1000;
        $metrics->total_co2_avoided_kg = 500;

        $this->assertEquals('0.5000 kg CO₂/kWh', $metrics->formatted_efficiency);
    }

    /** @test */
    public function it_can_get_impact_percentage()
    {
        $metrics = new ImpactMetrics();
        $metrics->total_co2_avoided_kg = 500;
        
        $percentage = $metrics->getImpactPercentage(1000);
        
        $this->assertEquals(50, $percentage);
    }

    /** @test */
    public function it_returns_zero_percentage_when_total_is_zero()
    {
        $metrics = new ImpactMetrics();
        $metrics->total_co2_avoided_kg = 500;
        
        $percentage = $metrics->getImpactPercentage(0);
        
        $this->assertEquals(0, $percentage);
    }

    /** @test */
    public function it_can_get_formatted_impact_percentage()
    {
        $metrics = new ImpactMetrics();
        $metrics->total_co2_avoided_kg = 500;
        
        $formatted = $metrics->getFormattedImpactPercentage(1000);
        
        $this->assertEquals('50.00%', $formatted);
    }

    /** @test */
    public function it_has_soft_deletes_trait()
    {
        $metrics = new ImpactMetrics();
        
        $this->assertTrue(method_exists($metrics, 'trashed'));
        $this->assertTrue(method_exists($metrics, 'restore'));
        $this->assertTrue(method_exists($metrics, 'forceDelete'));
    }

    /** @test */
    public function it_has_factory_trait()
    {
        $metrics = new ImpactMetrics();
        
        $this->assertTrue(method_exists($metrics, 'factory'));
    }

    /** @test */
    public function it_has_relationships_defined()
    {
        $metrics = new ImpactMetrics();
        
        $this->assertTrue(method_exists($metrics, 'user'));
        $this->assertTrue(method_exists($metrics, 'plantGroup'));
    }

    /** @test */
    public function it_has_scope_methods_defined()
    {
        $metrics = new ImpactMetrics();
        
        $this->assertTrue(method_exists($metrics, 'scopeByUser'));
        $this->assertTrue(method_exists($metrics, 'scopeByPlantGroup'));
        $this->assertTrue(method_exists($metrics, 'scopeGlobal'));
        $this->assertTrue(method_exists($metrics, 'scopeIndividual'));
        $this->assertTrue(method_exists($metrics, 'scopeByDateRange'));
        $this->assertTrue(method_exists($metrics, 'scopeByCo2Range'));
        $this->assertTrue(method_exists($metrics, 'scopeByKwhRange'));
        $this->assertTrue(method_exists($metrics, 'scopeRecent'));
        $this->assertTrue(method_exists($metrics, 'scopeThisMonth'));
        $this->assertTrue(method_exists($metrics, 'scopeThisYear'));
        $this->assertTrue(method_exists($metrics, 'scopeOrderByImpact'));
        $this->assertTrue(method_exists($metrics, 'scopeOrderByProduction'));
        $this->assertTrue(method_exists($metrics, 'scopeOrderByDate'));
    }

    /** @test */
    public function it_has_static_methods_defined()
    {
        $this->assertTrue(method_exists(ImpactMetrics::class, 'getTotalGlobalImpact'));
        $this->assertTrue(method_exists(ImpactMetrics::class, 'getTotalGlobalProduction'));
        $this->assertTrue(method_exists(ImpactMetrics::class, 'getTopUsersByImpact'));
        $this->assertTrue(method_exists(ImpactMetrics::class, 'getTopUsersByProduction'));
        $this->assertTrue(method_exists(ImpactMetrics::class, 'getCommunityImpact'));
    }

    /** @test */
    public function it_has_instance_methods_defined()
    {
        $metrics = new ImpactMetrics();
        
        $this->assertTrue(method_exists($metrics, 'updateMetrics'));
        $this->assertTrue(method_exists($metrics, 'resetMetrics'));
        $this->assertTrue(method_exists($metrics, 'addKwhProduction'));
        $this->assertTrue(method_exists($metrics, 'getImpactPercentage'));
        $this->assertTrue(method_exists($metrics, 'getFormattedImpactPercentage'));
    }
}
