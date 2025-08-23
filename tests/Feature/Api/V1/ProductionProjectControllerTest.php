<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\EnergySource;
use App\Models\ProductionProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class ProductionProjectControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Organization $organization;
    protected EnergySource $energySource;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario autenticado
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Crear organización
        $this->organization = Organization::factory()->create();
        
        // Crear fuente de energía
        $this->energySource = EnergySource::factory()->create();
    }

    /** @test */
    public function it_can_list_production_projects()
    {
        // Crear algunos proyectos de producción
        ProductionProject::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'project_type',
                            'status',
                            'capacity_kw',
                            'created_at'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page'
                    ]
                ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_can_filter_production_projects_by_type()
    {
        // Crear proyectos de diferentes tipos
        ProductionProject::factory()->create([
            'project_type' => 'solar_farm',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'project_type' => 'wind_farm',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?project_type=solar_farm');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('solar_farm', $response->json('data.0.project_type'));
    }

    /** @test */
    public function it_can_filter_production_projects_by_status()
    {
        // Crear proyectos de diferentes estados
        ProductionProject::factory()->create([
            'status' => 'planning',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'status' => 'in_progress',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?status=planning');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('planning', $response->json('data.0.status'));
    }

    /** @test */
    public function it_can_search_production_projects()
    {
        // Crear proyecto con nombre específico
        ProductionProject::factory()->create([
            'name' => 'Solar Farm Madrid',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?search=Madrid');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('Madrid', $response->json('data.0.name'));
    }

    /** @test */
    public function it_can_create_production_project()
    {
        $projectData = [
            'name' => 'Test Solar Project',
            'slug' => 'test-solar-project',
            'description' => 'A test solar energy project',
            'project_type' => 'solar_farm',
            'technology_type' => 'photovoltaic',
            'status' => 'planning',
            'organization_id' => $this->organization->id,
            'owner_user_id' => $this->user->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
            'capacity_kw' => 1000.0,
            'is_active' => true,
            'is_public' => false,
        ];

        $response = $this->postJson('/api/v1/production-projects', $projectData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'project_type',
                        'status',
                        'created_at'
                    ]
                ]);

        $this->assertDatabaseHas('production_projects', [
            'name' => 'Test Solar Project',
            'project_type' => 'solar_farm',
            'status' => 'planning'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_project()
    {
        $response = $this->postJson('/api/v1/production-projects', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'name',
                    'slug',
                    'project_type',
                    'technology_type',
                    'status',
                    'organization_id',
                    'owner_user_id',
                    'energy_source_id',
                    'created_by'
                ]);
    }

    /** @test */
    public function it_can_show_production_project()
    {
        $project = ProductionProject::factory()->create([
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/production-projects/{$project->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'project_type',
                        'status',
                        'organization',
                        'owner_user',
                        'energy_source',
                        'created_by_user'
                    ]
                ]);

        $this->assertEquals($project->id, $response->json('data.id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_project()
    {
        $response = $this->getJson('/api/v1/production-projects/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_production_project()
    {
        $project = ProductionProject::factory()->create([
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'name' => 'Updated Project Name',
            'capacity_kw' => 2000.0,
            'status' => 'in_progress'
        ];

        $response = $this->putJson("/api/v1/production-projects/{$project->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'capacity_kw',
                        'status',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('production_projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
            'capacity_kw' => 2000.0,
            'status' => 'in_progress'
        ]);
    }

    /** @test */
    public function it_can_delete_production_project()
    {
        $project = ProductionProject::factory()->create([
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/v1/production-projects/{$project->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Proyecto de producción eliminado exitosamente'
                ]);

        $this->assertSoftDeleted('production_projects', ['id' => $project->id]);
    }

    /** @test */
    public function it_can_get_production_project_statistics()
    {
        // Crear proyectos de diferentes tipos y estados
        ProductionProject::factory()->count(3)->create([
            'project_type' => 'solar_farm',
            'status' => 'in_progress',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->count(2)->create([
            'project_type' => 'wind_farm',
            'status' => 'completed',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_projects',
                        'active_projects',
                        'completed_projects',
                        'projects_by_type',
                        'projects_by_status'
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.total_projects'));
        $this->assertEquals(3, $response->json('data.projects_by_type.solar_farm'));
        $this->assertEquals(2, $response->json('data.projects_by_type.wind_farm'));
    }

    /** @test */
    public function it_can_get_project_types()
    {
        $response = $this->getJson('/api/v1/production-projects/types');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'solar_farm',
                        'wind_farm',
                        'hydroelectric',
                        'biomass',
                        'geothermal',
                        'hybrid',
                        'storage',
                        'grid_upgrade',
                        'other'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_project_statuses()
    {
        $response = $this->getJson('/api/v1/production-projects/statuses');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'planning',
                        'approved',
                        'in_progress',
                        'on_hold',
                        'completed',
                        'cancelled',
                        'maintenance'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_technology_types()
    {
        $response = $this->getJson('/api/v1/production-projects/technology-types');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'photovoltaic',
                        'concentrated_solar',
                        'wind_turbine',
                        'hydroelectric',
                        'biomass_plant',
                        'geothermal_plant',
                        'other'
                    ]
                ]);
    }

    /** @test */
    public function it_can_toggle_project_active_status()
    {
        $project = ProductionProject::factory()->create([
            'is_active' => true,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/production-projects/{$project->id}/toggle-active");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'is_active',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('production_projects', [
            'id' => $project->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function it_can_toggle_project_public_status()
    {
        $project = ProductionProject::factory()->create([
            'is_public' => false,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/production-projects/{$project->id}/toggle-public");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'is_public',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('production_projects', [
            'id' => $project->id,
            'is_public' => true
        ]);
    }

    /** @test */
    public function it_can_update_project_status()
    {
        $project = ProductionProject::factory()->create([
            'status' => 'planning',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/production-projects/{$project->id}/update-status", [
            'status' => 'in_progress'
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

        $this->assertDatabaseHas('production_projects', [
            'id' => $project->id,
            'status' => 'in_progress'
        ]);
    }

    /** @test */
    public function it_validates_status_when_updating()
    {
        $project = ProductionProject::factory()->create([
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/production-projects/{$project->id}/update-status", [
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_duplicate_project()
    {
        $project = ProductionProject::factory()->create([
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/production-projects/{$project->id}/duplicate", [
            'name' => 'Duplicated Project'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'status',
                        'completion_percentage',
                        'created_at'
                    ]
                ]);

        $this->assertDatabaseHas('production_projects', [
            'name' => 'Duplicated Project',
            'status' => 'planning',
            'completion_percentage' => 0
        ]);
    }

    /** @test */
    public function it_can_filter_projects_by_capacity_range()
    {
        ProductionProject::factory()->create([
            'capacity_kw' => 500,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'capacity_kw' => 1500,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?capacity_min=1000&capacity_max=2000');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(1500, $response->json('data.0.capacity_kw'));
    }

    /** @test */
    public function it_can_filter_projects_by_completion_percentage()
    {
        ProductionProject::factory()->create([
            'completion_percentage' => 25,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'completion_percentage' => 75,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?completion_min=50&completion_max=100');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(75, $response->json('data.0.completion_percentage'));
    }

    /** @test */
    public function it_can_filter_projects_by_investment_range()
    {
        ProductionProject::factory()->create([
            'total_investment' => 50000,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'total_investment' => 150000,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?investment_min=100000&investment_max=200000');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(150000, $response->json('data.0.total_investment'));
    }

    /** @test */
    public function it_can_sort_projects_by_different_fields()
    {
        ProductionProject::factory()->create([
            'name' => 'A Project',
            'capacity_kw' => 1000,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'name' => 'Z Project',
            'capacity_kw' => 500,
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        // Ordenar por nombre ascendente
        $response = $this->getJson('/api/v1/production-projects?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $this->assertEquals('A Project', $response->json('data.0.name'));

        // Ordenar por capacidad descendente
        $response = $this->getJson('/api/v1/production-projects?sort_by=capacity_kw&sort_direction=desc');

        $response->assertStatus(200);
        $this->assertEquals(1000, $response->json('data.0.capacity_kw'));
    }

    /** @test */
    public function it_can_filter_projects_by_location()
    {
        ProductionProject::factory()->create([
            'location_city' => 'Madrid',
            'location_country' => 'ES',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        ProductionProject::factory()->create([
            'location_city' => 'Barcelona',
            'location_country' => 'ES',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?location_city=Madrid');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Madrid', $response->json('data.0.location_city'));
    }

    /** @test */
    public function it_can_filter_projects_by_date_ranges()
    {
        $oldProject = ProductionProject::factory()->create([
            'construction_start_date' => '2023-01-01',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $newProject = ProductionProject::factory()->create([
            'construction_start_date' => '2024-01-01',
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?construction_start_from=2024-01-01');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($newProject->id, $response->json('data.0.id'));
    }

    /** @test */
    public function it_respects_pagination()
    {
        ProductionProject::factory()->count(25)->create([
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /** @test */
    public function it_limits_per_page_to_maximum()
    {
        ProductionProject::factory()->count(150)->create([
            'organization_id' => $this->organization->id,
            'energy_source_id' => $this->energySource->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/production-projects?per_page=200');

        $response->assertStatus(200);
        $this->assertCount(100, $response->json('data')); // Máximo 100
    }
}
