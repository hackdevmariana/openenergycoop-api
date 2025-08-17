<?php

namespace Tests\Feature\Feature\Controllers\Api\V1;

use App\Models\User;
use App\Models\PerformanceIndicator;
use App\Models\EnergyCooperative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class PerformanceIndicatorControllerTest extends TestCase
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
    public function it_can_list_performance_indicators()
    {
        PerformanceIndicator::factory(4)->create();

        $response = $this->getJson('/api/v1/performance-indicators');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'indicator_name', 'indicator_code', 'current_value', 'criticality'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_by_indicator_type()
    {
        PerformanceIndicator::factory(2)->create(['indicator_type' => 'kpi']);
        PerformanceIndicator::factory(3)->create(['indicator_type' => 'efficiency']);

        $response = $this->getJson('/api/v1/performance-indicators?indicator_type=kpi');

        $response->assertOk();
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_by_criticality()
    {
        PerformanceIndicator::factory(2)->critical()->create();
        PerformanceIndicator::factory(3)->create(['criticality' => 'low']);

        $response = $this->getJson('/api/v1/performance-indicators?criticality=critical');

        $response->assertOk();
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_active_indicators()
    {
        PerformanceIndicator::factory(3)->create(['is_active' => true]);
        PerformanceIndicator::factory(2)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/performance-indicators?is_active=1');

        $response->assertOk();
        $this->assertEquals(3, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_create_performance_indicator()
    {
        $indicatorData = [
            'indicator_name' => 'Test KPI',
            'indicator_code' => 'PI-TEST-001',
            'indicator_type' => 'kpi',
            'category' => 'operational',
            'criticality' => 'high',
            'current_value' => 85.5,
            'unit' => '%',
            'measurement_timestamp' => now()->toISOString(),
            'measurement_date' => now()->format('Y-m-d'),
            'period_start' => now()->subMonth()->format('Y-m-d'),
            'period_end' => now()->format('Y-m-d'),
            'period_type' => 'monthly',
        ];

        $response = $this->postJson('/api/v1/performance-indicators', $indicatorData);

        $response->assertCreated();
        $this->assertDatabaseHas('performance_indicators', [
            'indicator_name' => 'Test KPI',
            'current_value' => 85.5,
            'created_by_id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/v1/performance-indicators', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'indicator_name', 'indicator_code', 'indicator_type', 'category', 'current_value'
            ]);
    }

    /** @test */
    public function it_validates_unique_indicator_code()
    {
        PerformanceIndicator::factory()->create(['indicator_code' => 'EXISTING-PI']);

        $indicatorData = [
            'indicator_name' => 'New Indicator',
            'indicator_code' => 'EXISTING-PI',
            'indicator_type' => 'kpi',
            'category' => 'operational',
            'criticality' => 'medium',
            'current_value' => 75,
            'measurement_timestamp' => now()->toISOString(),
            'measurement_date' => now()->format('Y-m-d'),
            'period_start' => now()->subMonth()->format('Y-m-d'),
            'period_end' => now()->format('Y-m-d'),
            'period_type' => 'monthly',
        ];

        $response = $this->postJson('/api/v1/performance-indicators', $indicatorData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['indicator_code']);
    }

    /** @test */
    public function it_can_show_performance_indicator()
    {
        $indicator = PerformanceIndicator::factory()->operational()->create();

        $response = $this->getJson("/api/v1/performance-indicators/{$indicator->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $indicator->id])
            ->assertJsonStructure([
                'id', 'indicator_name', 'current_value', 'target_value', 'category'
            ]);
    }

    /** @test */
    public function it_can_update_performance_indicator()
    {
        $indicator = PerformanceIndicator::factory()->create([
            'indicator_name' => 'Original Name',
            'current_value' => 50
        ]);

        $updateData = [
            'indicator_name' => 'Updated Name',
            'current_value' => 75,
            'target_value' => 80,
            'is_active' => false
        ];

        $response = $this->putJson("/api/v1/performance-indicators/{$indicator->id}", $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('performance_indicators', [
            'id' => $indicator->id,
            'indicator_name' => 'Updated Name',
            'current_value' => 75,
            'is_active' => false
        ]);
    }

    /** @test */
    public function it_can_delete_performance_indicator()
    {
        $indicator = PerformanceIndicator::factory()->create();

        $response = $this->deleteJson("/api/v1/performance-indicators/{$indicator->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('performance_indicators', ['id' => $indicator->id]);
    }

    /** @test */
    public function it_can_get_dashboard_indicators()
    {
        PerformanceIndicator::factory(3)->forDashboard()->create();
        PerformanceIndicator::factory(2)->create(['show_in_dashboard' => false]);

        $response = $this->getJson('/api/v1/performance-indicators/dashboard');

        $response->assertOk()
            ->assertJsonCount(3);

        $indicators = $response->json();
        foreach ($indicators as $indicator) {
            $this->assertTrue($indicator['show_in_dashboard']);
            $this->assertTrue($indicator['is_active']);
        }
    }

    /** @test */
    public function it_can_get_indicators_with_alerts()
    {
        PerformanceIndicator::factory(2)->withAlerts()->create();
        PerformanceIndicator::factory(3)->create(['alerts_enabled' => false]);

        $response = $this->getJson('/api/v1/performance-indicators/alerts');

        $response->assertOk()
            ->assertJsonCount(2);

        $alerts = $response->json();
        foreach ($alerts as $alert) {
            $this->assertTrue($alert['alerts_enabled']);
            $this->assertContains($alert['current_alert_level'], ['warning', 'critical', 'emergency']);
        }
    }

    /** @test */
    public function it_can_filter_dashboard_indicators()
    {
        PerformanceIndicator::factory(2)->forDashboard()->create();
        PerformanceIndicator::factory(3)->create(['show_in_dashboard' => false]);

        $response = $this->getJson('/api/v1/performance-indicators?show_in_dashboard=1');

        $response->assertOk();
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /** @test */
    public function it_orders_by_priority_and_date()
    {
        $indicator1 = PerformanceIndicator::factory()->create([
            'priority' => 3,
            'measurement_date' => now()->subDays(2)
        ]);
        $indicator2 = PerformanceIndicator::factory()->create([
            'priority' => 5,
            'measurement_date' => now()->subDay()
        ]);
        $indicator3 = PerformanceIndicator::factory()->create([
            'priority' => 5,
            'measurement_date' => now()
        ]);

        $response = $this->getJson('/api/v1/performance-indicators');

        $response->assertOk();
        $data = $response->json('data');

        // Debería ordenar por priority desc, luego por measurement_date desc
        $this->assertEquals($indicator3->id, $data[0]['id']);
        $this->assertEquals($indicator2->id, $data[1]['id']);
        $this->assertEquals($indicator1->id, $data[2]['id']);
    }

    /** @test */
    public function it_handles_non_existent_indicator()
    {
        $response = $this->getJson('/api/v1/performance-indicators/99999');
        $response->assertNotFound();
    }

    /** @test */
    public function it_requires_authentication()
    {
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/v1/performance-indicators');
        $response->assertUnauthorized();
    }

    /** @test */
    public function it_validates_period_dates()
    {
        $indicatorData = [
            'indicator_name' => 'Test Indicator',
            'indicator_code' => 'TEST-001',
            'indicator_type' => 'kpi',
            'category' => 'operational',
            'criticality' => 'medium',
            'current_value' => 75,
            'measurement_timestamp' => now()->toISOString(),
            'measurement_date' => now()->format('Y-m-d'),
            'period_start' => now()->format('Y-m-d'),
            'period_end' => now()->subDay()->format('Y-m-d'), // End before start
            'period_type' => 'daily',
        ];

        $response = $this->postJson('/api/v1/performance-indicators', $indicatorData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['period_end']);
    }

    /** @test */
    public function it_can_create_financial_indicator()
    {
        $indicatorData = [
            'indicator_name' => 'ROI Mensual',
            'indicator_code' => 'FIN-ROI-001',
            'indicator_type' => 'kpi',
            'category' => 'financial',
            'criticality' => 'high',
            'current_value' => 15.75,
            'unit' => '%',
            'target_value' => 20.0,
            'measurement_timestamp' => now()->toISOString(),
            'measurement_date' => now()->format('Y-m-d'),
            'period_start' => now()->subMonth()->format('Y-m-d'),
            'period_end' => now()->format('Y-m-d'),
            'period_type' => 'monthly',
        ];

        $response = $this->postJson('/api/v1/performance-indicators', $indicatorData);

        $response->assertCreated();
        $this->assertDatabaseHas('performance_indicators', [
            'indicator_code' => 'FIN-ROI-001',
            'category' => 'financial',
            'current_value' => 15.75
        ]);
    }

    /** @test */
    public function it_can_create_critical_indicator_with_alerts()
    {
        $indicatorData = [
            'indicator_name' => 'Critical System Load',
            'indicator_code' => 'CRIT-SYS-001',
            'indicator_type' => 'metric',
            'category' => 'technical',
            'criticality' => 'critical',
            'current_value' => 95.5,
            'unit' => '%',
            'alerts_enabled' => true,
            'measurement_timestamp' => now()->toISOString(),
            'measurement_date' => now()->format('Y-m-d'),
            'period_start' => now()->subHour()->format('Y-m-d'),
            'period_end' => now()->format('Y-m-d'),
            'period_type' => 'instant',
        ];

        $response = $this->postJson('/api/v1/performance-indicators', $indicatorData);

        $response->assertCreated();
        $this->assertDatabaseHas('performance_indicators', [
            'indicator_code' => 'CRIT-SYS-001',
            'criticality' => 'critical',
            'alerts_enabled' => true
        ]);
    }
}