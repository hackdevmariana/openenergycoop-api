<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Plant;
use App\Models\User;
use App\Models\PlantGroup;
use App\Models\CooperativePlantConfig;
use App\Models\EnergyCooperative;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class PlantApiTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_list_all_plants()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Plant::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/plants');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'co2_equivalent_per_unit_kg',
                            'image',
                            'description',
                            'unit_label',
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
    public function it_can_show_a_specific_plant()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plant = Plant::factory()->create();

        $response = $this->getJson("/api/v1/plants/{$plant->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $plant->id,
                        'name' => $plant->name,
                        'co2_equivalent_per_unit_kg' => $plant->co2_equivalent_per_unit_kg,
                        'unit_label' => $plant->unit_label,
                        'is_active' => $plant->is_active
                    ]
                ]);
    }

    /** @test */
    public function it_can_create_a_new_plant()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plantData = [
            'name' => 'Nuevo Pino',
            'co2_equivalent_per_unit_kg' => 30.5,
            'image' => 'plants/nuevo-pino.jpg',
            'description' => 'Un nuevo tipo de pino',
            'unit_label' => 'árbol',
            'is_active' => true
        ];

        $response = $this->postJson('/api/v1/plants', $plantData);

        $response->assertStatus(201)
                ->assertJson([
                    'data' => [
                        'name' => 'Nuevo Pino',
                        'co2_equivalent_per_unit_kg' => 30.5,
                        'unit_label' => 'árbol',
                        'is_active' => true
                    ]
                ]);

        $this->assertDatabaseHas('plants', [
            'name' => 'Nuevo Pino',
            'co2_equivalent_per_unit_kg' => 30.5
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_plant()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/plants', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'co2_equivalent_per_unit_kg', 'unit_label']);
    }

    /** @test */
    public function it_can_update_a_plant()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plant = Plant::factory()->create();
        $updateData = [
            'name' => 'Pino Actualizado',
            'co2_equivalent_per_unit_kg' => 35.0,
            'description' => 'Descripción actualizada'
        ];

        $response = $this->putJson("/api/v1/plants/{$plant->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $plant->id,
                        'name' => 'Pino Actualizado',
                        'co2_equivalent_per_unit_kg' => 35.0,
                        'description' => 'Descripción actualizada'
                    ]
                ]);

        $this->assertDatabaseHas('plants', [
            'id' => $plant->id,
            'name' => 'Pino Actualizado',
            'co2_equivalent_per_unit_kg' => 35.0
        ]);
    }

    /** @test */
    public function it_can_delete_a_plant()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plant = Plant::factory()->create();

        $response = $this->deleteJson("/api/v1/plants/{$plant->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Planta eliminada correctamente']);

        $this->assertSoftDeleted('plants', ['id' => $plant->id]);
    }

    /** @test */
    public function it_can_list_active_plants()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Plant::factory()->create(['is_active' => true]);
        Plant::factory()->create(['is_active' => false]);
        Plant::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/plants/active');

        $response->assertStatus(200);
        
        $activePlants = $response->json('data');
        $this->assertEquals(2, count($activePlants));
        
        foreach ($activePlants as $plant) {
            $this->assertTrue($plant['is_active']);
        }
    }

    /** @test */
    public function it_can_filter_plants_by_unit_label()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Plant::factory()->create(['unit_label' => 'árbol']);
        Plant::factory()->create(['unit_label' => 'planta']);
        Plant::factory()->create(['unit_label' => 'árbol']);

        $response = $this->getJson('/api/v1/plants/by-unit-label/árbol');

        $response->assertStatus(200);
        
        $treePlants = $response->json('data');
        $this->assertEquals(2, count($treePlants));
        
        foreach ($treePlants as $plant) {
            $this->assertEquals('árbol', $plant['unit_label']);
        }
    }

    /** @test */
    public function it_can_filter_plants_by_co2_range()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Plant::factory()->create(['co2_equivalent_per_unit_kg' => 10.0]);
        Plant::factory()->create(['co2_equivalent_per_unit_kg' => 50.0]);
        Plant::factory()->create(['co2_equivalent_per_unit_kg' => 100.0]);

        $response = $this->getJson('/api/v1/plants/by-co2-range?min=20&max=80');

        $response->assertStatus(200);
        
        $mediumCo2Plants = $response->json('data');
        $this->assertEquals(1, count($mediumCo2Plants));
        $this->assertEquals(50.0, $mediumCo2Plants[0]['co2_equivalent_per_unit_kg']);
    }

    /** @test */
    public function it_can_toggle_plant_active_status()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plant = Plant::factory()->create(['is_active' => true]);

        $response = $this->postJson("/api/v1/plants/{$plant->id}/toggle-active");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $plant->id,
                        'is_active' => false
                    ]
                ]);

        $this->assertDatabaseHas('plants', [
            'id' => $plant->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function it_can_get_plant_statistics()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Plant::factory()->count(5)->create(['is_active' => true]);
        Plant::factory()->count(3)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/plants/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_plants',
                        'active_plants',
                        'inactive_plants',
                        'total_co2_potential',
                        'average_co2_per_plant',
                        'plants_by_unit_label'
                    ]
                ]);

        $stats = $response->json('data');
        $this->assertEquals(8, $stats['total_plants']);
        $this->assertEquals(5, $stats['active_plants']);
        $this->assertEquals(3, $stats['inactive_plants']);
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        $plant = Plant::factory()->create();

        // Intentar acceder sin autenticación
        $response = $this->getJson('/api/v1/plants');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/plants', []);
        $response->assertStatus(401);

        $response = $this->putJson("/api/v1/plants/{$plant->id}", []);
        $response->assertStatus(401);

        $response = $this->deleteJson("/api/v1/plants/{$plant->id}");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_paginate_plants()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Plant::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/plants?per_page=10');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(10, count($data['data']));
        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertEquals(25, $data['meta']['total']);
    }

    /** @test */
    public function it_can_search_plants()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Plant::factory()->create(['name' => 'Pino', 'description' => 'Árbol de hoja perenne']);
        Plant::factory()->create(['name' => 'Vid', 'description' => 'Planta trepadora']);
        Plant::factory()->create(['name' => 'Plátano', 'description' => 'Árbol frutal']);

        $response = $this->getJson('/api/v1/plants?search=árbol');

        $response->assertStatus(200);
        
        $searchResults = $response->json('data');
        $this->assertEquals(2, count($searchResults));
        
        $plantNames = collect($searchResults)->pluck('name')->toArray();
        $this->assertContains('Pino', $plantNames);
        $this->assertContains('Plátano', $plantNames);
    }

    /** @test */
    public function it_can_sort_plants()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Plant::factory()->create(['name' => 'Zapote', 'co2_equivalent_per_unit_kg' => 10.0]);
        Plant::factory()->create(['name' => 'Árbol', 'co2_equivalent_per_unit_kg' => 50.0]);
        Plant::factory()->create(['name' => 'Mango', 'co2_equivalent_per_unit_kg' => 30.0]);

        // Ordenar por nombre ascendente
        $response = $this->getJson('/api/v1/plants?sort=name&order=asc');
        $response->assertStatus(200);
        
        $plants = $response->json('data');
        $this->assertEquals('Árbol', $plants[0]['name']);
        $this->assertEquals('Mango', $plants[1]['name']);
        $this->assertEquals('Zapote', $plants[2]['name']);

        // Ordenar por CO2 descendente
        $response = $this->getJson('/api/v1/plants?sort=co2_equivalent_per_unit_kg&order=desc');
        $response->assertStatus(200);
        
        $plants = $response->json('data');
        $this->assertEquals(50.0, $plants[0]['co2_equivalent_per_unit_kg']);
        $this->assertEquals(30.0, $plants[1]['co2_equivalent_per_unit_kg']);
        $this->assertEquals(10.0, $plants[2]['co2_equivalent_per_unit_kg']);
    }

    /** @test */
    public function it_handles_plant_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/plants/99999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Planta no encontrada']);
    }

    /** @test */
    public function it_handles_update_validation_errors()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plant = Plant::factory()->create();

        $response = $this->putJson("/api/v1/plants/{$plant->id}", [
            'co2_equivalent_per_unit_kg' => 'invalid_number'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['co2_equivalent_per_unit_kg']);
    }
}
