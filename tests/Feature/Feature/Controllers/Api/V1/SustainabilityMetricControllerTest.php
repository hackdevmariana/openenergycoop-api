<?php

namespace Tests\Feature\Feature\Controllers\Api\V1;

use App\Models\User;
use App\Models\SustainabilityMetric;
use App\Models\EnergyCooperative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SustainabilityMetricControllerTest extends TestCase
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
    public function it_can_list_sustainability_metrics()
    {
        SustainabilityMetric::factory(5)->create();

        $response = $this->getJson('/api/v1/sustainability-metrics');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'metric_name', 'metric_code', 'value', 'unit'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_by_metric_type()
    {
        SustainabilityMetric::factory(2)->create(['metric_type' => 'carbon_footprint']);
        SustainabilityMetric::factory(3)->create(['metric_type' => 'renewable_percentage']);

        $response = $this->getJson('/api/v1/sustainability-metrics?metric_type=carbon_footprint');

        $response->assertOk();
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_by_certification()
    {
        SustainabilityMetric::factory(2)->certified()->create();
        SustainabilityMetric::factory(3)->create(['is_certified' => false]);

        $response = $this->getJson('/api/v1/sustainability-metrics?is_certified=1');

        $response->assertOk();
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_create_sustainability_metric()
    {
        $metricData = [
            'metric_name' => 'Test Carbon Footprint',
            'metric_code' => 'SUST-TEST-001',
            'metric_type' => 'carbon_footprint',
            'value' => 1250.50,
            'unit' => 'kg CO2',
            'measurement_date' => '2024-01-15',
            'period_start' => '2024-01-01',
            'period_end' => '2024-01-31',
            'period_type' => 'monthly',
        ];

        $response = $this->postJson('/api/v1/sustainability-metrics', $metricData);

        $response->assertCreated();
        $this->assertDatabaseHas('sustainability_metrics', [
            'metric_name' => 'Test Carbon Footprint',
            'value' => 1250.50
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/v1/sustainability-metrics', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'metric_name', 'metric_code', 'metric_type', 'value'
            ]);
    }

    /** @test */
    public function it_validates_unique_metric_code()
    {
        SustainabilityMetric::factory()->create(['metric_code' => 'EXISTING-CODE']);

        $metricData = [
            'metric_name' => 'New Metric',
            'metric_code' => 'EXISTING-CODE',
            'metric_type' => 'carbon_footprint',
            'value' => 100,
            'unit' => 'kg',
            'measurement_date' => '2024-01-15',
            'period_start' => '2024-01-01',
            'period_end' => '2024-01-31',
            'period_type' => 'monthly',
        ];

        $response = $this->postJson('/api/v1/sustainability-metrics', $metricData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['metric_code']);
    }

    /** @test */
    public function it_can_show_sustainability_metric()
    {
        $metric = SustainabilityMetric::factory()->carbonFootprint()->create();

        $response = $this->getJson("/api/v1/sustainability-metrics/{$metric->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $metric->id])
            ->assertJsonStructure([
                'id', 'metric_name', 'value', 'unit', 'metric_type'
            ]);
    }

    /** @test */
    public function it_can_update_sustainability_metric()
    {
        $metric = SustainabilityMetric::factory()->create([
            'metric_name' => 'Original Name',
            'value' => 100
        ]);

        $updateData = [
            'metric_name' => 'Updated Name',
            'value' => 150,
            'is_certified' => true
        ];

        $response = $this->putJson("/api/v1/sustainability-metrics/{$metric->id}", $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('sustainability_metrics', [
            'id' => $metric->id,
            'metric_name' => 'Updated Name',
            'value' => 150,
            'is_certified' => true
        ]);
    }

    /** @test */
    public function it_can_delete_sustainability_metric()
    {
        $metric = SustainabilityMetric::factory()->create();

        $response = $this->deleteJson("/api/v1/sustainability-metrics/{$metric->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('sustainability_metrics', ['id' => $metric->id]);
    }

    /** @test */
    public function it_can_get_metrics_summary()
    {
        SustainabilityMetric::factory(3)->create(['metric_type' => 'carbon_footprint']);
        SustainabilityMetric::factory(2)->certified()->create(['metric_type' => 'renewable_percentage']);

        $response = $this->getJson('/api/v1/sustainability-metrics/summary');

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'metric_type',
                    'total_metrics',
                    'average_value',
                    'certified_count'
                ]
            ]);

        $summary = $response->json();
        $this->assertCount(2, $summary); // 2 tipos de métricas
    }

    /** @test */
    public function it_can_filter_by_entity_type()
    {
        SustainabilityMetric::factory(2)->create(['entity_type' => 'user']);
        SustainabilityMetric::factory(3)->create(['entity_type' => 'cooperative']);

        $response = $this->getJson('/api/v1/sustainability-metrics?entity_type=user');

        $response->assertOk();
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /** @test */
    public function it_handles_non_existent_metric()
    {
        $response = $this->getJson('/api/v1/sustainability-metrics/99999');
        $response->assertNotFound();
    }

    /** @test */
    public function it_requires_authentication()
    {
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/v1/sustainability-metrics');
        $response->assertUnauthorized();
    }

    /** @test */
    public function it_validates_period_dates()
    {
        $metricData = [
            'metric_name' => 'Test Metric',
            'metric_code' => 'TEST-001',
            'metric_type' => 'carbon_footprint',
            'value' => 100,
            'unit' => 'kg',
            'measurement_date' => '2024-01-15',
            'period_start' => '2024-01-31',
            'period_end' => '2024-01-01', // End before start
            'period_type' => 'monthly',
        ];

        $response = $this->postJson('/api/v1/sustainability-metrics', $metricData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['period_end']);
    }

    /** @test */
    public function it_can_create_certified_metric()
    {
        $metricData = [
            'metric_name' => 'Certified Carbon Metric',
            'metric_code' => 'CERT-001',
            'metric_type' => 'carbon_footprint',
            'value' => 500,
            'unit' => 'kg CO2',
            'measurement_date' => '2024-01-15',
            'period_start' => '2024-01-01',
            'period_end' => '2024-01-31',
            'period_type' => 'monthly',
            'is_certified' => true,
        ];

        $response = $this->postJson('/api/v1/sustainability-metrics', $metricData);

        $response->assertCreated();
        $this->assertDatabaseHas('sustainability_metrics', [
            'metric_code' => 'CERT-001',
            'is_certified' => true
        ]);
    }
}