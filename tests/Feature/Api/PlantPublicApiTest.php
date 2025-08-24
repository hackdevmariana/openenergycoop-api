<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PlantPublicApiTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_list_all_plants_publicly()
    {
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
    public function it_can_show_a_specific_plant_publicly()
    {
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
    public function it_can_list_active_plants_publicly()
    {
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
    public function it_can_filter_plants_by_unit_label_publicly()
    {
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
    public function it_can_filter_plants_by_co2_range_publicly()
    {
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
    public function it_can_get_plant_statistics_publicly()
    {
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
    public function it_can_paginate_plants_publicly()
    {
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
    public function it_can_search_plants_publicly()
    {
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
    public function it_can_sort_plants_publicly()
    {
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
    public function it_handles_plant_not_found_publicly()
    {
        $response = $this->getJson('/api/v1/plants/99999');

        $response->assertStatus(404)
                ->assertJson(['message' => 'Planta no encontrada']);
    }

    /** @test */
    public function it_handles_invalid_co2_range_parameters()
    {
        $response = $this->getJson('/api/v1/plants/by-co2-range?min=invalid&max=80');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['min']);
    }

    /** @test */
    public function it_handles_missing_co2_range_parameters()
    {
        $response = $this->getJson('/api/v1/plants/by-co2-range');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['min', 'max']);
    }

    /** @test */
    public function it_handles_invalid_unit_label_parameter()
    {
        $response = $this->getJson('/api/v1/plants/by-unit-label/');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_empty_results_for_non_existent_unit_label()
    {
        Plant::factory()->create(['unit_label' => 'árbol']);

        $response = $this->getJson('/api/v1/plants/by-unit-label/planta-inexistente');

        $response->assertStatus(200);
        
        $plants = $response->json('data');
        $this->assertEquals(0, count($plants));
    }

    /** @test */
    public function it_returns_empty_results_for_co2_range_with_no_matches()
    {
        Plant::factory()->create(['co2_equivalent_per_unit_kg' => 10.0]);
        Plant::factory()->create(['co2_equivalent_per_unit_kg' => 100.0]);

        $response = $this->getJson('/api/v1/plants/by-co2-range?min=50&max=80');

        $response->assertStatus(200);
        
        $plants = $response->json('data');
        $this->assertEquals(0, count($plants));
    }

    /** @test */
    public function it_can_filter_plants_by_active_status_publicly()
    {
        Plant::factory()->create(['is_active' => true]);
        Plant::factory()->create(['is_active' => false]);
        Plant::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/plants?is_active=1');

        $response->assertStatus(200);
        
        $activePlants = $response->json('data');
        $this->assertEquals(2, count($activePlants));
        
        foreach ($activePlants as $plant) {
            $this->assertTrue($plant['is_active']);
        }
    }

    /** @test */
    public function it_can_filter_plants_by_unit_label_query_parameter()
    {
        Plant::factory()->create(['unit_label' => 'árbol']);
        Plant::factory()->create(['unit_label' => 'planta']);
        Plant::factory()->create(['unit_label' => 'árbol']);

        $response = $this->getJson('/api/v1/plants?unit_label=árbol');

        $response->assertStatus(200);
        
        $treePlants = $response->json('data');
        $this->assertEquals(2, count($treePlants));
        
        foreach ($treePlants as $plant) {
            $this->assertEquals('árbol', $plant['unit_label']);
        }
    }

    /** @test */
    public function it_returns_correct_meta_information_for_pagination()
    {
        Plant::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/plants?per_page=5&page=2');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(5, count($data['data']));
        $this->assertEquals(15, $data['meta']['total']);
        $this->assertEquals(2, $data['meta']['current_page']);
        $this->assertEquals(3, $data['meta']['last_page']);
    }

    /** @test */
    public function it_handles_search_with_special_characters()
    {
        Plant::factory()->create(['name' => 'Pino-Piñón', 'description' => 'Árbol con piñas']);
        Plant::factory()->create(['name' => 'Vid Común', 'description' => 'Planta trepadora']);

        $response = $this->getJson('/api/v1/plants?search=piña');

        $response->assertStatus(200);
        
        $searchResults = $response->json('data');
        $this->assertEquals(1, count($searchResults));
        $this->assertEquals('Pino-Piñón', $searchResults[0]['name']);
    }

    /** @test */
    public function it_handles_empty_search_results()
    {
        Plant::factory()->create(['name' => 'Pino', 'description' => 'Árbol de hoja perenne']);

        $response = $this->getJson('/api/v1/plants?search=planta-inexistente');

        $response->assertStatus(200);
        
        $searchResults = $response->json('data');
        $this->assertEquals(0, count($searchResults));
    }
}
