<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\PlantGroup;
use App\Models\Plant;
use App\Models\User;
use App\Models\EnergyCooperative;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class PlantGroupApiTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_list_all_plant_groups()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        PlantGroup::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/plant-groups');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'name',
                            'plant_id',
                            'number_of_plants',
                            'co2_avoided_total',
                            'custom_label',
                            'is_active',
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
    public function it_can_show_a_specific_plant_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create();

        $response = $this->getJson("/api/v1/plant-groups/{$plantGroup->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $plantGroup->id,
                        'name' => $plantGroup->name,
                        'number_of_plants' => $plantGroup->number_of_plants,
                        'co2_avoided_total' => $plantGroup->co2_avoided_total,
                        'is_active' => $plantGroup->is_active
                    ]
                ]);
    }

    /** @test */
    public function it_can_create_a_new_plant_group()
    {
        $user = User::factory()->create();
        $plant = Plant::factory()->create();
        Sanctum::actingAs($user);

        $plantGroupData = [
            'user_id' => $user->id,
            'name' => 'Mi Viña Solar',
            'plant_id' => $plant->id,
            'number_of_plants' => 50,
            'co2_avoided_total' => 1250.0,
            'custom_label' => 'Viña cooperativa',
            'is_active' => true
        ];

        $response = $this->postJson('/api/v1/plant-groups', $plantGroupData);

        $response->assertStatus(201)
                ->assertJson([
                    'data' => [
                        'name' => 'Mi Viña Solar',
                        'number_of_plants' => 50,
                        'co2_avoided_total' => 1250.0,
                        'custom_label' => 'Viña cooperativa',
                        'is_active' => true
                    ]
                ]);

        $this->assertDatabaseHas('plant_groups', [
            'name' => 'Mi Viña Solar',
            'user_id' => $user->id,
            'plant_id' => $plant->id
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_plant_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/plant-groups', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'plant_id', 'number_of_plants']);
    }

    /** @test */
    public function it_can_update_a_plant_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create();
        $updateData = [
            'name' => 'Viña Actualizada',
            'number_of_plants' => 75,
            'custom_label' => 'Nueva etiqueta'
        ];

        $response = $this->putJson("/api/v1/plant-groups/{$plantGroup->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $plantGroup->id,
                        'name' => 'Viña Actualizada',
                        'number_of_plants' => 75,
                        'custom_label' => 'Nueva etiqueta'
                    ]
                ]);

        $this->assertDatabaseHas('plant_groups', [
            'id' => $plantGroup->id,
            'name' => 'Viña Actualizada',
            'number_of_plants' => 75
        ]);
    }

    /** @test */
    public function it_can_delete_a_plant_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create();

        $response = $this->deleteJson("/api/v1/plant-groups/{$plantGroup->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Grupo de plantas eliminado correctamente']);

        $this->assertSoftDeleted('plant_groups', ['id' => $plantGroup->id]);
    }

    /** @test */
    public function it_can_filter_plant_groups_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user1);

        PlantGroup::factory()->create(['user_id' => $user1->id]);
        PlantGroup::factory()->create(['user_id' => $user2->id]);
        PlantGroup::factory()->create(['user_id' => $user1->id]);

        $response = $this->getJson("/api/v1/plant-groups/by-user/{$user1->id}");

        $response->assertStatus(200);
        
        $userGroups = $response->json('data');
        $this->assertEquals(2, count($userGroups));
        
        foreach ($userGroups as $group) {
            $this->assertEquals($user1->id, $group['user_id']);
        }
    }

    /** @test */
    public function it_can_list_collective_plant_groups()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        PlantGroup::factory()->create(['user_id' => null]); // Colectivo
        PlantGroup::factory()->create(['user_id' => $user->id]); // Individual
        PlantGroup::factory()->create(['user_id' => null]); // Colectivo

        $response = $this->getJson('/api/v1/plant-groups/collective');

        $response->assertStatus(200);
        
        $collectiveGroups = $response->json('data');
        $this->assertEquals(2, count($collectiveGroups));
        
        foreach ($collectiveGroups as $group) {
            $this->assertNull($group['user_id']);
        }
    }

    /** @test */
    public function it_can_add_plants_to_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create([
            'number_of_plants' => 10,
            'co2_avoided_total' => 250.0
        ]);

        $response = $this->postJson("/api/v1/plant-groups/{$plantGroup->id}/add-plants", [
            'number_of_plants' => 5
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $plantGroup->id,
                        'number_of_plants' => 15,
                        'co2_avoided_total' => 375.0
                    ]
                ]);

        $this->assertDatabaseHas('plant_groups', [
            'id' => $plantGroup->id,
            'number_of_plants' => 15,
            'co2_avoided_total' => 375.0
        ]);
    }

    /** @test */
    public function it_can_remove_plants_from_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create([
            'number_of_plants' => 20,
            'co2_avoided_total' => 500.0
        ]);

        $response = $this->postJson("/api/v1/plant-groups/{$plantGroup->id}/remove-plants", [
            'number_of_plants' => 8
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $plantGroup->id,
                        'number_of_plants' => 12,
                        'co2_avoided_total' => 300.0
                    ]
                ]);

        $this->assertDatabaseHas('plant_groups', [
            'id' => $plantGroup->id,
            'number_of_plants' => 12,
            'co2_avoided_total' => 300.0
        ]);
    }

    /** @test */
    public function it_can_toggle_plant_group_active_status()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create(['is_active' => true]);

        $response = $this->postJson("/api/v1/plant-groups/{$plantGroup->id}/toggle-active");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $plantGroup->id,
                        'is_active' => false
                    ]
                ]);

        $this->assertDatabaseHas('plant_groups', [
            'id' => $plantGroup->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function it_can_get_plant_group_statistics()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        PlantGroup::factory()->count(5)->create(['is_active' => true]);
        PlantGroup::factory()->count(3)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/plant-groups/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_groups',
                        'active_groups',
                        'inactive_groups',
                        'total_plants',
                        'total_co2_avoided',
                        'average_plants_per_group',
                        'groups_by_type'
                    ]
                ]);

        $stats = $response->json('data');
        $this->assertEquals(8, $stats['total_groups']);
        $this->assertEquals(5, $stats['active_groups']);
        $this->assertEquals(3, $stats['inactive_groups']);
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        $plantGroup = PlantGroup::factory()->create();

        // Intentar acceder sin autenticación
        $response = $this->getJson('/api/v1/plant-groups');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/plant-groups', []);
        $response->assertStatus(401);

        $response = $this->putJson("/api/v1/plant-groups/{$plantGroup->id}", []);
        $response->assertStatus(401);

        $response = $this->deleteJson("/api/v1/plant-groups/{$plantGroup->id}");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_paginate_plant_groups()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        PlantGroup::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/plant-groups?per_page=10');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(10, count($data['data']));
        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertEquals(25, $data['meta']['total']);
    }

    /** @test */
    public function it_can_search_plant_groups()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        PlantGroup::factory()->create(['name' => 'Mi Viña Solar', 'custom_label' => 'Viña cooperativa']);
        PlantGroup::factory()->create(['name' => 'Pinar Comunitario', 'custom_label' => 'Bosque compartido']);
        PlantGroup::factory()->create(['name' => 'Huerta Individual', 'custom_label' => 'Mi huerta']);

        $response = $this->getJson('/api/v1/plant-groups?search=cooperativa');

        $response->assertStatus(200);
        
        $searchResults = $response->json('data');
        $this->assertEquals(1, count($searchResults));
        $this->assertEquals('Mi Viña Solar', $searchResults[0]['name']);
    }

    /** @test */
    public function it_can_sort_plant_groups()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        PlantGroup::factory()->create(['name' => 'Zapote', 'number_of_plants' => 10]);
        PlantGroup::factory()->create(['name' => 'Árbol', 'number_of_plants' => 50]);
        PlantGroup::factory()->create(['name' => 'Mango', 'number_of_plants' => 30]);

        // Ordenar por nombre ascendente
        $response = $this->getJson('/api/v1/plant-groups?sort=name&order=asc');
        $response->assertStatus(200);
        
        $groups = $response->json('data');
        $this->assertEquals('Árbol', $groups[0]['name']);
        $this->assertEquals('Mango', $groups[1]['name']);
        $this->assertEquals('Zapote', $groups[2]['name']);

        // Ordenar por número de plantas descendente
        $response = $this->getJson('/api/v1/plant-groups?sort=number_of_plants&order=desc');
        $response->assertStatus(200);
        
        $groups = $response->json('data');
        $this->assertEquals(50, $groups[0]['number_of_plants']);
        $this->assertEquals(30, $groups[1]['number_of_plants']);
        $this->assertEquals(10, $groups[2]['number_of_plants']);
    }

    /** @test */
    public function it_handles_plant_group_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/plant-groups/99999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Grupo de plantas no encontrado']);
    }

    /** @test */
    public function it_handles_update_validation_errors()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create();

        $response = $this->putJson("/api/v1/plant-groups/{$plantGroup->id}", [
            'number_of_plants' => 'invalid_number'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['number_of_plants']);
    }

    /** @test */
    public function it_validates_add_plants_request()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create();

        $response = $this->postJson("/api/v1/plant-groups/{$plantGroup->id}/add-plants", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['number_of_plants']);
    }

    /** @test */
    public function it_validates_remove_plants_request()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create();

        $response = $this->postJson("/api/v1/plant-groups/{$plantGroup->id}/remove-plants", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['number_of_plants']);
    }

    /** @test */
    public function it_cannot_remove_more_plants_than_exist()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantGroup = PlantGroup::factory()->create(['number_of_plants' => 5]);

        $response = $this->postJson("/api/v1/plant-groups/{$plantGroup->id}/remove-plants", [
            'number_of_plants' => 10
        ]);

        $response->assertStatus(422)
                ->assertJson(['message' => 'No se pueden quitar más plantas de las que existen en el grupo']);
    }

    /** @test */
    public function it_can_create_collective_plant_group()
    {
        $user = User::factory()->create();
        $plant = Plant::factory()->create();
        Sanctum::actingAs($user);

        $plantGroupData = [
            'user_id' => null, // Colectivo
            'name' => 'Pinar Comunitario',
            'plant_id' => $plant->id,
            'number_of_plants' => 100,
            'co2_avoided_total' => 2500.0,
            'custom_label' => 'Bosque compartido',
            'is_active' => true
        ];

        $response = $this->postJson('/api/v1/plant-groups', $plantGroupData);

        $response->assertStatus(201)
                ->assertJson([
                    'data' => [
                        'name' => 'Pinar Comunitario',
                        'user_id' => null,
                        'number_of_plants' => 100,
                        'custom_label' => 'Bosque compartido'
                    ]
                ]);

        $this->assertDatabaseHas('plant_groups', [
            'name' => 'Pinar Comunitario',
            'user_id' => null
        ]);
    }
}
