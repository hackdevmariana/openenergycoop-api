<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\CooperativePlantConfig;
use App\Models\Plant;
use App\Models\User;
use App\Models\EnergyCooperative;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class CooperativePlantConfigApiTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_list_all_cooperative_plant_configs()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        CooperativePlantConfig::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/cooperative-plant-configs');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'cooperative_id',
                            'plant_id',
                            'default',
                            'active',
                            'organization_id',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'links',
                    'meta'
                ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function it_can_show_a_specific_cooperative_plant_config()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $config = CooperativePlantConfig::factory()->create();

        $response = $this->getJson("/api/v1/cooperative-plant-configs/{$config->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $config->id,
                        'cooperative_id' => $config->cooperative_id,
                        'plant_id' => $config->plant_id,
                        'default' => $config->default,
                        'active' => $config->active
                    ]
                ]);
    }

    /** @test */
    public function it_can_create_a_new_cooperative_plant_config()
    {
        $user = User::factory()->create();
        $cooperative = EnergyCooperative::factory()->create();
        $plant = Plant::factory()->create();
        Sanctum::actingAs($user);

        $configData = [
            'cooperative_id' => $cooperative->id,
            'plant_id' => $plant->id,
            'default' => false,
            'active' => true,
            'organization_id' => null
        ];

        $response = $this->postJson('/api/v1/cooperative-plant-configs', $configData);

        $response->assertStatus(201)
                ->assertJson([
                    'data' => [
                        'cooperative_id' => $cooperative->id,
                        'plant_id' => $plant->id,
                        'default' => false,
                        'active' => true
                    ]
                ]);

        $this->assertDatabaseHas('cooperative_plant_configs', [
            'cooperative_id' => $cooperative->id,
            'plant_id' => $plant->id,
            'default' => false,
            'active' => true
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_config()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cooperative-plant-configs', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cooperative_id', 'plant_id']);
    }

    /** @test */
    public function it_can_update_a_cooperative_plant_config()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $config = CooperativePlantConfig::factory()->create();
        $updateData = [
            'default' => true,
            'active' => false
        ];

        $response = $this->putJson("/api/v1/cooperative-plant-configs/{$config->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $config->id,
                        'default' => true,
                        'active' => false
                    ]
                ]);

        $this->assertDatabaseHas('cooperative_plant_configs', [
            'id' => $config->id,
            'default' => true,
            'active' => false
        ]);
    }

    /** @test */
    public function it_can_delete_a_cooperative_plant_config()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $config = CooperativePlantConfig::factory()->create();

        $response = $this->deleteJson("/api/v1/cooperative-plant-configs/{$config->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Configuración de planta cooperativa eliminada correctamente']);

        $this->assertDatabaseMissing('cooperative_plant_configs', ['id' => $config->id]);
    }

    /** @test */
    public function it_can_filter_configs_by_cooperative()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $cooperative1 = EnergyCooperative::factory()->create();
        $cooperative2 = EnergyCooperative::factory()->create();
        
        CooperativePlantConfig::factory()->create(['cooperative_id' => $cooperative1->id]);
        CooperativePlantConfig::factory()->create(['cooperative_id' => $cooperative2->id]);
        CooperativePlantConfig::factory()->create(['cooperative_id' => $cooperative1->id]);

        $response = $this->getJson("/api/v1/cooperative-plant-configs/by-cooperative/{$cooperative1->id}");

        $response->assertStatus(200);
        
        $cooperativeConfigs = $response->json('data');
        $this->assertEquals(2, count($cooperativeConfigs));
        
        foreach ($cooperativeConfigs as $config) {
            $this->assertEquals($cooperative1->id, $config['cooperative_id']);
        }
    }

    /** @test */
    public function it_can_get_default_config_for_cooperative()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $cooperative = EnergyCooperative::factory()->create();
        CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'default' => true
        ]);

        $response = $this->getJson("/api/v1/cooperative-plant-configs/get-default/{$cooperative->id}");

        $response->assertStatus(200);
        
        $defaultConfig = $response->json('data');
        $this->assertTrue($defaultConfig['default']);
        $this->assertEquals($cooperative->id, $defaultConfig['cooperative_id']);
    }

    /** @test */
    public function it_can_set_config_as_default()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $cooperative = EnergyCooperative::factory()->create();
        
        // Crear configuración existente por defecto
        $existingDefault = CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'default' => true
        ]);
        
        // Crear nueva configuración
        $newConfig = CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'default' => false
        ]);

        $response = $this->postJson("/api/v1/cooperative-plant-configs/{$newConfig->id}/set-as-default");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $newConfig->id,
                        'default' => true
                    ]
                ]);

        // Verificar que la configuración anterior ya no es por defecto
        $this->assertDatabaseHas('cooperative_plant_configs', [
            'id' => $existingDefault->id,
            'default' => false
        ]);
        
        $this->assertDatabaseHas('cooperative_plant_configs', [
            'id' => $newConfig->id,
            'default' => true
        ]);
    }

    /** @test */
    public function it_can_remove_default_status()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $config = CooperativePlantConfig::factory()->create(['default' => true]);

        $response = $this->postJson("/api/v1/cooperative-plant-configs/{$config->id}/remove-default");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $config->id,
                        'default' => false
                    ]
                ]);

        $this->assertDatabaseHas('cooperative_plant_configs', [
            'id' => $config->id,
            'default' => false
        ]);
    }

    /** @test */
    public function it_can_toggle_config_active_status()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $config = CooperativePlantConfig::factory()->create(['active' => true]);

        $response = $this->postJson("/api/v1/cooperative-plant-configs/{$config->id}/toggle-active");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $config->id,
                        'active' => false
                    ]
                ]);

        $this->assertDatabaseHas('cooperative_plant_configs', [
            'id' => $config->id,
            'active' => false
        ]);
    }

    /** @test */
    public function it_can_get_config_statistics()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        CooperativePlantConfig::factory()->count(5)->create(['active' => true]);
        CooperativePlantConfig::factory()->count(3)->create(['active' => false]);
        CooperativePlantConfig::factory()->count(2)->create(['default' => true]);

        $response = $this->getJson('/api/v1/cooperative-plant-configs/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_configs',
                        'active_configs',
                        'inactive_configs',
                        'default_configs',
                        'configs_by_cooperative',
                        'configs_by_plant'
                    ]
                ]);

        $stats = $response->json('data');
        $this->assertEquals(8, $stats['total_configs']);
        $this->assertEquals(5, $stats['active_configs']);
        $this->assertEquals(3, $stats['inactive_configs']);
        $this->assertEquals(2, $stats['default_configs']);
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        $config = CooperativePlantConfig::factory()->create();

        // Intentar acceder sin autenticación
        $response = $this->getJson('/api/v1/cooperative-plant-configs');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/cooperative-plant-configs', []);
        $response->assertStatus(401);

        $response = $this->putJson("/api/v1/cooperative-plant-configs/{$config->id}", []);
        $response->assertStatus(401);

        $response = $this->deleteJson("/api/v1/cooperative-plant-configs/{$config->id}");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_paginate_configs()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        CooperativePlantConfig::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/cooperative-plant-configs?per_page=10');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(10, count($data['data']));
        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertEquals(25, $data['meta']['total']);
    }

    /** @test */
    public function it_can_sort_configs()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $cooperative1 = EnergyCooperative::factory()->create();
        $cooperative2 = EnergyCooperative::factory()->create();
        $cooperative3 = EnergyCooperative::factory()->create();

        CooperativePlantConfig::factory()->create(['cooperative_id' => $cooperative3->id]);
        CooperativePlantConfig::factory()->create(['cooperative_id' => $cooperative1->id]);
        CooperativePlantConfig::factory()->create(['cooperative_id' => $cooperative2->id]);

        // Ordenar por cooperative_id ascendente
        $response = $this->getJson('/api/v1/cooperative-plant-configs?sort=cooperative_id&order=asc');
        $response->assertStatus(200);
        
        $configs = $response->json('data');
        $this->assertEquals($cooperative1->id, $configs[0]['cooperative_id']);
        $this->assertEquals($cooperative2->id, $configs[1]['cooperative_id']);
        $this->assertEquals($cooperative3->id, $configs[2]['cooperative_id']);
    }

    /** @test */
    public function it_handles_config_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/cooperative-plant-configs/99999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Configuración de planta cooperativa no encontrada']);
    }

    /** @test */
    public function it_handles_update_validation_errors()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $config = CooperativePlantConfig::factory()->create();

        $response = $this->putJson("/api/v1/cooperative-plant-configs/{$config->id}", [
            'cooperative_id' => 'invalid_id'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cooperative_id']);
    }

    /** @test */
    public function it_can_create_config_with_organization()
    {
        $user = User::factory()->create();
        $cooperative = EnergyCooperative::factory()->create();
        $plant = Plant::factory()->create();
        $organization = Organization::factory()->create();
        Sanctum::actingAs($user);

        $configData = [
            'cooperative_id' => $cooperative->id,
            'plant_id' => $plant->id,
            'default' => false,
            'active' => true,
            'organization_id' => $organization->id
        ];

        $response = $this->postJson('/api/v1/cooperative-plant-configs', $configData);

        $response->assertStatus(201)
                ->assertJson([
                    'data' => [
                        'cooperative_id' => $cooperative->id,
                        'plant_id' => $plant->id,
                        'organization_id' => $organization->id
                    ]
                ]);

        $this->assertDatabaseHas('cooperative_plant_configs', [
            'cooperative_id' => $cooperative->id,
            'plant_id' => $plant->id,
            'organization_id' => $organization->id
        ]);
    }

    /** @test */
    public function it_ensures_unique_default_per_cooperative()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $cooperative = EnergyCooperative::factory()->create();
        
        // Crear primera configuración por defecto
        $config1 = CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'default' => true
        ]);
        
        // Intentar crear segunda configuración por defecto
        $configData = [
            'cooperative_id' => $cooperative->id,
            'plant_id' => Plant::factory()->create()->id,
            'default' => true
        ];

        $response = $this->postJson('/api/v1/cooperative-plant-configs', $configData);

        $response->assertStatus(201);
        
        // Verificar que la primera ya no es por defecto
        $this->assertDatabaseHas('cooperative_plant_configs', [
            'id' => $config1->id,
            'default' => false
        ]);
        
        // Verificar que la nueva es por defecto
        $this->assertDatabaseHas('cooperative_plant_configs', [
            'id' => $response->json('data.id'),
            'default' => true
        ]);
    }

    /** @test */
    public function it_can_filter_configs_by_plant()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plant1 = Plant::factory()->create();
        $plant2 = Plant::factory()->create();
        
        CooperativePlantConfig::factory()->create(['plant_id' => $plant1->id]);
        CooperativePlantConfig::factory()->create(['plant_id' => $plant2->id]);
        CooperativePlantConfig::factory()->create(['plant_id' => $plant1->id]);

        $response = $this->getJson("/api/v1/cooperative-plant-configs?plant_id={$plant1->id}");

        $response->assertStatus(200);
        
        $plantConfigs = $response->json('data');
        $this->assertEquals(2, count($plantConfigs));
        
        foreach ($plantConfigs as $config) {
            $this->assertEquals($plant1->id, $config['plant_id']);
        }
    }

    /** @test */
    public function it_can_filter_configs_by_active_status()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        CooperativePlantConfig::factory()->create(['active' => true]);
        CooperativePlantConfig::factory()->create(['active' => false]);
        CooperativePlantConfig::factory()->create(['active' => true]);

        $response = $this->getJson('/api/v1/cooperative-plant-configs?active=1');

        $response->assertStatus(200);
        
        $activeConfigs = $response->json('data');
        $this->assertEquals(2, count($activeConfigs));
        
        foreach ($activeConfigs as $config) {
            $this->assertTrue($config['active']);
        }
    }
}
