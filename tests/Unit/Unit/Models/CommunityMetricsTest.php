<?php

namespace Tests\Unit\Unit\Models;

use Tests\TestCase;
use App\Models\CommunityMetrics;
use App\Models\Organization;
use Illuminate\Foundation\Testing\WithFaker;

class CommunityMetricsTest extends TestCase
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
            'organization_id',
            'total_users',
            'total_kwh_produced',
            'total_co2_avoided',
            'updated_at'
        ];

        $this->assertEquals($fillable, (new CommunityMetrics())->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $metrics = new CommunityMetrics();
        
        $this->assertTrue($metrics->hasCast('total_users', 'integer'));
        $this->assertTrue($metrics->hasCast('total_kwh_produced', 'decimal'));
        $this->assertTrue($metrics->hasCast('total_co2_avoided', 'decimal'));
        $this->assertTrue($metrics->hasCast('updated_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('created_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('deleted_at', 'datetime'));
    }

    /** @test */
    public function it_has_correct_dates()
    {
        $metrics = new CommunityMetrics();
        
        // En Laravel 10+, las fechas se manejan automáticamente a través de $casts
        // Solo verificamos que los campos estén en $casts como datetime
        $this->assertTrue($metrics->hasCast('updated_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('created_at', 'datetime'));
        $this->assertTrue($metrics->hasCast('deleted_at', 'datetime'));
    }

    /** @test */
    public function it_has_formatted_users_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 1234;

        $this->assertEquals('1,234 usuarios', $metrics->formatted_users);
    }

    /** @test */
    public function it_has_formatted_kwh_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_kwh_produced = 1234.56;

        $this->assertEquals('1,234.56 kWh', $metrics->formatted_kwh);
    }

    /** @test */
    public function it_has_formatted_co2_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_co2_avoided = 567.89;

        $this->assertEquals('567.89 kg CO₂', $metrics->formatted_co2);
    }

    /** @test */
    public function it_has_formatted_date_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->updated_at = '2024-01-15 10:30:00';

        $this->assertEquals('15/01/2024 10:30', $metrics->formatted_date);
    }

    /** @test */
    public function it_has_efficiency_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 100;
        $metrics->total_co2_avoided = 500;

        $this->assertEquals(5, $metrics->efficiency);
    }

    /** @test */
    public function it_returns_zero_efficiency_when_no_users()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 0;
        $metrics->total_co2_avoided = 500;

        $this->assertEquals(0, $metrics->efficiency);
    }

    /** @test */
    public function it_has_formatted_efficiency_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 100;
        $metrics->total_co2_avoided = 500;

        $this->assertEquals('5.00 kg CO₂/usuario', $metrics->formatted_efficiency);
    }

    /** @test */
    public function it_has_production_per_user_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 100;
        $metrics->total_kwh_produced = 5000;

        $this->assertEquals(50, $metrics->production_per_user);
    }

    /** @test */
    public function it_returns_zero_production_per_user_when_no_users()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 0;
        $metrics->total_kwh_produced = 5000;

        $this->assertEquals(0, $metrics->production_per_user);
    }

    /** @test */
    public function it_has_formatted_production_per_user_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 100;
        $metrics->total_kwh_produced = 5000;

        $this->assertEquals('50.00 kWh/usuario', $metrics->formatted_production_per_user);
    }

    /** @test */
    public function it_has_co2_per_user_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 100;
        $metrics->total_co2_avoided = 500;

        $this->assertEquals(5, $metrics->co2_per_user);
    }

    /** @test */
    public function it_returns_zero_co2_per_user_when_no_users()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 0;
        $metrics->total_co2_avoided = 500;

        $this->assertEquals(0, $metrics->co2_per_user);
    }

    /** @test */
    public function it_has_formatted_co2_per_user_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 100;
        $metrics->total_co2_avoided = 500;

        $this->assertEquals('5.00 kg CO₂/usuario', $metrics->formatted_co2_per_user);
    }

    /** @test */
    public function it_has_is_active_accessor()
    {
        $activeMetrics = new CommunityMetrics();
        $activeMetrics->total_users = 100;

        $inactiveMetrics = new CommunityMetrics();
        $inactiveMetrics->total_users = 0;

        $this->assertTrue($activeMetrics->is_active);
        $this->assertFalse($inactiveMetrics->is_active);
    }

    /** @test */
    public function it_has_is_inactive_accessor()
    {
        $activeMetrics = new CommunityMetrics();
        $activeMetrics->total_users = 100;

        $inactiveMetrics = new CommunityMetrics();
        $inactiveMetrics->total_users = 0;

        $this->assertFalse($activeMetrics->is_inactive);
        $this->assertTrue($inactiveMetrics->is_inactive);
    }

    /** @test */
    public function it_can_calculate_efficiency()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 100;
        $metrics->total_kwh_produced = 5000;
        $metrics->total_co2_avoided = 2500;

        $efficiency = $metrics->calculateEfficiency();

        $this->assertEquals(0.5, $efficiency);
    }

    /** @test */
    public function it_returns_zero_efficiency_when_no_users_or_production()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 0;
        $metrics->total_kwh_produced = 0;
        $metrics->total_co2_avoided = 0;

        $efficiency = $metrics->calculateEfficiency();

        $this->assertEquals(0, $efficiency);
    }

    /** @test */
    public function it_has_formatted_calculated_efficiency_accessor()
    {
        $metrics = new CommunityMetrics();
        $metrics->total_users = 100;
        $metrics->total_kwh_produced = 5000;
        $metrics->total_co2_avoided = 2500;

        $this->assertEquals('0.5000 kg CO₂/kWh', $metrics->formatted_calculated_efficiency);
    }

    /** @test */
    public function it_has_soft_deletes_trait()
    {
        $metrics = new CommunityMetrics();
        
        $this->assertTrue(method_exists($metrics, 'trashed'));
        $this->assertTrue(method_exists($metrics, 'restore'));
        $this->assertTrue(method_exists($metrics, 'forceDelete'));
    }

    /** @test */
    public function it_has_factory_trait()
    {
        $metrics = new CommunityMetrics();
        
        $this->assertTrue(method_exists($metrics, 'factory'));
    }

    /** @test */
    public function it_has_relationships_defined()
    {
        $metrics = new CommunityMetrics();
        
        $this->assertTrue(method_exists($metrics, 'organization'));
    }

    /** @test */
    public function it_has_scope_methods_defined()
    {
        $metrics = new CommunityMetrics();
        
        $this->assertTrue(method_exists($metrics, 'scopeByOrganization'));
        $this->assertTrue(method_exists($metrics, 'scopeByUserCount'));
        $this->assertTrue(method_exists($metrics, 'scopeByCo2Range'));
        $this->assertTrue(method_exists($metrics, 'scopeByKwhRange'));
        $this->assertTrue(method_exists($metrics, 'scopeActive'));
        $this->assertTrue(method_exists($metrics, 'scopeInactive'));
        $this->assertTrue(method_exists($metrics, 'scopeOrderByUsers'));
        $this->assertTrue(method_exists($metrics, 'scopeOrderByImpact'));
        $this->assertTrue(method_exists($metrics, 'scopeOrderByProduction'));
        $this->assertTrue(method_exists($metrics, 'scopeOrderByDate'));
        $this->assertTrue(method_exists($metrics, 'scopeRecent'));
        $this->assertTrue(method_exists($metrics, 'scopeThisMonth'));
        $this->assertTrue(method_exists($metrics, 'scopeThisYear'));
    }

    /** @test */
    public function it_has_static_methods_defined()
    {
        $this->assertTrue(method_exists(CommunityMetrics::class, 'getTotalCommunityImpact'));
        $this->assertTrue(method_exists(CommunityMetrics::class, 'getTotalCommunityProduction'));
        $this->assertTrue(method_exists(CommunityMetrics::class, 'getTotalCommunityUsers'));
        $this->assertTrue(method_exists(CommunityMetrics::class, 'getTopOrganizationsByImpact'));
        $this->assertTrue(method_exists(CommunityMetrics::class, 'getTopOrganizationsByProduction'));
        $this->assertTrue(method_exists(CommunityMetrics::class, 'getTopOrganizationsByUsers'));
        $this->assertTrue(method_exists(CommunityMetrics::class, 'getAverageMetrics'));
        $this->assertTrue(method_exists(CommunityMetrics::class, 'getFormattedAverageMetrics'));
    }

    /** @test */
    public function it_has_instance_methods_defined()
    {
        $metrics = new CommunityMetrics();
        
        $this->assertTrue(method_exists($metrics, 'updateMetrics'));
        $this->assertTrue(method_exists($metrics, 'addUser'));
        $this->assertTrue(method_exists($metrics, 'removeUser'));
        $this->assertTrue(method_exists($metrics, 'addKwhProduction'));
        $this->assertTrue(method_exists($metrics, 'addCo2Avoided'));
        $this->assertTrue(method_exists($metrics, 'resetMetrics'));
        $this->assertTrue(method_exists($metrics, 'calculateEfficiency'));
        $this->assertTrue(method_exists($metrics, 'getRankingPosition'));
        $this->assertTrue(method_exists($metrics, 'getFormattedRankingAttribute'));
    }

    /** @test */
    public function it_has_ranking_methods()
    {
        $metrics = new CommunityMetrics();
        
        $this->assertTrue(method_exists($metrics, 'getRankingPosition'));
        $this->assertTrue(method_exists($metrics, 'getFormattedRankingAttribute'));
    }

    /** @test */
    public function it_has_private_helper_methods()
    {
        $metrics = new CommunityMetrics();
        
        // Verificar que el método privado existe usando reflection
        $reflection = new \ReflectionClass($metrics);
        $this->assertTrue($reflection->hasMethod('getOrdinalSuffix'));
    }
}
