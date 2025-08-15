<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AchievementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    #[Test]
    public function it_can_list_achievements()
    {
        Sanctum::actingAs($this->user);
        
        // Crear algunos achievements de prueba
        Achievement::factory()->count(3)->active()->create();
        Achievement::factory()->count(2)->inactive()->create();

        $response = $this->getJson('/api/v1/achievements');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'icon',
                    'type',
                    'criteria',
                    'points_reward',
                    'is_active',
                    'sort_order',
                    'unlocks_count',
                    'created_at',
                    'updated_at'
                ]
            ],
            'links',
            'meta'
        ]);
        
        // Por defecto solo debe mostrar los activos
        $this->assertCount(3, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_achievements_by_type()
    {
        Sanctum::actingAs($this->user);
        
        Achievement::factory()->energy()->count(2)->create();
        Achievement::factory()->participation()->count(1)->create();
        Achievement::factory()->community()->count(1)->create();

        $response = $this->getJson('/api/v1/achievements?type=energy');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $achievement) {
            $this->assertEquals('energy', $achievement['type']);
        }
    }

    #[Test]
    public function it_can_show_inactive_achievements()
    {
        Sanctum::actingAs($this->user);
        
        Achievement::factory()->active()->count(2)->create();
        Achievement::factory()->inactive()->count(3)->create();

        $response = $this->getJson('/api/v1/achievements?active_only=false');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function it_can_show_specific_achievement()
    {
        Sanctum::actingAs($this->user);
        
        $achievement = Achievement::factory()->create([
            'name' => 'Test Achievement',
            'type' => 'energy',
            'points_reward' => 100
        ]);

        $response = $this->getJson("/api/v1/achievements/{$achievement->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $achievement->id,
                'name' => 'Test Achievement',
                'type' => 'energy',
                'points_reward' => 100
            ]
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_achievement()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/achievements/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_get_achievement_types()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/achievements/types');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'energy' => 'Energía',
                'participation' => 'Participación',
                'community' => 'Comunidad',
                'milestone' => 'Hito'
            ]
        ]);
    }

    #[Test]
    public function it_can_get_achievement_leaderboard()
    {
        Sanctum::actingAs($this->user);
        
        // Crear achievements con diferentes números de usuarios que los han obtenido
        $popularAchievement = Achievement::factory()->create(['name' => 'Popular Achievement']);
        $lessPopularAchievement = Achievement::factory()->create(['name' => 'Less Popular Achievement']);
        
        // Crear usuarios que han obtenido los achievements
        $users = User::factory()->count(5)->create();
        
        // El popular lo obtienen 4 usuarios
        foreach ($users->take(4) as $user) {
            UserAchievement::factory()->create([
                'user_id' => $user->id,
                'achievement_id' => $popularAchievement->id
            ]);
        }
        
        // El menos popular lo obtienen 2 usuarios
        foreach ($users->take(2) as $user) {
            UserAchievement::factory()->create([
                'user_id' => $user->id,
                'achievement_id' => $lessPopularAchievement->id
            ]);
        }

        $response = $this->getJson('/api/v1/achievements/leaderboard?limit=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'type',
                    'points_reward',
                    'unlocks_count'
                ]
            ]
        ]);
        
        // Verificar que el más popular aparece primero
        $data = $response->json('data');
        $this->assertEquals('Popular Achievement', $data[0]['name']);
        $this->assertEquals(4, $data[0]['unlocks_count']);
    }

    #[Test]
    public function it_requires_authentication_to_access_achievements()
    {
        $response = $this->getJson('/api/v1/achievements');
        $response->assertStatus(401);
        
        $response = $this->getJson('/api/v1/achievements/types');
        $response->assertStatus(401);
        
        $response = $this->getJson('/api/v1/achievements/leaderboard');
        $response->assertStatus(401);
    }

    #[Test]
    public function it_can_paginate_achievements()
    {
        Sanctum::actingAs($this->user);
        
        Achievement::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/achievements?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $response->assertJsonStructure([
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next'
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'per_page',
                'to',
                'total'
            ]
        ]);
    }

    #[Test]
    public function it_includes_unlocks_count_in_response()
    {
        Sanctum::actingAs($this->user);
        
        $achievement = Achievement::factory()->create();
        $users = User::factory()->count(3)->create();
        
        // Crear algunos user achievements
        foreach ($users as $user) {
            UserAchievement::factory()->create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id
            ]);
        }

        $response = $this->getJson("/api/v1/achievements/{$achievement->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $achievement->id,
                'unlocks_count' => 3
            ]
        ]);
    }

    #[Test]
    public function it_orders_achievements_correctly()
    {
        Sanctum::actingAs($this->user);
        
        Achievement::factory()->create(['name' => 'Third', 'sort_order' => 3]);
        Achievement::factory()->create(['name' => 'First', 'sort_order' => 1]);
        Achievement::factory()->create(['name' => 'Second', 'sort_order' => 2]);

        $response = $this->getJson('/api/v1/achievements');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Verificar que están ordenados por sort_order
        $this->assertEquals(1, $data[0]['sort_order']);
        $this->assertEquals(2, $data[1]['sort_order']);
        $this->assertEquals(3, $data[2]['sort_order']);
        
        // Verificar los nombres correspondientes
        $this->assertEquals('First', $data[0]['name']);
        $this->assertEquals('Second', $data[1]['name']);
        $this->assertEquals('Third', $data[2]['name']);
    }
}
