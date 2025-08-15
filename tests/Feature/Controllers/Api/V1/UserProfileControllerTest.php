<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Organization;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;
    protected UserProfile $userProfile;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        
        $this->userProfile = UserProfile::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
            'points_total' => 1500,
            'show_in_rankings' => true
        ]);
    }

    #[Test]
    public function it_can_list_user_profiles()
    {
        Sanctum::actingAs($this->user);
        
        // Crear algunos perfiles adicionales
        UserProfile::factory()->count(3)->create([
            'organization_id' => $this->organization->id
        ]);

        $response = $this->getJson('/api/v1/user-profiles');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'avatar',
                    'bio',
                    'municipality_id',
                    'join_date',
                    'role_in_cooperative',
                    'profile_completed',
                    'newsletter_opt_in',
                    'show_in_rankings',
                    'co2_avoided_total',
                    'kwh_produced_total',
                    'points_total',
                    'badges_earned',
                    'birth_date',
                    'team_id',
                    'age',
                    'organization_rank',
                    'municipality_rank',
                    'created_at',
                    'updated_at',
                    'user',
                    'organization'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_can_filter_profiles_by_organization()
    {
        Sanctum::actingAs($this->user);
        
        $anotherOrg = Organization::factory()->create();
        
        UserProfile::factory()->count(2)->create([
            'organization_id' => $this->organization->id
        ]);
        
        UserProfile::factory()->count(1)->create([
            'organization_id' => $anotherOrg->id
        ]);

        $response = $this->getJson("/api/v1/user-profiles?organization_id={$this->organization->id}");

        $response->assertStatus(200);
        // +1 porque ya existe el perfil del usuario actual
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $profile) {
            $this->assertEquals($this->organization->id, $profile['organization']['id']);
        }
    }

    #[Test]
    public function it_can_filter_profiles_by_rankings_visibility()
    {
        Sanctum::actingAs($this->user);
        
        UserProfile::factory()->inRankings()->count(2)->create([
            'organization_id' => $this->organization->id
        ]);
        
        UserProfile::factory()->notInRankings()->count(1)->create([
            'organization_id' => $this->organization->id
        ]);

        $response = $this->getJson('/api/v1/user-profiles?show_in_rankings=true');

        $response->assertStatus(200);
        // +1 porque el perfil del usuario actual está en rankings
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $profile) {
            $this->assertTrue($profile['show_in_rankings']);
        }
    }

    #[Test]
    public function it_can_filter_profiles_by_municipality()
    {
        Sanctum::actingAs($this->user);
        
        $municipalityId = '28001';
        
        UserProfile::factory()->count(2)->create([
            'municipality_id' => $municipalityId,
            'organization_id' => $this->organization->id
        ]);
        
        UserProfile::factory()->count(1)->create([
            'municipality_id' => '28002',
            'organization_id' => $this->organization->id
        ]);

        $response = $this->getJson("/api/v1/user-profiles?municipality_id={$municipalityId}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $profile) {
            $this->assertEquals($municipalityId, $profile['municipality_id']);
        }
    }

    #[Test]
    public function it_can_show_specific_user_profile()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/user-profiles/{$this->userProfile->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $this->userProfile->id,
                'points_total' => 1500,
                'show_in_rankings' => true
            ]
        ]);
    }

    #[Test]
    public function it_can_get_my_profile()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/user-profiles/me');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $this->userProfile->id,
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ]
            ]
        ]);
    }

    #[Test]
    public function it_returns_404_if_user_has_no_profile()
    {
        $userWithoutProfile = User::factory()->create();
        $userWithoutProfile->assignRole('admin');
        
        Sanctum::actingAs($userWithoutProfile);

        $response = $this->getJson('/api/v1/user-profiles/me');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_my_profile()
    {
        Sanctum::actingAs($this->user);

        $updateData = [
            'bio' => 'Updated bio text',
            'municipality_id' => '28002',
            'role_in_cooperative' => 'promotor',
            'newsletter_opt_in' => true,
            'show_in_rankings' => false,
            'birth_date' => '1990-05-15',
            'team_id' => 'team-green'
        ];

        $response = $this->putJson('/api/v1/user-profiles/me', $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'bio' => 'Updated bio text',
                'municipality_id' => '28002',
                'role_in_cooperative' => 'promotor',
                'newsletter_opt_in' => true,
                'show_in_rankings' => false,
                'birth_date' => '1990-05-15',
                'team_id' => 'team-green'
            ]
        ]);
        
        $this->assertDatabaseHas('user_profiles', [
            'id' => $this->userProfile->id,
            'bio' => 'Updated bio text',
            'municipality_id' => '28002',
            'role_in_cooperative' => 'promotor',
            'newsletter_opt_in' => true,
            'show_in_rankings' => false,
            'team_id' => 'team-green'
        ]);
    }

    #[Test]
    public function it_validates_profile_update_data()
    {
        Sanctum::actingAs($this->user);

        $invalidData = [
            'bio' => str_repeat('a', 501), // Too long
            'role_in_cooperative' => 'invalid_role',
            'birth_date' => '2020-01-01', // Too young
        ];

        $response = $this->putJson('/api/v1/user-profiles/me', $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'bio',
            'role_in_cooperative',
            'birth_date'
        ]);
    }

    #[Test]
    public function it_can_get_organization_ranking()
    {
        Sanctum::actingAs($this->user);
        
        // Crear perfiles con diferentes puntos
        $profiles = UserProfile::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
            'show_in_rankings' => true
        ]);
        
        // Asignar puntos diferentes
        $profiles[0]->update(['points_total' => 2000]);
        $profiles[1]->update(['points_total' => 1800]);
        $profiles[2]->update(['points_total' => 1600]);

        $response = $this->getJson("/api/v1/user-profiles/rankings/organization/{$this->organization->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'points_total',
                    'user'
                ]
            ]
        ]);
        
        // Verificar que están ordenados por puntos (descendente)
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual($data[1]['points_total'], $data[0]['points_total']);
    }

    #[Test]
    public function it_can_get_municipality_ranking()
    {
        Sanctum::actingAs($this->user);
        
        $municipalityId = '28001';
        
        // Crear perfiles en el mismo municipio
        UserProfile::factory()->count(3)->create([
            'municipality_id' => $municipalityId,
            'show_in_rankings' => true,
            'points_total' => 1000
        ]);

        $response = $this->getJson("/api/v1/user-profiles/rankings/municipality/{$municipalityId}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'points_total',
                    'municipality_id',
                    'user',
                    'organization'
                ]
            ]
        ]);
        
        foreach ($response->json('data') as $profile) {
            $this->assertEquals($municipalityId, $profile['municipality_id']);
            $this->assertTrue($profile['show_in_rankings']);
        }
    }

    #[Test]
    public function it_can_get_profile_statistics()
    {
        Sanctum::actingAs($this->user);
        
        // Crear algunos perfiles adicionales para estadísticas
        UserProfile::factory()->completed()->count(5)->create([
            'organization_id' => $this->organization->id,
            'points_total' => 1000,
            'kwh_produced_total' => 500,
            'co2_avoided_total' => 250
        ]);

        $response = $this->getJson('/api/v1/user-profiles/statistics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_profiles',
                'completed_profiles',
                'profiles_in_rankings',
                'total_points',
                'total_kwh_produced',
                'total_co2_avoided',
                'average_points_per_user'
            ]
        ]);
        
        $data = $response->json('data');
        $this->assertIsInt($data['total_profiles']);
        $this->assertIsInt($data['completed_profiles']);
        $this->assertGreaterThan(0, $data['total_profiles']);
    }

    #[Test]
    public function it_orders_profiles_by_points_by_default()
    {
        Sanctum::actingAs($this->user);
        
        // Crear perfiles con diferentes puntos
        UserProfile::factory()->create([
            'points_total' => 500,
            'organization_id' => $this->organization->id
        ]);
        
        UserProfile::factory()->create([
            'points_total' => 2000,
            'organization_id' => $this->organization->id
        ]);

        $response = $this->getJson('/api/v1/user-profiles');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Verificar orden descendente por puntos
        $this->assertGreaterThanOrEqual($data[1]['points_total'], $data[0]['points_total']);
    }

    #[Test]
    public function it_requires_authentication()
    {
        $endpoints = [
            '/api/v1/user-profiles',
            "/api/v1/user-profiles/{$this->userProfile->id}",
            '/api/v1/user-profiles/me',
            "/api/v1/user-profiles/rankings/organization/{$this->organization->id}",
            '/api/v1/user-profiles/rankings/municipality/28001',
            '/api/v1/user-profiles/statistics'
        ];

        foreach ($endpoints as $endpoint) {
            $this->getJson($endpoint)->assertStatus(401);
        }
        
        $this->putJson('/api/v1/user-profiles/me', [])->assertStatus(401);
    }

    #[Test]
    public function it_can_paginate_profiles()
    {
        Sanctum::actingAs($this->user);
        
        UserProfile::factory()->count(25)->create([
            'organization_id' => $this->organization->id
        ]);

        $response = $this->getJson('/api/v1/user-profiles?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    #[Test]
    public function it_limits_ranking_results()
    {
        Sanctum::actingAs($this->user);
        
        UserProfile::factory()->count(60)->create([
            'organization_id' => $this->organization->id,
            'show_in_rankings' => true
        ]);

        $response = $this->getJson("/api/v1/user-profiles/rankings/organization/{$this->organization->id}?limit=20");

        $response->assertStatus(200);
        $this->assertCount(20, $response->json('data'));
    }

    #[Test]
    public function it_includes_user_and_organization_relationships()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/user-profiles/{$this->userProfile->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email'
                ],
                'organization' => [
                    'id',
                    'name'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_calculates_ranks_correctly()
    {
        Sanctum::actingAs($this->user);
        
        // El perfil actual tiene 1500 puntos
        // Crear perfiles con más y menos puntos
        UserProfile::factory()->create([
            'points_total' => 2000, // Mejor que el usuario actual
            'organization_id' => $this->organization->id,
            'show_in_rankings' => true
        ]);
        
        UserProfile::factory()->create([
            'points_total' => 1000, // Peor que el usuario actual
            'organization_id' => $this->organization->id,
            'show_in_rankings' => true
        ]);

        $response = $this->getJson('/api/v1/user-profiles/me');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // El usuario debería estar en segundo lugar (rank 2)
        $this->assertEquals(2, $data['organization_rank']);
    }
}
