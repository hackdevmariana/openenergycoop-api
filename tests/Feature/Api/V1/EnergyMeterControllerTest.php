<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyMeter;
use App\Models\Customer;
use App\Models\EnergyInstallation;
use App\Models\ConsumptionPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnergyMeterControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $customer;
    protected $installation;
    protected $consumptionPoint;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->installation = EnergyInstallation::factory()->create();
        $this->consumptionPoint = ConsumptionPoint::factory()->create();
    }

    /** @test */
    public function it_can_list_energy_meters()
    {
        EnergyMeter::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'meter_number',
                        'name',
                        'meter_type',
                        'status',
                        'meter_category',
                        'created_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'total',
                    'per_page',
                    'last_page'
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_energy_meters_by_search()
    {
        $meter1 = EnergyMeter::factory()->create(['name' => 'Smart Meter A']);
        $meter2 = EnergyMeter::factory()->create(['name' => 'Digital Meter B']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters?search=Smart');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $meter1->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $meter2->id));
    }

    /** @test */
    public function it_can_filter_energy_meters_by_meter_type()
    {
        $smartMeter = EnergyMeter::factory()->create(['meter_type' => 'smart_meter']);
        $digitalMeter = EnergyMeter::factory()->create(['meter_type' => 'digital_meter']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters?meter_type=smart_meter');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $smartMeter->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $digitalMeter->id));
    }

    /** @test */
    public function it_can_filter_energy_meters_by_status()
    {
        $activeMeter = EnergyMeter::factory()->create(['status' => 'active']);
        $inactiveMeter = EnergyMeter::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters?status=active');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $activeMeter->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $inactiveMeter->id));
    }

    /** @test */
    public function it_can_filter_energy_meters_by_customer()
    {
        $meter1 = EnergyMeter::factory()->create(['customer_id' => $this->customer->id]);
        $meter2 = EnergyMeter::factory()->create(['customer_id' => Customer::factory()->create()->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-meters?customer_id={$this->customer->id}");

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $meter1->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $meter2->id));
    }

    /** @test */
    public function it_can_sort_energy_meters()
    {
        $meter1 = EnergyMeter::factory()->create(['name' => 'A Meter']);
        $meter2 = EnergyMeter::factory()->create(['name' => 'B Meter']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($meter1->id, $data[0]['id']);
        $this->assertEquals($meter2->id, $data[1]['id']);
    }

    /** @test */
    public function it_can_create_energy_meter()
    {
        $data = [
            'meter_number' => 'MTR-001',
            'name' => 'Smart Meter',
            'description' => 'Smart electricity meter',
            'meter_type' => 'smart_meter',
            'status' => 'active',
            'meter_category' => 'electricity',
            'serial_number' => 'SN123456',
            'customer_id' => $this->customer->id,
            'installation_date' => now()->subDays(10)->format('Y-m-d'),
            'voltage_rating' => 230.0,
            'current_rating' => 63.0,
            'accuracy_class' => 0.5,
            'is_smart_meter' => true,
            'has_remote_reading' => true,
            'has_two_way_communication' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-meters', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'meter_number',
                    'name',
                    'meter_type',
                    'status',
                    'created_at'
                ]
            ]);

        $this->assertDatabaseHas('energy_meters', [
            'meter_number' => 'MTR-001',
            'name' => 'Smart Meter',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-meters', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'meter_number',
                'name',
                'meter_type',
                'status',
                'meter_category',
                'serial_number',
                'customer_id',
                'installation_date'
            ]);
    }

    /** @test */
    public function it_validates_unique_meter_number_when_creating()
    {
        $existingMeter = EnergyMeter::factory()->create(['meter_number' => 'MTR-001']);

        $data = [
            'meter_number' => 'MTR-001',
            'name' => 'Another Meter',
            'meter_type' => 'smart_meter',
            'status' => 'active',
            'meter_category' => 'electricity',
            'serial_number' => 'SN789012',
            'customer_id' => $this->customer->id,
            'installation_date' => now()->subDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-meters', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['meter_number']);
    }

    /** @test */
    public function it_can_show_energy_meter()
    {
        $energyMeter = EnergyMeter::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-meters/{$energyMeter->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'meter_number',
                    'name',
                    'meter_type',
                    'status',
                    'meter_category',
                    'created_at'
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_energy_meter()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_energy_meter()
    {
        $energyMeter = EnergyMeter::factory()->create();
        $updateData = [
            'name' => 'Updated Smart Meter',
            'description' => 'Updated description',
            'status' => 'maintenance',
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-meters/{$energyMeter->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'status',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('energy_meters', [
            'id' => $energyMeter->id,
            'name' => 'Updated Smart Meter',
            'status' => 'maintenance',
        ]);
    }

    /** @test */
    public function it_validates_unique_meter_number_when_updating()
    {
        $meter1 = EnergyMeter::factory()->create(['meter_number' => 'MTR-001']);
        $meter2 = EnergyMeter::factory()->create(['meter_number' => 'MTR-002']);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-meters/{$meter2->id}", [
                'meter_number' => 'MTR-001'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['meter_number']);
    }

    /** @test */
    public function it_can_delete_energy_meter()
    {
        $energyMeter = EnergyMeter::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/energy-meters/{$energyMeter->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Medidor de energía eliminado exitosamente']);

        $this->assertDatabaseMissing('energy_meters', [
            'id' => $energyMeter->id,
        ]);
    }

    /** @test */
    public function it_can_get_energy_meter_statistics()
    {
        EnergyMeter::factory()->count(5)->create(['status' => 'active']);
        EnergyMeter::factory()->count(3)->create(['status' => 'maintenance']);
        EnergyMeter::factory()->count(2)->create(['status' => 'faulty']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_meters',
                    'active_meters',
                    'maintenance_meters',
                    'faulty_meters',
                    'smart_meters',
                    'meters_by_type',
                    'meters_by_category',
                    'meters_by_status'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(10, $data['total_meters']);
        $this->assertEquals(5, $data['active_meters']);
        $this->assertEquals(3, $data['maintenance_meters']);
        $this->assertEquals(2, $data['faulty_meters']);
    }

    /** @test */
    public function it_can_get_meter_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/types');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function it_can_get_meter_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/statuses');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function it_can_get_meter_categories()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function it_can_update_meter_status()
    {
        $energyMeter = EnergyMeter::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-meters/{$energyMeter->id}/update-status", [
                'status' => 'maintenance'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('energy_meters', [
            'id' => $energyMeter->id,
            'status' => 'maintenance',
        ]);
    }

    /** @test */
    public function it_validates_status_when_updating()
    {
        $energyMeter = EnergyMeter::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-meters/{$energyMeter->id}/update-status", [
                'status' => 'invalid_status'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_can_duplicate_energy_meter()
    {
        $energyMeter = EnergyMeter::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-meters/{$energyMeter->id}/duplicate", [
                'meter_number' => 'MTR-002',
                'name' => 'Smart Meter - Copy'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'meter_number',
                    'name',
                    'status',
                    'created_at'
                ]
            ]);

        $this->assertDatabaseHas('energy_meters', [
            'meter_number' => 'MTR-002',
            'name' => 'Smart Meter - Copy',
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function it_validates_unique_meter_number_when_duplicating()
    {
        $meter1 = EnergyMeter::factory()->create(['meter_number' => 'MTR-001']);
        $meter2 = EnergyMeter::factory()->create(['meter_number' => 'MTR-002']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-meters/{$meter1->id}/duplicate", [
                'meter_number' => 'MTR-002'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['meter_number']);
    }

    /** @test */
    public function it_can_get_active_meters()
    {
        $activeMeter = EnergyMeter::factory()->create(['status' => 'active']);
        $inactiveMeter = EnergyMeter::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/active');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $activeMeter->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $inactiveMeter->id));
    }

    /** @test */
    public function it_can_get_smart_meters()
    {
        $smartMeter = EnergyMeter::factory()->create(['is_smart_meter' => true]);
        $regularMeter = EnergyMeter::factory()->create(['is_smart_meter' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/smart-meters');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $smartMeter->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $regularMeter->id));
    }

    /** @test */
    public function it_can_get_meters_needing_calibration()
    {
        $meterNeedingCalibration = EnergyMeter::factory()->create([
            'next_calibration_date' => now()->subDays(5),
            'status' => 'active'
        ]);
        $meterNotNeedingCalibration = EnergyMeter::factory()->create([
            'next_calibration_date' => now()->addDays(30),
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/needs-calibration');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $meterNeedingCalibration->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $meterNotNeedingCalibration->id));
    }

    /** @test */
    public function it_can_get_meters_by_type()
    {
        $smartMeter = EnergyMeter::factory()->create(['meter_type' => 'smart_meter']);
        $digitalMeter = EnergyMeter::factory()->create(['meter_type' => 'digital_meter']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/by-type/smart_meter');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $smartMeter->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $digitalMeter->id));
    }

    /** @test */
    public function it_can_get_meters_by_category()
    {
        $electricityMeter = EnergyMeter::factory()->create(['meter_category' => 'electricity']);
        $waterMeter = EnergyMeter::factory()->create(['meter_category' => 'water']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/by-category/electricity');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $electricityMeter->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $waterMeter->id));
    }

    /** @test */
    public function it_can_get_meters_by_customer()
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();

        $meter1 = EnergyMeter::factory()->create(['customer_id' => $customer1->id]);
        $meter2 = EnergyMeter::factory()->create(['customer_id' => $customer2->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-meters/by-customer/{$customer1->id}");

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $meter1->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $meter2->id));
    }

    /** @test */
    public function it_can_get_meters_by_installation()
    {
        $installation1 = EnergyInstallation::factory()->create();
        $installation2 = EnergyInstallation::factory()->create();

        $meter1 = EnergyMeter::factory()->create(['installation_id' => $installation1->id]);
        $meter2 = EnergyMeter::factory()->create(['installation_id' => $installation2->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-meters/by-installation/{$installation1->id}");

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $meter1->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $meter2->id));
    }

    /** @test */
    public function it_can_get_high_accuracy_meters()
    {
        $highAccuracyMeter = EnergyMeter::factory()->create([
            'accuracy_class' => 0.5,
            'status' => 'active'
        ]);
        $lowAccuracyMeter = EnergyMeter::factory()->create([
            'accuracy_class' => 2.0,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters/high-accuracy?threshold=1.0');

        $response->assertStatus(200);
        $this->assertTrue(collect($response->json('data'))->contains('id', $highAccuracyMeter->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $lowAccuracyMeter->id));
    }

    /** @test */
    public function it_respects_pagination_limits()
    {
        EnergyMeter::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    /** @test */
    public function it_enforces_maximum_pagination_limit()
    {
        EnergyMeter::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-meters?per_page=150');

        $response->assertStatus(200);
        $this->assertCount(100, $response->json('data')); // Máximo 100
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/energy-meters');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_logs_activity_when_creating()
    {
        $data = [
            'meter_number' => 'MTR-003',
            'name' => 'Test Meter',
            'meter_type' => 'smart_meter',
            'status' => 'active',
            'meter_category' => 'electricity',
            'serial_number' => 'SN789012',
            'customer_id' => $this->customer->id,
            'installation_date' => now()->subDays(5)->format('Y-m-d'),
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/energy-meters', $data);

        // Verificar que se creó el medidor
        $this->assertDatabaseHas('energy_meters', [
            'meter_number' => 'MTR-003',
        ]);
    }

    /** @test */
    public function it_logs_activity_when_updating()
    {
        $energyMeter = EnergyMeter::factory()->create();

        $this->actingAs($this->user)
            ->putJson("/api/v1/energy-meters/{$energyMeter->id}", [
                'name' => 'Updated Name'
            ]);

        // Verificar que se actualizó el medidor
        $this->assertDatabaseHas('energy_meters', [
            'id' => $energyMeter->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_logs_activity_when_deleting()
    {
        $energyMeter = EnergyMeter::factory()->create();

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/energy-meters/{$energyMeter->id}");

        // Verificar que se eliminó el medidor
        $this->assertDatabaseMissing('energy_meters', [
            'id' => $energyMeter->id,
        ]);
    }
}
