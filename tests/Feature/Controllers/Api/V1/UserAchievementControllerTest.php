<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserAchievementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $adminUser;
    protected Achievement $achievement;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
        
        $this->achievement = Achievement::factory()->create([
            'name' => 'Test Achievement',
            'type' => 'energy',
            'points_reward' => 100
        ]);
    }

    #[Test]
    public function it_can_list_user_achievements()
    {
        Sanctum::actingAs($this->adminUser);
        
        // Crear algunos user achievements
        UserAchievement::factory()->count(3)->create([
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->getJson('/api/v1/user-achievements');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'earned_at',
                    'custom_message',
                    'reward_granted',
                    'created_at',
                    'updated_at',
                    'user',
                    'achievement'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_can_filter_achievements_by_user()
    {
        Sanctum::actingAs($this->adminUser);
        
        $anotherUser = User::factory()->create();
        
        UserAchievement::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);
        
        UserAchievement::factory()->count(1)->create([
            'user_id' => $anotherUser->id,
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->getJson("/api/v1/user-achievements?user_id={$this->user->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $userAchievement) {
            $this->assertEquals($this->user->id, $userAchievement['user']['id']);
        }
    }

    #[Test]
    public function it_can_filter_achievements_by_type()
    {
        Sanctum::actingAs($this->adminUser);
        
        $energyAchievement = Achievement::factory()->energy()->create();
        $participationAchievement = Achievement::factory()->participation()->create();
        
        UserAchievement::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'achievement_id' => $energyAchievement->id
        ]);
        
        UserAchievement::factory()->count(1)->create([
            'user_id' => $this->user->id,
            'achievement_id' => $participationAchievement->id
        ]);

        $response = $this->getJson('/api/v1/user-achievements?achievement_type=energy');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $userAchievement) {
            $this->assertEquals('energy', $userAchievement['achievement']['type']);
        }
    }

    #[Test]
    public function it_can_filter_by_reward_status()
    {
        Sanctum::actingAs($this->adminUser);
        
        UserAchievement::factory()->rewardGranted()->count(2)->create([
            'achievement_id' => $this->achievement->id
        ]);
        
        UserAchievement::factory()->pendingReward()->count(1)->create([
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->getJson('/api/v1/user-achievements?reward_granted=true');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $userAchievement) {
            $this->assertTrue($userAchievement['reward_granted']);
        }
    }

    #[Test]
    public function it_can_show_specific_user_achievement()
    {
        Sanctum::actingAs($this->adminUser);
        
        $userAchievement = UserAchievement::factory()->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id,
            'custom_message' => 'Great job!'
        ]);

        $response = $this->getJson("/api/v1/user-achievements/{$userAchievement->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $userAchievement->id,
                'custom_message' => 'Great job!'
            ]
        ]);
    }

    #[Test]
    public function it_can_get_my_achievements()
    {
        Sanctum::actingAs($this->user);
        
        // Crear achievements para el usuario actual
        UserAchievement::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);
        
        // Crear achievements para otro usuario (no deberían aparecer)
        $anotherUser = User::factory()->create();
        UserAchievement::factory()->count(2)->create([
            'user_id' => $anotherUser->id,
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->getJson('/api/v1/user-achievements/me');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $userAchievement) {
            $this->assertEquals($this->user->id, $userAchievement['user']['id']);
        }
    }

    #[Test]
    public function it_can_get_my_recent_achievements()
    {
        Sanctum::actingAs($this->user);
        
        // Crear achievements recientes y antiguos
        UserAchievement::factory()->recent()->count(3)->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);
        
        UserAchievement::factory()->old()->count(2)->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->getJson('/api/v1/user-achievements/me/recent?limit=2');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        // Verificar que están ordenados por fecha más reciente
        $data = $response->json('data');
        $firstDate = \Carbon\Carbon::parse($data[0]['earned_at']);
        $secondDate = \Carbon\Carbon::parse($data[1]['earned_at']);
        
        $this->assertGreaterThanOrEqual($secondDate, $firstDate);
    }

    #[Test]
    public function it_can_get_my_achievement_statistics()
    {
        Sanctum::actingAs($this->user);
        
        // Crear diferentes tipos de achievements
        $energyAchievement = Achievement::factory()->energy()->create(['points_reward' => 100]);
        $participationAchievement = Achievement::factory()->participation()->create(['points_reward' => 50]);
        
        UserAchievement::factory()->rewardGranted()->count(2)->create([
            'user_id' => $this->user->id,
            'achievement_id' => $energyAchievement->id
        ]);
        
        UserAchievement::factory()->pendingReward()->count(1)->create([
            'user_id' => $this->user->id,
            'achievement_id' => $participationAchievement->id
        ]);

        $response = $this->getJson('/api/v1/user-achievements/me/statistics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_achievements',
                'rewards_granted',
                'pending_rewards',
                'by_type' => [
                    'energy',
                    'participation',
                    'community',
                    'milestone'
                ],
                'total_points_earned'
            ]
        ]);
        
        $data = $response->json('data');
        $this->assertEquals(3, $data['total_achievements']);
        $this->assertEquals(2, $data['rewards_granted']);
        $this->assertEquals(1, $data['pending_rewards']);
        $this->assertEquals(2, $data['by_type']['energy']);
        $this->assertEquals(1, $data['by_type']['participation']);
        $this->assertEquals(250, $data['total_points_earned']); // 2*100 + 1*50
    }

    #[Test]
    public function it_can_grant_reward_for_achievement()
    {
        Sanctum::actingAs($this->adminUser);
        
        $userAchievement = UserAchievement::factory()->pendingReward()->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->postJson("/api/v1/user-achievements/{$userAchievement->id}/grant-reward");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Recompensa otorgada exitosamente',
            'data' => [
                'id' => $userAchievement->id,
                'reward_granted' => true
            ]
        ]);
        
        $this->assertDatabaseHas('user_achievements', [
            'id' => $userAchievement->id,
            'reward_granted' => true
        ]);
    }

    #[Test]
    public function it_cannot_grant_reward_twice()
    {
        Sanctum::actingAs($this->adminUser);
        
        $userAchievement = UserAchievement::factory()->rewardGranted()->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->postJson("/api/v1/user-achievements/{$userAchievement->id}/grant-reward");

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'La recompensa ya fue otorgada'
        ]);
    }

    #[Test]
    public function it_can_get_user_achievements_leaderboard()
    {
        Sanctum::actingAs($this->adminUser);
        
        // Crear usuarios con diferentes números de achievements
        $user1 = User::factory()->create(['name' => 'Top User']);
        $user2 = User::factory()->create(['name' => 'Second User']);
        $user3 = User::factory()->create(['name' => 'Third User']);
        
        // User1 tiene 5 achievements (500 puntos)
        UserAchievement::factory()->count(5)->create([
            'user_id' => $user1->id,
            'achievement_id' => Achievement::factory()->create(['points_reward' => 100])->id
        ]);
        
        // User2 tiene 3 achievements (150 puntos)
        UserAchievement::factory()->count(3)->create([
            'user_id' => $user2->id,
            'achievement_id' => Achievement::factory()->create(['points_reward' => 50])->id
        ]);
        
        // User3 tiene 2 achievements (200 puntos)
        UserAchievement::factory()->count(2)->create([
            'user_id' => $user3->id,
            'achievement_id' => Achievement::factory()->create(['points_reward' => 100])->id
        ]);

        $response = $this->getJson('/api/v1/user-achievements/leaderboard?limit=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'user_id',
                    'user_name',
                    'achievements_count',
                    'total_points'
                ]
            ]
        ]);
        
        $data = $response->json('data');
        
        // Verificar que están ordenados por número de achievements (descendente)
        $this->assertEquals('Top User', $data[0]['user_name']);
        $this->assertEquals(5, $data[0]['achievements_count']);
        $this->assertEquals(500, $data[0]['total_points']);
    }

    #[Test]
    public function it_orders_achievements_by_earned_date()
    {
        Sanctum::actingAs($this->adminUser);
        
        $old = UserAchievement::factory()->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id,
            'earned_at' => now()->subDays(5)
        ]);
        
        $recent = UserAchievement::factory()->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id,
            'earned_at' => now()->subDays(1)
        ]);

        $response = $this->getJson('/api/v1/user-achievements');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // El más reciente debe aparecer primero
        $this->assertEquals($recent->id, $data[0]['id']);
        $this->assertEquals($old->id, $data[1]['id']);
    }

    #[Test]
    public function it_requires_authentication_for_protected_endpoints()
    {
        $userAchievement = UserAchievement::factory()->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);

        $endpoints = [
            '/api/v1/user-achievements',
            "/api/v1/user-achievements/{$userAchievement->id}",
            '/api/v1/user-achievements/me',
            '/api/v1/user-achievements/me/recent',
            '/api/v1/user-achievements/me/statistics',
            '/api/v1/user-achievements/leaderboard'
        ];

        foreach ($endpoints as $endpoint) {
            $this->getJson($endpoint)->assertStatus(401);
        }
        
        $this->postJson("/api/v1/user-achievements/{$userAchievement->id}/grant-reward")
             ->assertStatus(401);
    }

    #[Test]
    public function regular_users_cannot_grant_rewards()
    {
        Sanctum::actingAs($this->user); // Usuario regular, no admin
        
        $userAchievement = UserAchievement::factory()->pendingReward()->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->postJson("/api/v1/user-achievements/{$userAchievement->id}/grant-reward");

        $response->assertStatus(403); // Forbidden
    }

    #[Test]
    public function it_can_paginate_user_achievements()
    {
        Sanctum::actingAs($this->adminUser);
        
        UserAchievement::factory()->count(25)->create([
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->getJson('/api/v1/user-achievements?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    #[Test]
    public function it_includes_related_data_in_responses()
    {
        Sanctum::actingAs($this->adminUser);
        
        $userAchievement = UserAchievement::factory()->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->getJson("/api/v1/user-achievements/{$userAchievement->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email'
                ],
                'achievement' => [
                    'id',
                    'name',
                    'description',
                    'icon',
                    'type',
                    'points_reward'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_achievement()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/user-achievements/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function my_recent_achievements_respects_limit()
    {
        Sanctum::actingAs($this->user);
        
        UserAchievement::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'achievement_id' => $this->achievement->id
        ]);

        $response = $this->getJson('/api/v1/user-achievements/me/recent?limit=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }
}
