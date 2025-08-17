<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyStorage;
use App\Models\User;
use App\Models\Provider;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class EnergyStorageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    /** @test */
    public function user_can_list_energy_storage_systems()
    {
        Sanctum::actingAs($this->user);
        
        EnergyStorage::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/energy-storages');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id', 'system_id', 'name', 'storage_type',
                            'capacity_kwh', 'status', 'charge_level_percentage'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_create_storage_system()
    {
        Sanctum::actingAs($this->user);
        
        $provider = Provider::factory()->create();
        
        $storageData = [
            'user_id' => $this->user->id,
            'provider_id' => $provider->id,
            'system_id' => 'ES-TEST-001',
            'name' => 'Test Battery System',
            'storage_type' => 'battery_lithium',
            'manufacturer' => 'Tesla',
            'model' => 'Powerwall 3',
            'capacity_kwh' => 100.0,
            'usable_capacity_kwh' => 90.0,
            'max_charge_power_kw' => 25.0,
            'max_discharge_power_kw' => 25.0,
        ];

        $response = $this->postJson('/api/v1/energy-storages', $storageData);

        $response->assertCreated()
            ->assertJsonFragment([
                'system_id' => 'ES-TEST-001',
                'name' => 'Test Battery System',
                'storage_type' => 'battery_lithium'
            ]);
    }

    /** @test */
    public function user_can_start_charging_their_system()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'online',
            'charge_level_percentage' => 50.0
        ]);

        $response = $this->postJson("/api/v1/energy-storages/{$storage->id}/start-charging");

        $response->assertOk()
            ->assertJsonFragment([
                'status' => 'charging',
                'success' => true
            ]);

        $this->assertDatabaseHas('energy_storages', [
            'id' => $storage->id,
            'status' => 'charging'
        ]);
    }

    /** @test */
    public function cannot_charge_fully_charged_system()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'online',
            'charge_level_percentage' => 100.0,
            'max_charge_level' => 100.0
        ]);

        $response = $this->postJson("/api/v1/energy-storages/{$storage->id}/start-charging");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'success' => false,
                'message' => 'El sistema ya está en su nivel máximo de carga'
            ]);
    }

    /** @test */
    public function user_can_start_discharging_their_system()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'standby',
            'charge_level_percentage' => 80.0,
            'min_charge_level' => 10.0
        ]);

        $response = $this->postJson("/api/v1/energy-storages/{$storage->id}/start-discharging");

        $response->assertOk()
            ->assertJsonFragment([
                'status' => 'discharging',
                'success' => true
            ]);
    }

    /** @test */
    public function cannot_discharge_low_battery_system()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'online',
            'charge_level_percentage' => 5.0,
            'min_charge_level' => 10.0
        ]);

        $response = $this->postJson("/api/v1/energy-storages/{$storage->id}/start-discharging");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'success' => false,
                'message' => 'El sistema está en su nivel mínimo de carga'
            ]);
    }

    /** @test */
    public function user_can_stop_system_operation()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->charging()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->postJson("/api/v1/energy-storages/{$storage->id}/stop-operation");

        $response->assertOk()
            ->assertJsonFragment([
                'status' => 'standby',
                'success' => true
            ]);
    }

    /** @test */
    public function cannot_stop_non_operating_system()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'standby'
        ]);

        $response = $this->postJson("/api/v1/energy-storages/{$storage->id}/stop-operation");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'success' => false,
                'message' => 'El sistema no está en operación'
            ]);
    }

    /** @test */
    public function user_can_update_charge_level()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->create([
            'user_id' => $this->user->id,
            'usable_capacity_kwh' => 100.0
        ]);

        $response = $this->postJson("/api/v1/energy-storages/{$storage->id}/update-charge-level", [
            'charge_level_percentage' => 75.0,
            'current_charge_kwh' => 75.0
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'charge_level_percentage' => 75.0,
                'current_charge_kwh' => 75.0
            ]);
    }

    /** @test */
    public function user_can_get_system_performance_metrics()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/energy-storages/{$storage->id}/performance");

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'basic_info' => [
                        'system_id', 'name', 'storage_type', 'status'
                    ],
                    'capacity_metrics' => [
                        'total_capacity_kwh', 'usable_capacity_kwh',
                        'current_charge_kwh', 'charge_level_percentage'
                    ],
                    'efficiency_metrics' => [
                        'round_trip_efficiency', 'current_health_percentage'
                    ],
                    'operational_metrics' => [
                        'cycle_count', 'max_charge_power_kw'
                    ],
                    'financial_metrics'
                ]
            ]);
    }

    /** @test */
    public function user_can_get_their_storage_systems()
    {
        Sanctum::actingAs($this->user);
        
        EnergyStorage::factory()->count(3)->create(['user_id' => $this->user->id]);
        EnergyStorage::factory()->count(2)->create(); // Otros usuarios

        $response = $this->getJson('/api/v1/energy-storages/my-storage-systems');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_get_storage_overview()
    {
        EnergyStorage::factory()->count(5)->create(['is_active' => true]);
        EnergyStorage::factory()->charging()->count(2)->create(['is_active' => true]);
        EnergyStorage::factory()->discharging()->count(1)->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/energy-storages/storage-overview');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_systems',
                    'systems_by_status',
                    'systems_by_type',
                    'total_capacity_mwh',
                    'total_stored_mwh',
                    'average_efficiency',
                    'low_battery_systems'
                ]
            ]);
    }

    /** @test */
    public function user_can_filter_by_storage_type()
    {
        Sanctum::actingAs($this->user);
        
        EnergyStorage::factory()->count(3)->create(['storage_type' => 'battery_lithium']);
        EnergyStorage::factory()->count(2)->create(['storage_type' => 'pumped_hydro']);

        $response = $this->getJson('/api/v1/energy-storages?storage_type=battery_lithium');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_filter_by_capacity_range()
    {
        Sanctum::actingAs($this->user);
        
        EnergyStorage::factory()->create(['capacity_kwh' => 50.0]);
        EnergyStorage::factory()->create(['capacity_kwh' => 150.0]);
        EnergyStorage::factory()->create(['capacity_kwh' => 250.0]);

        $response = $this->getJson('/api/v1/energy-storages?min_capacity=100&max_capacity=200');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.meta.total'));
    }

    /** @test */
    public function cannot_delete_operating_system()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->charging()->create();

        $response = $this->deleteJson("/api/v1/energy-storages/{$storage->id}");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'success' => false,
                'message' => 'No se puede eliminar un sistema que está en operación'
            ]);
    }

    /** @test */
    public function can_delete_offline_system()
    {
        Sanctum::actingAs($this->user);
        
        $storage = EnergyStorage::factory()->create(['status' => 'offline']);

        $response = $this->deleteJson("/api/v1/energy-storages/{$storage->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('energy_storages', ['id' => $storage->id]);
    }

    /** @test */
    public function system_id_must_be_unique()
    {
        Sanctum::actingAs($this->user);
        
        EnergyStorage::factory()->create(['system_id' => 'ES-UNIQUE']);
        
        $provider = Provider::factory()->create();

        $response = $this->postJson('/api/v1/energy-storages', [
            'user_id' => $this->user->id,
            'provider_id' => $provider->id,
            'system_id' => 'ES-UNIQUE',
            'name' => 'Test System',
            'storage_type' => 'battery_lithium',
            'capacity_kwh' => 100,
            'usable_capacity_kwh' => 90,
            'max_charge_power_kw' => 25,
            'max_discharge_power_kw' => 25,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['system_id']);
    }

    /** @test */
    public function guest_can_access_public_storage_overview()
    {
        EnergyStorage::factory()->count(5)->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/energy-storages/storage-overview');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['total_systems', 'systems_by_status']
            ]);
    }

    /** @test */
    public function guest_cannot_access_private_endpoints()
    {
        $response = $this->getJson('/api/v1/energy-storages');
        $response->assertUnauthorized();
    }
}