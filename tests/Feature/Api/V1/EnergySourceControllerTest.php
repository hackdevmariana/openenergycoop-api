<?php

namespace Tests\Feature\Api\V1;

use App\Models\EnergySource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnergySourceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_energy_sources()
    {
        EnergySource::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/energy-sources');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'slug', 'description', 'category', 'type', 'status',
                        'efficiency_typical', 'capacity_typical', 'is_renewable', 'is_clean',
                        'is_active', 'created_at'
                    ]
                ],
                'meta' => ['current_page', 'total', 'per_page', 'last_page']
            ]);

        $this->assertEquals(3, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_category()
    {
        EnergySource::factory()->create(['category' => 'renewable']);
        EnergySource::factory()->create(['category' => 'non_renewable']);

        $response = $this->getJson('/api/v1/energy-sources?category=renewable');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('renewable', $response->json('data.0.category'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_type()
    {
        EnergySource::factory()->create(['type' => 'photovoltaic']);
        EnergySource::factory()->create(['type' => 'wind_turbine']);

        $response = $this->getJson('/api/v1/energy-sources?type=photovoltaic');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('photovoltaic', $response->json('data.0.type'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_status()
    {
        EnergySource::factory()->create(['status' => 'active']);
        EnergySource::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/energy-sources?status=active');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_renewable_status()
    {
        EnergySource::factory()->create(['is_renewable' => true]);
        EnergySource::factory()->create(['is_renewable' => false]);

        $response = $this->getJson('/api/v1/energy-sources?is_renewable=true');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertTrue($response->json('data.0.is_renewable'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_clean_status()
    {
        EnergySource::factory()->create(['is_clean' => true]);
        EnergySource::factory()->create(['is_clean' => false]);

        $response = $this->getJson('/api/v1/energy-sources?is_clean=true');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertTrue($response->json('data.0.is_clean'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_active_status()
    {
        EnergySource::factory()->create(['is_active' => true]);
        EnergySource::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/energy-sources?is_active=true');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertTrue($response->json('data.0.is_active'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_featured_status()
    {
        EnergySource::factory()->create(['is_featured' => true]);
        EnergySource::factory()->create(['is_featured' => false]);

        $response = $this->getJson('/api/v1/energy-sources?is_featured=true');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertTrue($response->json('data.0.is_featured'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_efficiency_range()
    {
        EnergySource::factory()->create(['efficiency_typical' => 85.0]);
        EnergySource::factory()->create(['efficiency_typical' => 75.0]);
        EnergySource::factory()->create(['efficiency_typical' => 95.0]);

        $response = $this->getJson('/api/v1/energy-sources?efficiency_min=80&efficiency_max=90');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals(85.0, $response->json('data.0.efficiency_typical'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_capacity_range()
    {
        EnergySource::factory()->create(['capacity_typical' => 500.0]);
        EnergySource::factory()->create(['capacity_typical' => 1000.0]);
        EnergySource::factory()->create(['capacity_typical' => 2000.0]);

        $response = $this->getJson('/api/v1/energy-sources?capacity_min=800&capacity_max=1500');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals(1000.0, $response->json('data.0.capacity_typical'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_carbon_footprint()
    {
        EnergySource::factory()->create(['carbon_footprint_kg_kwh' => 0.1]);
        EnergySource::factory()->create(['carbon_footprint_kg_kwh' => 0.5]);
        EnergySource::factory()->create(['carbon_footprint_kg_kwh' => 1.0]);

        $response = $this->getJson('/api/v1/energy-sources?carbon_footprint_max=0.3');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals(0.1, $response->json('data.0.carbon_footprint_kg_kwh'));
    }

    /** @test */
    public function it_can_filter_energy_sources_by_cost_range()
    {
        EnergySource::factory()->create(['installation_cost_per_kw' => 2000.0]);
        EnergySource::factory()->create(['installation_cost_per_kw' => 4000.0]);
        EnergySource::factory()->create(['installation_cost_per_kw' => 6000.0]);

        $response = $this->getJson('/api/v1/energy-sources?cost_min=3000&cost_max=5000');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals(4000.0, $response->json('data.0.installation_cost_per_kw'));
    }

    /** @test */
    public function it_can_search_energy_sources()
    {
        EnergySource::factory()->create(['name' => 'Solar Panel High Efficiency']);
        EnergySource::factory()->create(['name' => 'Wind Turbine Standard']);
        EnergySource::factory()->create(['description' => 'High efficiency solar technology']);

        $response = $this->getJson('/api/v1/energy-sources?search=solar');

        $response->assertOk();
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_sort_energy_sources()
    {
        EnergySource::factory()->create(['name' => 'Zebra Energy']);
        EnergySource::factory()->create(['name' => 'Alpha Energy']);
        EnergySource::factory()->create(['name' => 'Beta Energy']);

        $response = $this->getJson('/api/v1/energy-sources?sort_by=name&sort_direction=asc');

        $response->assertOk();
        $this->assertEquals('Alpha Energy', $response->json('data.0.name'));
        $this->assertEquals('Beta Energy', $response->json('data.1.name'));
        $this->assertEquals('Zebra Energy', $response->json('data.2.name'));
    }

    /** @test */
    public function it_can_paginate_energy_sources()
    {
        EnergySource::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/energy-sources?per_page=10');

        $response->assertOk();
        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /** @test */
    public function it_can_create_energy_source()
    {
        $data = [
            'name' => 'Solar Panel High Efficiency',
            'description' => 'High efficiency photovoltaic solar panel',
            'category' => 'renewable',
            'type' => 'photovoltaic',
            'status' => 'active',
            'efficiency_typical' => 85.5,
            'capacity_typical' => 500.0,
            'is_renewable' => true,
            'is_clean' => true,
        ];

        $response = $this->postJson('/api/v1/energy-sources', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'name', 'slug', 'description', 'category', 'type', 'status',
                    'efficiency_typical', 'capacity_typical', 'is_renewable', 'is_clean'
                ]
            ]);

        $this->assertDatabaseHas('energy_sources', [
            'name' => 'Solar Panel High Efficiency',
            'category' => 'renewable',
            'type' => 'photovoltaic',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_energy_source()
    {
        $response = $this->postJson('/api/v1/energy-sources', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'category', 'type', 'status']);
    }

    /** @test */
    public function it_validates_category_values_when_creating_energy_source()
    {
        $data = [
            'name' => 'Test Energy Source',
            'category' => 'invalid_category',
            'type' => 'photovoltaic',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/energy-sources', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['category']);
    }

    /** @test */
    public function it_validates_type_values_when_creating_energy_source()
    {
        $data = [
            'name' => 'Test Energy Source',
            'category' => 'renewable',
            'type' => 'invalid_type',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/energy-sources', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function it_validates_status_values_when_creating_energy_source()
    {
        $data = [
            'name' => 'Test Energy Source',
            'category' => 'renewable',
            'type' => 'photovoltaic',
            'status' => 'invalid_status',
        ];

        $response = $this->postJson('/api/v1/energy-sources', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_validates_efficiency_range_when_creating_energy_source()
    {
        $data = [
            'name' => 'Test Energy Source',
            'category' => 'renewable',
            'type' => 'photovoltaic',
            'status' => 'active',
            'efficiency_typical' => 150.0, // Invalid: > 100
        ];

        $response = $this->postJson('/api/v1/energy-sources', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['efficiency_typical']);
    }

    /** @test */
    public function it_can_show_energy_source()
    {
        $energySource = EnergySource::factory()->create();

        $response = $this->getJson("/api/v1/energy-sources/{$energySource->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'slug', 'description', 'category', 'type', 'status',
                    'efficiency_typical', 'capacity_typical', 'is_renewable', 'is_clean',
                    'is_active', 'created_at'
                ]
            ]);

        $this->assertEquals($energySource->id, $response->json('data.id'));
    }

    /** @test */
    public function it_returns_404_for_non_existent_energy_source()
    {
        $response = $this->getJson('/api/v1/energy-sources/999');

        $response->assertNotFound();
    }

    /** @test */
    public function it_can_update_energy_source()
    {
        $energySource = EnergySource::factory()->create();
        $updateData = [
            'name' => 'Updated Energy Source Name',
            'efficiency_typical' => 90.0,
        ];

        $response = $this->putJson("/api/v1/energy-sources/{$energySource->id}", $updateData);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'efficiency_typical', 'updated_at']
            ]);

        $this->assertDatabaseHas('energy_sources', [
            'id' => $energySource->id,
            'name' => 'Updated Energy Source Name',
            'efficiency_typical' => 90.0,
        ]);
    }

    /** @test */
    public function it_validates_unique_slug_when_updating_energy_source()
    {
        $energySource1 = EnergySource::factory()->create(['slug' => 'energy-source-1']);
        $energySource2 = EnergySource::factory()->create(['slug' => 'energy-source-2']);

        $response = $this->putJson("/api/v1/energy-sources/{$energySource2->id}", [
            'slug' => 'energy-source-1'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function it_can_delete_energy_source()
    {
        $energySource = EnergySource::factory()->create();

        $response = $this->deleteJson("/api/v1/energy-sources/{$energySource->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Fuente de energía eliminada exitosamente']);

        $this->assertSoftDeleted('energy_sources', ['id' => $energySource->id]);
    }

    /** @test */
    public function it_can_get_energy_source_statistics()
    {
        EnergySource::factory()->create(['category' => 'renewable', 'is_active' => true]);
        EnergySource::factory()->create(['category' => 'renewable', 'is_active' => true]);
        EnergySource::factory()->create(['category' => 'non_renewable', 'is_active' => false]);

        $response = $this->getJson('/api/v1/energy-sources/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total_sources', 'active_sources', 'renewable_sources', 'clean_sources',
                    'average_efficiency', 'total_capacity_kw', 'sources_by_category',
                    'sources_by_type', 'sources_by_status'
                ]
            ]);

        $this->assertEquals(3, $response->json('data.total_sources'));
        $this->assertEquals(2, $response->json('data.active_sources'));
        $this->assertEquals(2, $response->json('data.renewable_sources'));
    }

    /** @test */
    public function it_can_get_energy_source_categories()
    {
        $response = $this->getJson('/api/v1/energy-sources/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['renewable', 'non_renewable', 'hybrid']
            ]);
    }

    /** @test */
    public function it_can_get_energy_source_types()
    {
        $response = $this->getJson('/api/v1/energy-sources/types');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['photovoltaic', 'concentrated_solar', 'wind_turbine', 'hydroelectric']
            ]);
    }

    /** @test */
    public function it_can_get_energy_source_statuses()
    {
        $response = $this->getJson('/api/v1/energy-sources/statuses');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['active', 'inactive', 'maintenance', 'development', 'testing', 'deprecated']
            ]);
    }

    /** @test */
    public function it_can_toggle_energy_source_active_status()
    {
        $energySource = EnergySource::factory()->create(['is_active' => true]);

        $response = $this->postJson("/api/v1/energy-sources/{$energySource->id}/toggle-active");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'is_active', 'updated_at']
            ]);

        $this->assertFalse($response->json('data.is_active'));
        $this->assertDatabaseHas('energy_sources', [
            'id' => $energySource->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function it_can_toggle_energy_source_featured_status()
    {
        $energySource = EnergySource::factory()->create(['is_featured' => false]);

        $response = $this->postJson("/api/v1/energy-sources/{$energySource->id}/toggle-featured");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'is_featured', 'updated_at']
            ]);

        $this->assertTrue($response->json('data.is_featured'));
        $this->assertDatabaseHas('energy_sources', [
            'id' => $energySource->id,
            'is_featured' => true
        ]);
    }

    /** @test */
    public function it_can_update_energy_source_status()
    {
        $energySource = EnergySource::factory()->create(['status' => 'active']);

        $response = $this->postJson("/api/v1/energy-sources/{$energySource->id}/update-status", [
            'status' => 'maintenance'
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'status', 'updated_at']
            ]);

        $this->assertEquals('maintenance', $response->json('data.status'));
        $this->assertDatabaseHas('energy_sources', [
            'id' => $energySource->id,
            'status' => 'maintenance'
        ]);
    }

    /** @test */
    public function it_validates_status_when_updating_energy_source_status()
    {
        $energySource = EnergySource::factory()->create();

        $response = $this->postJson("/api/v1/energy-sources/{$energySource->id}/update-status", [
            'status' => 'invalid_status'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_can_duplicate_energy_source()
    {
        $energySource = EnergySource::factory()->create([
            'name' => 'Original Energy Source',
            'is_active' => true,
            'is_featured' => true
        ]);

        $response = $this->postJson("/api/v1/energy-sources/{$energySource->id}/duplicate", [
            'name' => 'Duplicated Energy Source'
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'status', 'is_active', 'is_featured', 'created_at']
            ]);

        $this->assertEquals('Duplicated Energy Source', $response->json('data.name'));
        $this->assertTrue($response->json('data.is_active'));
        $this->assertFalse($response->json('data.is_featured'));

        $this->assertDatabaseHas('energy_sources', [
            'name' => 'Duplicated Energy Source',
            'is_active' => true,
            'is_featured' => false
        ]);
    }

    /** @test */
    public function it_can_get_featured_energy_sources()
    {
        EnergySource::factory()->create(['is_featured' => true, 'is_active' => true]);
        EnergySource::factory()->create(['is_featured' => false, 'is_active' => true]);
        EnergySource::factory()->create(['is_featured' => true, 'is_active' => false]);

        $response = $this->getJson('/api/v1/energy-sources/featured');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
        $this->assertTrue($response->json('data.0.is_featured'));
        $this->assertTrue($response->json('data.0.is_active'));
    }

    /** @test */
    public function it_can_get_renewable_energy_sources()
    {
        EnergySource::factory()->create(['is_renewable' => true, 'is_active' => true]);
        EnergySource::factory()->create(['is_renewable' => false, 'is_active' => true]);
        EnergySource::factory()->create(['is_renewable' => true, 'is_active' => false]);

        $response = $this->getJson('/api/v1/energy-sources/renewable');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
        $this->assertTrue($response->json('data.0.is_renewable'));
        $this->assertTrue($response->json('data.0.is_active'));
    }

    /** @test */
    public function it_can_get_clean_energy_sources()
    {
        EnergySource::factory()->create(['is_clean' => true, 'is_active' => true]);
        EnergySource::factory()->create(['is_clean' => false, 'is_active' => true]);
        EnergySource::factory()->create(['is_clean' => true, 'is_active' => false]);

        $response = $this->getJson('/api/v1/energy-sources/clean');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
        $this->assertTrue($response->json('data.0.is_clean'));
        $this->assertTrue($response->json('data.0.is_active'));
    }

    /** @test */
    public function it_respects_limit_parameter_for_featured_sources()
    {
        EnergySource::factory()->count(15)->create(['is_featured' => true, 'is_active' => true]);

        $response = $this->getJson('/api/v1/energy-sources/featured?limit=5');

        $response->assertOk();
        $this->assertEquals(5, count($response->json('data')));
    }

    /** @test */
    public function it_respects_limit_parameter_for_renewable_sources()
    {
        EnergySource::factory()->count(25)->create(['is_renewable' => true, 'is_active' => true]);

        $response = $this->getJson('/api/v1/energy-sources/renewable?limit=10');

        $response->assertOk();
        $this->assertEquals(10, count($response->json('data')));
    }

    /** @test */
    public function it_respects_limit_parameter_for_clean_sources()
    {
        EnergySource::factory()->count(25)->create(['is_clean' => true, 'is_active' => true]);

        $response = $this->getJson('/api/v1/energy-sources/clean?limit=10');

        $response->assertOk();
        $this->assertEquals(10, count($response->json('data')));
    }

    /** @test */
    public function it_limits_maximum_results_for_featured_sources()
    {
        EnergySource::factory()->count(100)->create(['is_featured' => true, 'is_active' => true]);

        $response = $this->getJson('/api/v1/energy-sources/featured?limit=100');

        $response->assertOk();
        $this->assertEquals(50, count($response->json('data'))); // Max limit is 50
    }

    /** @test */
    public function it_limits_maximum_results_for_renewable_sources()
    {
        EnergySource::factory()->count(200)->create(['is_renewable' => true, 'is_active' => true]);

        $response = $this->getJson('/api/v1/energy-sources/renewable?limit=200');

        $response->assertOk();
        $this->assertEquals(100, count($response->json('data'))); // Max limit is 100
    }

    /** @test */
    public function it_limits_maximum_results_for_clean_sources()
    {
        EnergySource::factory()->count(200)->create(['is_clean' => true, 'is_active' => true]);

        $response = $this->getJson('/api/v1/energy-sources/clean?limit=200');

        $response->assertOk();
        $this->assertEquals(100, count($response->json('data'))); // Max limit is 100
    }
}
