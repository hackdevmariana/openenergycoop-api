<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\EnergyChallenge;
use App\Models\UserChallengeProgress;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class UserChallengeProgressApiTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario de prueba
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        
        // Crear desafío de prueba
        $this->challenge = EnergyChallenge::factory()->active()->create();
    }

    /** @test */
    public function it_can_list_user_challenge_progress()
    {
        // Crear algunos progresos
        UserChallengeProgress::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/user-challenge-progress');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'user_id',
                                'challenge_id',
                                'progress_kwh',
                                'completed_at',
                                'created_at',
                                'updated_at'
                            ]
                        ]
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_can_create_user_challenge_progress_when_authenticated()
    {
        Sanctum::actingAs($this->adminUser);

        $progressData = [
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id,
            'progress_kwh' => 25.5,
            'completed_at' => null
        ];

        $response = $this->postJson('/api/v1/user-challenge-progress', $progressData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'user_id',
                        'challenge_id',
                        'progress_kwh',
                        'completed_at'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('user_challenge_progress', [
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id,
            'progress_kwh' => 25.5
        ]);
    }

    /** @test */
    public function it_cannot_create_user_challenge_progress_when_not_authenticated()
    {
        $progressData = [
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id,
            'progress_kwh' => 25.5
        ];

        $response = $this->postJson('/api/v1/user-challenge-progress', $progressData);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_progress()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/user-challenge-progress', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'user_id',
                    'challenge_id',
                    'progress_kwh'
                ]);
    }

    /** @test */
    public function it_cannot_create_duplicate_progress_for_same_user_and_challenge()
    {
        Sanctum::actingAs($this->adminUser);

        // Crear progreso existente
        UserChallengeProgress::factory()->create([
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id
        ]);

        // Intentar crear otro progreso para el mismo usuario y desafío
        $progressData = [
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id,
            'progress_kwh' => 30.0
        ];

        $response = $this->postJson('/api/v1/user-challenge-progress', $progressData);

        $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                    'message' => 'Ya existe un progreso para este usuario en este desafío'
                ]);
    }

    /** @test */
    public function it_can_show_user_challenge_progress()
    {
        $progress = UserChallengeProgress::factory()->create();

        $response = $this->getJson("/api/v1/user-challenge-progress/{$progress->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'user_id',
                        'challenge_id',
                        'progress_kwh',
                        'completed_at'
                    ],
                    'message'
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $progress->id,
                        'user_id' => $progress->user_id
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_user_challenge_progress_when_authenticated()
    {
        Sanctum::actingAs($this->adminUser);
        
        $progress = UserChallengeProgress::factory()->create();
        
        $updateData = [
            'progress_kwh' => 75.0,
            'completed_at' => now()->toISOString()
        ];

        $response = $this->putJson("/api/v1/user-challenge-progress/{$progress->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'progress_kwh',
                        'completed_at'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('user_challenge_progress', [
            'id' => $progress->id,
            'progress_kwh' => 75.0
        ]);
    }

    /** @test */
    public function it_can_delete_user_challenge_progress_when_authenticated()
    {
        Sanctum::actingAs($this->adminUser);
        
        $progress = UserChallengeProgress::factory()->create();

        $response = $this->deleteJson("/api/v1/user-challenge-progress/{$progress->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Progreso eliminado exitosamente'
                ]);

        $this->assertDatabaseMissing('user_challenge_progress', [
            'id' => $progress->id
        ]);
    }

    /** @test */
    public function it_can_update_progress_with_additional_kwh()
    {
        Sanctum::actingAs($this->user);
        
        $progress = UserChallengeProgress::factory()->create([
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id,
            'progress_kwh' => 25.0
        ]);

        $response = $this->postJson("/api/v1/user-challenge-progress/{$progress->id}/update-progress", [
            'additional_kwh' => 15.5
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'progress_kwh'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('user_challenge_progress', [
            'id' => $progress->id,
            'progress_kwh' => 40.5
        ]);
    }

    /** @test */
    public function it_can_complete_challenge()
    {
        Sanctum::actingAs($this->user);
        
        $progress = UserChallengeProgress::factory()->create([
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id,
            'progress_kwh' => 100.0
        ]);

        $response = $this->postJson("/api/v1/user-challenge-progress/{$progress->id}/complete");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'completed_at'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('user_challenge_progress', [
            'id' => $progress->id,
            'completed_at' => now()->toDateString()
        ]);
    }

    /** @test */
    public function it_can_reset_progress()
    {
        Sanctum::actingAs($this->user);
        
        $progress = UserChallengeProgress::factory()->create([
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id,
            'progress_kwh' => 75.0,
            'completed_at' => now()
        ]);

        $response = $this->postJson("/api/v1/user-challenge-progress/{$progress->id}/reset");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'progress_kwh',
                        'completed_at'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('user_challenge_progress', [
            'id' => $progress->id,
            'progress_kwh' => 0.0,
            'completed_at' => null
        ]);
    }

    /** @test */
    public function it_can_get_my_progress_when_authenticated()
    {
        Sanctum::actingAs($this->user);
        
        // Crear progresos para el usuario autenticado
        UserChallengeProgress::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);
        
        // Crear progresos para otros usuarios
        UserChallengeProgress::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/user-challenge-progress/my-progress');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'user_id',
                                'challenge_id',
                                'progress_kwh'
                            ]
                        ]
                    ],
                    'message'
                ]);

        $data = $response->json('data.data');
        $this->assertCount(3, $data);
        foreach ($data as $progress) {
            $this->assertEquals($this->user->id, $progress['user_id']);
        }
    }

    /** @test */
    public function it_cannot_get_my_progress_when_not_authenticated()
    {
        $response = $this->getJson('/api/v1/user-challenge-progress/my-progress');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_join_challenge()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/energy-challenges/{$this->challenge->id}/join");

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'user_id',
                        'challenge_id',
                        'progress_kwh'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('user_challenge_progress', [
            'user_id' => $this->user->id,
            'challenge_id' => $this->challenge->id,
            'progress_kwh' => 0.0
        ]);
    }

    /** @test */
    public function it_cannot_join_challenge_when_not_authenticated()
    {
        $response = $this->postJson("/api/v1/energy-challenges/{$this->challenge->id}/join");

        $response->assertStatus(401);
    }

    /** @test */
    public function it_cannot_join_challenge_twice()
    {
        Sanctum::actingAs($this->user);

        // Unirse al desafío por primera vez
        $this->postJson("/api/v1/energy-challenges/{$this->challenge->id}/join");

        // Intentar unirse de nuevo
        $response = $this->postJson("/api/v1/energy-challenges/{$this->challenge->id}/join");

        $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                    'message' => 'Ya estás participando en este desafío'
                ]);
    }

    /** @test */
    public function it_cannot_join_inactive_challenge()
    {
        Sanctum::actingAs($this->user);
        
        $inactiveChallenge = EnergyChallenge::factory()->create([
            'is_active' => false,
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(40)
        ]);

        $response = $this->postJson("/api/v1/energy-challenges/{$inactiveChallenge->id}/join");

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Este desafío no está disponible actualmente'
                ]);
    }

    /** @test */
    public function it_can_filter_progress_by_user()
    {
        $otherUser = User::factory()->create();
        
        UserChallengeProgress::factory()->count(2)->create([
            'user_id' => $this->user->id
        ]);
        UserChallengeProgress::factory()->count(3)->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->getJson("/api/v1/user-challenge-progress?user_id={$this->user->id}");

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
        foreach ($data as $progress) {
            $this->assertEquals($this->user->id, $progress['user_id']);
        }
    }

    /** @test */
    public function it_can_filter_progress_by_challenge()
    {
        $otherChallenge = EnergyChallenge::factory()->create();
        
        UserChallengeProgress::factory()->count(2)->create([
            'challenge_id' => $this->challenge->id
        ]);
        UserChallengeProgress::factory()->count(3)->create([
            'challenge_id' => $otherChallenge->id
        ]);

        $response = $this->getJson("/api/v1/user-challenge-progress?challenge_id={$this->challenge->id}");

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
        foreach ($data as $progress) {
            $this->assertEquals($this->challenge->id, $progress['challenge_id']);
        }
    }

    /** @test */
    public function it_can_filter_progress_by_status()
    {
        // Crear progresos completados e incompletos
        UserChallengeProgress::factory()->count(2)->create([
            'completed_at' => now()
        ]);
        UserChallengeProgress::factory()->count(3)->create([
            'completed_at' => null
        ]);

        $response = $this->getJson('/api/v1/user-challenge-progress?status=completed');

        $response->assertStatus(200);
        
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
        foreach ($data as $progress) {
            $this->assertNotNull($progress['completed_at']);
        }
    }

    /** @test */
    public function it_can_paginate_progress()
    {
        UserChallengeProgress::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/user-challenge-progress?per_page=10');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals(10, $data['per_page']);
        $this->assertEquals(25, $data['total']);
        $this->assertCount(10, $data['data']);
    }
}
