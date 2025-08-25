<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Device;
use App\Models\ConsumptionPoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class DeviceControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $user;
    protected $device;
    protected $consumptionPoint;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario autenticado
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Crear punto de consumo
        $this->consumptionPoint = ConsumptionPoint::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        // Crear dispositivo de prueba
        $this->device = Device::factory()->create([
            'user_id' => $this->user->id,
            'consumption_point_id' => $this->consumptionPoint->id
        ]);
    }

    /** @test */
    public function it_can_list_devices()
    {
        $response = $this->getJson('/api/v1/devices');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'name', 'type', 'user_id', 'consumption_point_id',
                            'api_endpoint', 'active', 'last_communication', 'created_at', 'updated_at'
                        ]
                    ],
                    'links', 'meta'
                ]);
    }

    /** @test */
    public function it_can_create_device()
    {
        $deviceData = [
            'name' => 'Smart Meter Test',
            'type' => 'smart_meter',
            'user_id' => $this->user->id,
            'consumption_point_id' => $this->consumptionPoint->id,
            'api_endpoint' => 'https://api.example.com/device',
            'active' => true
        ];

        $response = $this->postJson('/api/v1/devices', $deviceData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'name', 'type', 'user_id', 'consumption_point_id',
                        'api_endpoint', 'active', 'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('devices', [
            'name' => 'Smart Meter Test',
            'type' => 'smart_meter'
        ]);
    }

    /** @test */
    public function it_can_show_device()
    {
        $response = $this->getJson("/api/v1/devices/{$this->device->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'name', 'type', 'user_id', 'consumption_point_id',
                        'api_endpoint', 'active', 'last_communication', 'created_at', 'updated_at'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_device()
    {
        $updateData = [
            'name' => 'Updated Smart Meter',
            'active' => false
        ];

        $response = $this->putJson("/api/v1/devices/{$this->device->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'name', 'type', 'user_id', 'consumption_point_id',
                        'api_endpoint', 'active', 'last_communication', 'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('devices', [
            'id' => $this->device->id,
            'name' => 'Updated Smart Meter',
            'active' => false
        ]);
    }

    /** @test */
    public function it_can_delete_device()
    {
        $response = $this->deleteJson("/api/v1/devices/{$this->device->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Device deleted successfully']);

        $this->assertSoftDeleted('devices', ['id' => $this->device->id]);
    }

    /** @test */
    public function it_can_activate_device()
    {
        $this->device->update(['active' => false]);

        $response = $this->postJson("/api/v1/devices/{$this->device->id}/activate");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Device activated successfully']);

        $this->assertDatabaseHas('devices', [
            'id' => $this->device->id,
            'active' => true
        ]);
    }

    /** @test */
    public function it_can_deactivate_device()
    {
        $this->device->update(['active' => true]);

        $response = $this->postJson("/api/v1/devices/{$this->device->id}/deactivate");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Device deactivated successfully']);

        $this->assertDatabaseHas('devices', [
            'id' => $this->device->id,
            'active' => false
        ]);
    }

    /** @test */
    public function it_can_update_device_communication()
    {
        $response = $this->postJson("/api/v1/devices/{$this->device->id}/update-communication");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Device communication updated successfully']);

        $this->assertDatabaseHas('devices', [
            'id' => $this->device->id
        ]);
    }

    /** @test */
    public function it_can_get_device_statistics()
    {
        $response = $this->getJson('/api/v1/devices/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_devices', 'active_devices', 'inactive_devices',
                        'devices_by_type', 'recent_communications'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_device_types()
    {
        $response = $this->getJson('/api/v1/devices/types');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['value', 'label', 'description', 'capabilities']
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_device_capabilities()
    {
        $response = $this->getJson('/api/v1/devices/capabilities');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['name', 'description', 'supported_types']
                    ]
                ]);
    }

    /** @test */
    public function it_can_bulk_update_devices()
    {
        $device2 = Device::factory()->create(['user_id' => $this->user->id]);
        
        $bulkData = [
            'device_ids' => [$this->device->id, $device2->id],
            'updates' => ['active' => false]
        ];

        $response = $this->postJson('/api/v1/devices/bulk-update', $bulkData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Devices updated successfully']);

        $this->assertDatabaseHas('devices', ['id' => $this->device->id, 'active' => false]);
        $this->assertDatabaseHas('devices', ['id' => $device2->id, 'active' => false]);
    }

    /** @test */
    public function it_can_bulk_delete_devices()
    {
        $device2 = Device::factory()->create(['user_id' => $this->user->id]);
        
        $bulkData = [
            'device_ids' => [$this->device->id, $device2->id]
        ];

        $response = $this->postJson('/api/v1/devices/bulk-delete', $bulkData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Devices deleted successfully']);

        $this->assertSoftDeleted('devices', ['id' => $this->device->id]);
        $this->assertSoftDeleted('devices', ['id' => $device2->id]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/v1/devices', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'type', 'user_id']);
    }

    /** @test */
    public function it_validates_device_type_on_create()
    {
        $deviceData = [
            'name' => 'Test Device',
            'type' => 'invalid_type',
            'user_id' => $this->user->id
        ];

        $response = $this->postJson('/api/v1/devices', $deviceData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        // Desautenticar usuario
        auth()->logout();

        $response = $this->getJson('/api/v1/devices');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_devices_by_type()
    {
        $smartMeter = Device::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'smart_meter'
        ]);

        $response = $this->getJson('/api/v1/devices?type=smart_meter');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_devices_by_status()
    {
        $activeDevice = Device::factory()->create([
            'user_id' => $this->user->id,
            'active' => true
        ]);

        $response = $this->getJson('/api/v1/devices?active=1');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_sort_devices()
    {
        $response = $this->getJson('/api/v1/devices?sort=name&order=desc');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_paginate_devices()
    {
        // Crear múltiples dispositivos
        Device::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/devices?per_page=10');

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data');
    }
}
