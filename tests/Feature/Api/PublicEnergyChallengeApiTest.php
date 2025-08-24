<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\EnergyChallenge;
use App\Models\UserChallengeProgress;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class PublicEnergyChallengeApiTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear desafíos de prueba
        $this->activeChallenge = EnergyChallenge::factory()->active()->create();
        $this->upcomingChallenge = EnergyChallenge::factory()->upcoming()->create();
        $this->completedChallenge = EnergyChallenge::factory()->create([
            'ends_at' => now()->subDays(1)
        ]);
    }

    /** @test */
    public function it_can_list_public_energy_challenges()
    {
        // Crear algunos desafíos adicionales
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
                                'is_active'
                            ]
                        ]
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_can_list_public_active_energy_challenges()
    {
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

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['is_active']);
    }

    /** @test */
    public function it_can_list_public_upcoming_energy_challenges()
    {
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

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertTrue(strtotime($data[0]['starts_at']) > time());
    }

    /** @test */
    public function it_can_show_public_energy_challenge()
    {
        $response = $this->getJson("/api/v1/energy-challenges/{$this->activeChallenge->id}");

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
                        'id' => $this->activeChallenge->id,
                        'title' => $this->activeChallenge->title
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_public_challenge_statistics()
    {
        // Crear algunos progresos de usuario
        UserChallengeProgress::factory()->count(5)->create([
            'challenge_id' => $this->activeChallenge->id
        ]);

        $response = $this->getJson("/api/v1/energy-challenges/{$this->activeChallenge->id}/statistics");

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
    public function it_can_filter_public_challenges_by_type()
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
    public function it_can_filter_public_challenges_by_reward_type()
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
    public function it_can_search_public_challenges()
    {
        EnergyChallenge::factory()->create(['title' => 'Desafío Solar Público']);
        EnergyChallenge::factory()->create(['title' => 'Desafío Eólico Público']);
        EnergyChallenge::factory()->create(['title' => 'Otro Desafío']);

        $response = $this->getJson('/api/v1/energy-challenges?search=solar');

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('solar', strtolower($data[0]['title']));
    }

    /** @test */
    public function it_can_sort_public_challenges()
    {
        EnergyChallenge::factory()->create(['title' => 'A Desafío Público']);
        EnergyChallenge::factory()->create(['title' => 'B Desafío Público']);
        EnergyChallenge::factory()->create(['title' => 'C Desafío Público']);

        $response = $this->getJson('/api/v1/energy-challenges?sort_by=title&sort_order=asc');

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertEquals('A Desafío Público', $data[0]['title']);
        $this->assertEquals('C Desafío Público', $data[2]['title']);
    }

    /** @test */
    public function it_can_paginate_public_challenges()
    {
        EnergyChallenge::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/energy-challenges?per_page=10');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals(10, $data['per_page']);
        $this->assertEquals(25, $data['total']);
        $this->assertCount(10, $data['data']);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_challenge()
    {
        $response = $this->getJson('/api/v1/energy-challenges/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_challenge_statistics()
    {
        $response = $this->getJson('/api/v1/energy-challenges/99999/statistics');

        $response->assertStatus(404);
    }
}
