<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\EnergyChallenge;
use App\Models\UserChallengeProgress;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class EnergyChallengeApiTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario de prueba
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function it_can_list_energy_challenges()
    {
        // Crear algunos desafíos
        EnergyChallenge::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/energy-challenges');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'type',
                                'goal_kwh',
                                'starts_at',
                                'ends_at',
                                'reward_type',
                                'is_active',
                                'created_at',
                                'updated_at'
                            ]
                        ]
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_can_list_active_energy_challenges()
    {
        // Crear desafíos activos e inactivos
        EnergyChallenge::factory()->active()->count(2)->create();
        EnergyChallenge::factory()->count(3)->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/energy-challenges/active');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'is_active'
                            ]
                        ]
                    ],
                    'message'
                ]);

        // Verificar que solo devuelve desafíos activos
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
        foreach ($data as $challenge) {
            $this->assertTrue($challenge['is_active']);
        }
    }

    /** @test */
    public function it_can_list_upcoming_energy_challenges()
    {
        // Crear desafíos próximos
        EnergyChallenge::factory()->upcoming()->count(3)->create();

        $response = $this->getJson('/api/v1/energy-challenges/upcoming');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'starts_at'
                            ]
                        ]
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_can_show_energy_challenge()
    {
        $challenge = EnergyChallenge::factory()->create();

        $response = $this->getJson("/api/v1/energy-challenges/{$challenge->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'type',
                        'goal_kwh',
                        'starts_at',
                        'ends_at',
                        'reward_type',
                        'is_active'
                    ],
                    'message'
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $challenge->id,
                        'title' => $challenge->title
                    ]
                ]);
    }

    /** @test */
    public function it_can_create_energy_challenge_when_authenticated()
    {
        Sanctum::actingAs($this->adminUser);

        $challengeData = [
            'title' => 'Desafío de Ahorro Energético',
            'description' => 'Ahorra energía durante 30 días',
            'type' => 'individual',
            'goal_kwh' => 100.5,
            'starts_at' => now()->addDays(1)->toISOString(),
            'ends_at' => now()->addDays(31)->toISOString(),
            'reward_type' => 'badge',
            'reward_details' => ['badge_name' => 'Ahorrador'],
            'is_active' => true
        ];

        $response = $this->postJson('/api/v1/energy-challenges', $challengeData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'type',
                        'goal_kwh',
                        'starts_at',
                        'ends_at',
                        'reward_type',
                        'is_active'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('energy_challenges', [
            'title' => 'Desafío de Ahorro Energético',
            'type' => 'individual',
            'goal_kwh' => 100.5
        ]);
    }

    /** @test */
    public function it_cannot_create_energy_challenge_when_not_authenticated()
    {
        $challengeData = [
            'title' => 'Desafío de Ahorro Energético',
            'description' => 'Ahorra energía durante 30 días',
            'type' => 'individual',
            'goal_kwh' => 100.5,
            'starts_at' => now()->addDays(1)->toISOString(),
            'ends_at' => now()->addDays(31)->toISOString(),
            'reward_type' => 'badge'
        ];

        $response = $this->postJson('/api/v1/energy-challenges', $challengeData);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_challenge()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/energy_challenges', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'title',
                    'description',
                    'type',
                    'goal_kwh',
                    'starts_at',
                    'ends_at',
                    'reward_type'
                ]);
    }

    /** @test */
    public function it_can_update_energy_challenge_when_authenticated()
    {
        Sanctum::actingAs($this->adminUser);
        
        $challenge = EnergyChallenge::factory()->create();
        
        $updateData = [
            'title' => 'Título Actualizado',
            'goal_kwh' => 150.0
        ];

        $response = $this->putJson("/api/v1/energy-challenges/{$challenge->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'goal_kwh'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('energy_challenges', [
            'id' => $challenge->id,
            'title' => 'Título Actualizado',
            'goal_kwh' => 150.0
        ]);
    }

    /** @test */
    public function it_can_delete_energy_challenge_when_authenticated()
    {
        Sanctum::actingAs($this->adminUser);
        
        $challenge = EnergyChallenge::factory()->create();

        $response = $this->deleteJson("/api/v1/energy-challenges/{$challenge->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Desafío eliminado exitosamente'
                ]);

        $this->assertSoftDeleted('energy_challenges', [
            'id' => $challenge->id
        ]);
    }

    /** @test */
    public function it_can_get_challenge_statistics()
    {
        $challenge = EnergyChallenge::factory()->create();
        
        // Crear algunos progresos de usuario
        UserChallengeProgress::factory()->count(5)->create([
            'challenge_id' => $challenge->id
        ]);

        $response = $this->getJson("/api/v1/energy-challenges/{$challenge->id}/statistics");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_participants',
                        'average_progress',
                        'progress_percentage',
                        'days_remaining',
                        'top_participants'
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_can_filter_challenges_by_type()
    {
        EnergyChallenge::factory()->count(3)->create(['type' => 'individual']);
        EnergyChallenge::factory()->count(2)->create(['type' => 'colectivo']);

        $response = $this->getJson('/api/v1/energy-challenges?type=individual');

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(3, $data);
        foreach ($data as $challenge) {
            $this->assertEquals('individual', $challenge['type']);
        }
    }

    /** @test */
    public function it_can_filter_challenges_by_reward_type()
    {
        EnergyChallenge::factory()->count(2)->create(['reward_type' => 'badge']);
        EnergyChallenge::factory()->count(3)->create(['reward_type' => 'energy_donation']);

        $response = $this->getJson('/api/v1/energy-challenges?reward_type=badge');

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
        foreach ($data as $challenge) {
            $this->assertEquals('badge', $challenge['reward_type']);
        }
    }

    /** @test */
    public function it_can_search_challenges()
    {
        EnergyChallenge::factory()->create(['title' => 'Desafío Solar']);
        EnergyChallenge::factory()->create(['title' => 'Desafío Eólico']);
        EnergyChallenge::factory()->create(['title' => 'Otro Desafío']);

        $response = $this->getJson('/api/v1/energy-challenges?search=solar');

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('solar', strtolower($data[0]['title']));
    }

    /** @test */
    public function it_can_sort_challenges()
    {
        EnergyChallenge::factory()->create(['title' => 'A Desafío']);
        EnergyChallenge::factory()->create(['title' => 'B Desafío']);
        EnergyChallenge::factory()->create(['title' => 'C Desafío']);

        $response = $this->getJson('/api/v1/energy-challenges?sort_by=title&sort_order=asc');

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertEquals('A Desafío', $data[0]['title']);
        $this->assertEquals('C Desafío', $data[2]['title']);
    }

    /** @test */
    public function it_can_paginate_challenges()
    {
        EnergyChallenge::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/energy-challenges?per_page=10');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals(10, $data['per_page']);
        $this->assertEquals(25, $data['total']);
        $this->assertCount(10, $data['data']);
    }
}
