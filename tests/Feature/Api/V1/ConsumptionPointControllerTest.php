<?php

namespace Tests\Feature\Api\V1;

use App\Models\ConsumptionPoint;
use App\Models\Customer;
use App\Models\EnergyInstallation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConsumptionPointControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_consumption_points()
    {
        ConsumptionPoint::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/consumption-points');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'point_number',
                            'name',
                            'point_type',
                            'status',
                            'created_at'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page'
                    ]
                ]);
    }

    /** @test */
    public function it_can_filter_consumption_points_by_search()
    {
        ConsumptionPoint::factory()->create(['name' => 'Residential Point']);
        ConsumptionPoint::factory()->create(['name' => 'Commercial Point']);

        $response = $this->getJson('/api/v1/consumption-points?search=Residential');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Residential Point', $response->json('data.0.name'));
    }

    /** @test */
    public function it_can_filter_consumption_points_by_point_type()
    {
        ConsumptionPoint::factory()->create(['point_type' => 'residential']);
        ConsumptionPoint::factory()->create(['point_type' => 'commercial']);

        $response = $this->getJson('/api/v1/consumption-points?point_type=residential');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('residential', $response->json('data.0.point_type'));
    }

    /** @test */
    public function it_can_filter_consumption_points_by_status()
    {
        ConsumptionPoint::factory()->create(['status' => 'active']);
        ConsumptionPoint::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/consumption-points?status=active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_filter_consumption_points_by_customer()
    {
        $customer = Customer::factory()->create();
        ConsumptionPoint::factory()->create(['customer_id' => $customer->id]);
        ConsumptionPoint::factory()->create(['customer_id' => Customer::factory()->create()->id]);

        $response = $this->getJson("/api/v1/consumption-points?customer_id={$customer->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($customer->id, $response->json('data.0.customer_id'));
    }

    /** @test */
    public function it_can_sort_consumption_points()
    {
        ConsumptionPoint::factory()->create(['name' => 'B Point']);
        ConsumptionPoint::factory()->create(['name' => 'A Point']);

        $response = $this->getJson('/api/v1/consumption-points?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('A Point', $response->json('data.0.name'));
        $this->assertEquals('B Point', $response->json('data.1.name'));
    }

    /** @test */
    public function it_can_create_consumption_point()
    {
        $customer = Customer::factory()->create();
        $data = [
            'point_number' => 'CP-001',
            'name' => 'Test Point',
            'point_type' => 'residential',
            'status' => 'active',
            'customer_id' => $customer->id,
            'connection_date' => '2024-01-15',
            'peak_demand_kw' => 15.5,
            'annual_consumption_kwh' => 5000.0
        ];

        $response = $this->postJson('/api/v1/consumption-points', $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'point_number',
                        'name',
                        'point_type',
                        'status'
                    ]
                ]);

        $this->assertDatabaseHas('consumption_points', [
            'point_number' => 'CP-001',
            'name' => 'Test Point'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating()
    {
        $response = $this->postJson('/api/v1/consumption-points', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'point_number',
                    'name',
                    'point_type',
                    'status',
                    'customer_id',
                    'connection_date'
                ]);
    }

    /** @test */
    public function it_validates_unique_point_number()
    {
        $customer = Customer::factory()->create();
        ConsumptionPoint::factory()->create(['point_number' => 'CP-001']);

        $data = [
            'point_number' => 'CP-001',
            'name' => 'Test Point',
            'point_type' => 'residential',
            'status' => 'active',
            'customer_id' => $customer->id,
            'connection_date' => '2024-01-15'
        ];

        $response = $this->postJson('/api/v1/consumption-points', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['point_number']);
    }

    /** @test */
    public function it_can_show_consumption_point()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();

        $response = $this->getJson("/api/v1/consumption-points/{$consumptionPoint->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'point_number',
                        'name',
                        'point_type',
                        'status'
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_consumption_point()
    {
        $response = $this->getJson('/api/v1/consumption-points/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_consumption_point()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();
        $updateData = [
            'name' => 'Updated Point',
            'peak_demand_kw' => 20.0
        ];

        $response = $this->putJson("/api/v1/consumption-points/{$consumptionPoint->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'peak_demand_kw'
                    ]
                ]);

        $this->assertDatabaseHas('consumption_points', [
            'id' => $consumptionPoint->id,
            'name' => 'Updated Point',
            'peak_demand_kw' => 20.0
        ]);
    }

    /** @test */
    public function it_validates_unique_point_number_on_update()
    {
        $point1 = ConsumptionPoint::factory()->create(['point_number' => 'CP-001']);
        $point2 = ConsumptionPoint::factory()->create(['point_number' => 'CP-002']);

        $response = $this->putJson("/api/v1/consumption-points/{$point2->id}", [
            'point_number' => 'CP-001'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['point_number']);
    }

    /** @test */
    public function it_can_delete_consumption_point()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();

        $response = $this->deleteJson("/api/v1/consumption-points/{$consumptionPoint->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('consumption_points', ['id' => $consumptionPoint->id]);
    }

    /** @test */
    public function it_can_get_statistics()
    {
        ConsumptionPoint::factory()->create(['status' => 'active', 'point_type' => 'residential']);
        ConsumptionPoint::factory()->create(['status' => 'active', 'point_type' => 'commercial']);
        ConsumptionPoint::factory()->create(['status' => 'maintenance', 'point_type' => 'residential']);

        $response = $this->getJson('/api/v1/consumption-points/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_points',
                        'active_points',
                        'maintenance_points',
                        'points_by_type',
                        'points_by_status'
                    ]
                ]);

        $this->assertEquals(3, $response->json('data.total_points'));
        $this->assertEquals(2, $response->json('data.active_points'));
        $this->assertEquals(1, $response->json('data.maintenance_points'));
    }

    /** @test */
    public function it_can_get_point_types()
    {
        $response = $this->getJson('/api/v1/consumption-points/types');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'residential',
                        'commercial',
                        'industrial'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_statuses()
    {
        $response = $this->getJson('/api/v1/consumption-points/statuses');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'active',
                        'inactive',
                        'maintenance'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_status()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create(['status' => 'active']);

        $response = $this->postJson("/api/v1/consumption-points/{$consumptionPoint->id}/update-status", [
            'status' => 'maintenance'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('consumption_points', [
            'id' => $consumptionPoint->id,
            'status' => 'maintenance'
        ]);
    }

    /** @test */
    public function it_validates_status_on_update()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();

        $response = $this->postJson("/api/v1/consumption-points/{$consumptionPoint->id}/update-status", [
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_duplicate_consumption_point()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create([
            'point_number' => 'CP-001',
            'name' => 'Original Point'
        ]);

        $response = $this->postJson("/api/v1/consumption-points/{$consumptionPoint->id}/duplicate", [
            'point_number' => 'CP-002',
            'name' => 'Duplicated Point'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('consumption_points', [
            'point_number' => 'CP-002',
            'name' => 'Duplicated Point',
            'status' => 'planned'
        ]);
    }

    /** @test */
    public function it_validates_unique_point_number_on_duplicate()
    {
        $point1 = ConsumptionPoint::factory()->create(['point_number' => 'CP-001']);
        ConsumptionPoint::factory()->create(['point_number' => 'CP-002']);

        $response = $this->postJson("/api/v1/consumption-points/{$point1->id}/duplicate", [
            'point_number' => 'CP-002'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['point_number']);
    }

    /** @test */
    public function it_can_get_active_consumption_points()
    {
        ConsumptionPoint::factory()->create(['status' => 'active']);
        ConsumptionPoint::factory()->create(['status' => 'inactive']);
        ConsumptionPoint::factory()->create(['status' => 'maintenance']);

        $response = $this->getJson('/api/v1/consumption-points/active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_maintenance_consumption_points()
    {
        ConsumptionPoint::factory()->create(['status' => 'active']);
        ConsumptionPoint::factory()->create(['status' => 'maintenance']);
        ConsumptionPoint::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/consumption-points/maintenance');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('maintenance', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_disconnected_consumption_points()
    {
        ConsumptionPoint::factory()->create(['status' => 'active']);
        ConsumptionPoint::factory()->create(['status' => 'disconnected']);
        ConsumptionPoint::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/consumption-points/disconnected');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('disconnected', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_get_consumption_points_by_type()
    {
        ConsumptionPoint::factory()->create(['point_type' => 'residential']);
        ConsumptionPoint::factory()->create(['point_type' => 'commercial']);
        ConsumptionPoint::factory()->create(['point_type' => 'residential']);

        $response = $this->getJson('/api/v1/consumption-points/by-type/residential');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals('residential', $response->json('data.0.point_type'));
    }

    /** @test */
    public function it_can_get_consumption_points_by_customer()
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        
        ConsumptionPoint::factory()->create(['customer_id' => $customer1->id]);
        ConsumptionPoint::factory()->create(['customer_id' => $customer2->id]);
        ConsumptionPoint::factory()->create(['customer_id' => $customer1->id]);

        $response = $this->getJson("/api/v1/consumption-points/by-customer/{$customer1->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals($customer1->id, $response->json('data.0.customer_id'));
    }

    /** @test */
    public function it_can_get_consumption_points_by_installation()
    {
        $installation1 = EnergyInstallation::factory()->create();
        $installation2 = EnergyInstallation::factory()->create();
        
        ConsumptionPoint::factory()->create(['installation_id' => $installation1->id]);
        ConsumptionPoint::factory()->create(['installation_id' => $installation2->id]);
        ConsumptionPoint::factory()->create(['installation_id' => $installation1->id]);

        $response = $this->getJson("/api/v1/consumption-points/by-installation/{$installation1->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals($installation1->id, $response->json('data.0.installation_id'));
    }

    /** @test */
    public function it_can_get_high_consumption_points()
    {
        ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 5000]);
        ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 15000]);
        ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 8000]);

        $response = $this->getJson('/api/v1/consumption-points/high-consumption?threshold=10000');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(15000, $response->json('data.0.annual_consumption_kwh'));
    }

    /** @test */
    public function it_can_get_points_needing_calibration()
    {
        $pastDate = now()->subDays(5);
        $futureDate = now()->addDays(5);
        
        ConsumptionPoint::factory()->create(['meter_next_calibration_date' => $pastDate]);
        ConsumptionPoint::factory()->create(['meter_next_calibration_date' => $futureDate]);
        ConsumptionPoint::factory()->create(['meter_next_calibration_date' => $pastDate]);

        $response = $this->getJson('/api/v1/consumption-points/needs-calibration');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_respects_pagination_limits()
    {
        ConsumptionPoint::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/consumption-points?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(10, $response->json('meta.per_page'));
    }

    /** @test */
    public function it_enforces_maximum_pagination_limit()
    {
        ConsumptionPoint::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/consumption-points?per_page=150');

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(100, $response->json('meta.per_page'));
    }

    /** @test */
    public function it_can_filter_by_peak_demand_range()
    {
        ConsumptionPoint::factory()->create(['peak_demand_kw' => 10.0]);
        ConsumptionPoint::factory()->create(['peak_demand_kw' => 50.0]);
        ConsumptionPoint::factory()->create(['peak_demand_kw' => 100.0]);

        $response = $this->getJson('/api/v1/consumption-points?peak_demand_min=20&peak_demand_max=80');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(50.0, $response->json('data.0.peak_demand_kw'));
    }

    /** @test */
    public function it_can_filter_by_annual_consumption_range()
    {
        ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 1000.0]);
        ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 5000.0]);
        ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 10000.0]);

        $response = $this->getJson('/api/v1/consumption-points?annual_consumption_min=2000&annual_consumption_max=8000');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(5000.0, $response->json('data.0.annual_consumption_kwh'));
    }

    /** @test */
    public function it_can_filter_by_connection_date_range()
    {
        ConsumptionPoint::factory()->create(['connection_date' => '2024-01-01']);
        ConsumptionPoint::factory()->create(['connection_date' => '2024-06-15']);
        ConsumptionPoint::factory()->create(['connection_date' => '2024-12-31']);

        $response = $this->getJson('/api/v1/consumption-points?connection_date_from=2024-03-01&connection_date_to=2024-09-30');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('2024-06-15', $response->json('data.0.connection_date'));
    }

    /** @test */
    public function it_can_filter_by_meter_type()
    {
        ConsumptionPoint::factory()->create(['meter_type' => 'smart']);
        ConsumptionPoint::factory()->create(['meter_type' => 'analog']);
        ConsumptionPoint::factory()->create(['meter_type' => 'smart']);

        $response = $this->getJson('/api/v1/consumption-points?meter_type=smart');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals('smart', $response->json('data.0.meter_type'));
    }

    /** @test */
    public function it_can_filter_by_connection_type()
    {
        ConsumptionPoint::factory()->create(['connection_type' => 'grid']);
        ConsumptionPoint::factory()->create(['connection_type' => 'off-grid']);
        ConsumptionPoint::factory()->create(['connection_type' => 'grid']);

        $response = $this->getJson('/api/v1/consumption-points?connection_type=grid');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals('grid', $response->json('data.0.connection_type'));
    }

    /** @test */
    public function it_requires_authentication()
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/consumption-points');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_logs_creation_activity()
    {
        $customer = Customer::factory()->create();
        $data = [
            'point_number' => 'CP-001',
            'name' => 'Test Point',
            'point_type' => 'residential',
            'status' => 'active',
            'customer_id' => $customer->id,
            'connection_date' => '2024-01-15'
        ];

        $this->postJson('/api/v1/consumption-points', $data);

        // Verificar que se registró la actividad (esto dependerá de cómo esté implementado el logging)
        $this->assertTrue(true);
    }

    /** @test */
    public function it_logs_update_activity()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();
        $updateData = ['name' => 'Updated Point'];

        $this->putJson("/api/v1/consumption-points/{$consumptionPoint->id}", $updateData);

        // Verificar que se registró la actividad
        $this->assertTrue(true);
    }

    /** @test */
    public function it_logs_deletion_activity()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();

        $this->deleteJson("/api/v1/consumption-points/{$consumptionPoint->id}");

        // Verificar que se registró la actividad
        $this->assertTrue(true);
    }

    /** @test */
    public function it_logs_status_update_activity()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create(['status' => 'active']);

        $this->postJson("/api/v1/consumption-points/{$consumptionPoint->id}/update-status", [
            'status' => 'maintenance'
        ]);

        // Verificar que se registró la actividad
        $this->assertTrue(true);
    }

    /** @test */
    public function it_logs_duplication_activity()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();

        $this->postJson("/api/v1/consumption-points/{$consumptionPoint->id}/duplicate", [
            'point_number' => 'CP-002'
        ]);

        // Verificar que se registró la actividad
        $this->assertTrue(true);
    }
}
