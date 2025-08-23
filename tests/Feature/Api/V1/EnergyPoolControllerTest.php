<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergyPool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnergyPoolControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_paginated_energy_pools()
    {
        EnergyPool::factory()->count(15)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'pool_number', 'name', 'pool_type', 'status', 'energy_category',
                        'total_capacity_mw', 'available_capacity_mw', 'utilization_percentage'
                    ]
                ],
                'meta' => ['current_page', 'total', 'per_page', 'last_page']
            ]);

        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_index_with_filters()
    {
        EnergyPool::factory()->create(['pool_type' => 'trading', 'status' => 'active']);
        EnergyPool::factory()->create(['pool_type' => 'storage', 'status' => 'inactive']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools?pool_type=trading&status=active');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_index_with_search()
    {
        EnergyPool::factory()->create(['name' => 'Solar Pool']);
        EnergyPool::factory()->create(['name' => 'Wind Pool']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools?search=Solar');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_index_with_sorting()
    {
        EnergyPool::factory()->create(['name' => 'B Pool']);
        EnergyPool::factory()->create(['name' => 'A Pool']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('A Pool', $response->json('data.0.name'));
    }

    public function test_store_creates_new_energy_pool()
    {
        $data = [
            'pool_number' => 'POOL-001',
            'name' => 'Test Energy Pool',
            'pool_type' => 'trading',
            'status' => 'active',
            'energy_category' => 'renewable',
            'total_capacity_mw' => 100.0,
            'available_capacity_mw' => 80.0
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-pools', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'pool_number', 'name', 'pool_type', 'status', 'energy_category',
                    'total_capacity_mw', 'available_capacity_mw'
                ]
            ]);

        $this->assertDatabaseHas('energy_pools', [
            'pool_number' => 'POOL-001',
            'name' => 'Test Energy Pool'
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-pools', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pool_number', 'name', 'pool_type', 'status', 'energy_category']);
    }

    public function test_store_validates_unique_pool_number()
    {
        EnergyPool::factory()->create(['pool_number' => 'POOL-001']);

        $data = [
            'pool_number' => 'POOL-001',
            'name' => 'Test Pool',
            'pool_type' => 'trading',
            'status' => 'active',
            'energy_category' => 'renewable'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/energy-pools', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pool_number']);
    }

    public function test_show_returns_energy_pool()
    {
        $energyPool = EnergyPool::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/energy-pools/{$energyPool->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'pool_number', 'name', 'pool_type', 'status', 'energy_category',
                    'total_capacity_mw', 'available_capacity_mw', 'utilization_percentage'
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent_pool()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_energy_pool()
    {
        $energyPool = EnergyPool::factory()->create();
        $updateData = ['name' => 'Updated Pool Name'];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-pools/{$energyPool->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('energy_pools', [
            'id' => $energyPool->id,
            'name' => 'Updated Pool Name'
        ]);
    }

    public function test_update_validates_unique_pool_number()
    {
        $pool1 = EnergyPool::factory()->create(['pool_number' => 'POOL-001']);
        $pool2 = EnergyPool::factory()->create(['pool_number' => 'POOL-002']);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/energy-pools/{$pool2->id}", ['pool_number' => 'POOL-001']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pool_number']);
    }

    public function test_destroy_deletes_energy_pool()
    {
        $energyPool = EnergyPool::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/energy-pools/{$energyPool->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('energy_pools', ['id' => $energyPool->id]);
    }

    public function test_statistics_returns_pool_statistics()
    {
        EnergyPool::factory()->count(5)->create(['status' => 'active']);
        EnergyPool::factory()->count(3)->create(['status' => 'inactive']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_pools', 'active_pools', 'inactive_pools',
                'by_type', 'by_status', 'by_category'
            ]);

        $this->assertEquals(8, $response->json('total_pools'));
        $this->assertEquals(5, $response->json('active_pools'));
    }

    public function test_types_returns_pool_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/types');

        $response->assertStatus(200)
            ->assertJsonStructure(['trading', 'storage', 'distribution']);
    }

    public function test_statuses_returns_pool_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/statuses');

        $response->assertStatus(200)
            ->assertJsonStructure(['active', 'inactive', 'maintenance', 'decommissioned']);
    }

    public function test_categories_returns_energy_categories()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['renewable', 'fossil', 'nuclear', 'hybrid']);
    }

    public function test_update_status_updates_pool_status()
    {
        $energyPool = EnergyPool::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/energy-pools/{$energyPool->id}/update-status", [
                'status' => 'maintenance'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('energy_pools', [
            'id' => $energyPool->id,
            'status' => 'maintenance'
        ]);
    }

    public function test_update_status_validates_status()
    {
        $energyPool = EnergyPool::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/energy-pools/{$energyPool->id}/update-status", [
                'status' => 'invalid_status'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_duplicate_creates_copy_of_pool()
    {
        $energyPool = EnergyPool::factory()->create([
            'pool_number' => 'POOL-001',
            'name' => 'Original Pool'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-pools/{$energyPool->id}/duplicate");

        $response->assertStatus(201);
        $this->assertDatabaseHas('energy_pools', [
            'name' => 'Original Pool (Copy)',
            'pool_number' => 'POOL-001-Copy'
        ]);
    }

    public function test_duplicate_validates_unique_pool_number()
    {
        $energyPool = EnergyPool::factory()->create(['pool_number' => 'POOL-001']);
        EnergyPool::factory()->create(['pool_number' => 'POOL-001-Copy']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/energy-pools/{$energyPool->id}/duplicate");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pool_number']);
    }

    public function test_active_returns_active_pools()
    {
        EnergyPool::factory()->create(['status' => 'active']);
        EnergyPool::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/active');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_type_returns_pools_by_type()
    {
        EnergyPool::factory()->create(['pool_type' => 'trading']);
        EnergyPool::factory()->create(['pool_type' => 'storage']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/by-type/trading');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_category_returns_pools_by_category()
    {
        EnergyPool::factory()->create(['energy_category' => 'renewable']);
        EnergyPool::factory()->create(['energy_category' => 'fossil']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/by-category/renewable');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_high_efficiency_returns_high_efficiency_pools()
    {
        EnergyPool::factory()->create(['efficiency_rating' => 95.0]);
        EnergyPool::factory()->create(['efficiency_rating' => 75.0]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/high-efficiency');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_high_availability_returns_high_availability_pools()
    {
        EnergyPool::factory()->create(['availability_factor' => 98.0]);
        EnergyPool::factory()->create(['availability_factor' => 85.0]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/high-availability');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_region_returns_pools_by_region()
    {
        EnergyPool::factory()->create(['region' => 'North']);
        EnergyPool::factory()->create(['region' => 'South']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/by-region/North');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_country_returns_pools_by_country()
    {
        EnergyPool::factory()->create(['country' => 'Spain']);
        EnergyPool::factory()->create(['country' => 'France']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/by-country/Spain');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_pending_approval_returns_pending_approval_pools()
    {
        EnergyPool::factory()->create(['approved_by' => null]);
        EnergyPool::factory()->create(['approved_by' => 1]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/pending-approval');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_approved_returns_approved_pools()
    {
        EnergyPool::factory()->create(['approved_by' => null]);
        EnergyPool::factory()->create(['approved_by' => 1]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools/approved');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_pagination_with_limit()
    {
        EnergyPool::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/energy-pools?per_page=10');

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/energy-pools');
        $response->assertStatus(401);
    }

    public function test_logs_activity_on_create()
    {
        $data = [
            'pool_number' => 'POOL-001',
            'name' => 'Test Pool',
            'pool_type' => 'trading',
            'status' => 'active',
            'energy_category' => 'renewable'
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/energy-pools', $data);

        // Verificar que se registró la actividad (si tienes un sistema de logging)
        // $this->assertDatabaseHas('activity_logs', [...]);
    }
}
