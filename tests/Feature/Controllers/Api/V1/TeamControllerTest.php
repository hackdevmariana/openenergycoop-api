<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Organization;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $adminUser;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
    }

    #[Test]
    public function it_can_list_teams()
    {
        Sanctum::actingAs($this->user);
        
        Team::factory()->count(5)->create([
            'organization_id' => $this->organization->id
        ]);

        $response = $this->getJson('/api/v1/teams');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'is_open',
                    'max_members',
                    'logo_path',
                    'members_count',
                    'is_full',
                    'can_join',
                    'created_at',
                    'updated_at',
                    'organization',
                    'created_by',
                    'meta'
                ]
            ],
            'links',
            'meta'
        ]);
    }

    #[Test]
    public function it_can_filter_teams_by_organization()
    {
        Sanctum::actingAs($this->user);
        
        $anotherOrg = Organization::factory()->create();
        
        Team::factory()->count(3)->create([
            'organization_id' => $this->organization->id
        ]);
        
        Team::factory()->count(2)->create([
            'organization_id' => $anotherOrg->id
        ]);

        $response = $this->getJson("/api/v1/teams?organization_id={$this->organization->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $team) {
            $this->assertEquals($this->organization->id, $team['organization']['id']);
        }
    }

    #[Test]
    public function it_can_filter_teams_by_open_status()
    {
        Sanctum::actingAs($this->user);
        
        Team::factory()->open()->count(2)->create();
        Team::factory()->closed()->count(3)->create();

        $response = $this->getJson('/api/v1/teams?is_open=true');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $team) {
            $this->assertTrue($team['is_open']);
        }
    }

    #[Test]
    public function it_can_filter_teams_with_space_available()
    {
        Sanctum::actingAs($this->user);
        
        // Equipo con espacio (5 miembros, límite 10)
        $teamWithSpace = Team::factory()->withMemberLimit(10)->create();
        TeamMembership::factory()->count(5)->create(['team_id' => $teamWithSpace->id]);
        
        // Equipo lleno (10 miembros, límite 10)
        $fullTeam = Team::factory()->withMemberLimit(10)->create();
        TeamMembership::factory()->count(10)->create(['team_id' => $fullTeam->id]);
        
        // Equipo sin límite
        $unlimitedTeam = Team::factory()->withoutMemberLimit()->create();
        TeamMembership::factory()->count(15)->create(['team_id' => $unlimitedTeam->id]);

        $response = $this->getJson('/api/v1/teams?has_space=true');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Debe incluir el equipo con espacio y el sin límite
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    #[Test]
    public function it_can_search_teams()
    {
        Sanctum::actingAs($this->user);
        
        Team::factory()->create(['name' => 'Green Warriors']);
        Team::factory()->create(['name' => 'Solar Squad']);
        Team::factory()->create(['description' => 'A team focused on green energy']);

        $response = $this->getJson('/api/v1/teams?search=green');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_create_team()
    {
        Sanctum::actingAs($this->user);

        $teamData = [
            'name' => 'Test Team',
            'description' => 'A test team for unit testing',
            'organization_id' => $this->organization->id,
            'is_open' => true,
            'max_members' => 20
        ];

        $response = $this->postJson('/api/v1/teams', $teamData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => 'Test Team',
                'slug' => 'test-team',
                'description' => 'A test team for unit testing',
                'is_open' => true,
                'max_members' => 20
            ]
        ]);
        
        $this->assertDatabaseHas('teams', [
            'name' => 'Test Team',
            'slug' => 'test-team',
            'created_by_user_id' => $this->user->id,
            'organization_id' => $this->organization->id
        ]);
        
        // Verificar que el creador se agregó como admin automáticamente
        $team = Team::where('name', 'Test Team')->first();
        $this->assertDatabaseHas('team_memberships', [
            'team_id' => $team->id,
            'user_id' => $this->user->id,
            'role' => 'admin'
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_team()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/teams', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_validates_unique_slug_when_creating_team()
    {
        Sanctum::actingAs($this->user);
        
        Team::factory()->create(['slug' => 'test-team']);

        $teamData = [
            'name' => 'Test Team',
            'slug' => 'test-team'
        ];

        $response = $this->postJson('/api/v1/teams', $teamData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['slug']);
    }

    #[Test]
    public function it_can_show_team()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'created_by_user_id' => $this->user->id
        ]);
        
        // Agregar algunos miembros
        TeamMembership::factory()->count(3)->create(['team_id' => $team->id]);

        $response = $this->getJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $team->id,
                'name' => $team->name
            ]
        ]);
        
        $response->assertJsonStructure([
            'data' => [
                'members',
                'statistics',
                'current_challenges'
            ]
        ]);
    }

    #[Test]
    public function it_can_update_team()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create([
            'created_by_user_id' => $this->user->id,
            'name' => 'Original Name'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'is_open' => false,
            'max_members' => 25
        ];

        $response = $this->putJson("/api/v1/teams/{$team->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'name' => 'Updated Name',
                'slug' => 'updated-name',
                'description' => 'Updated description',
                'is_open' => false,
                'max_members' => 25
            ]
        ]);
        
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Updated Name',
            'slug' => 'updated-name'
        ]);
    }

    #[Test]
    public function it_prevents_unauthorized_team_updates()
    {
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);
        
        $team = Team::factory()->create([
            'created_by_user_id' => $this->user->id
        ]);

        $response = $this->putJson("/api/v1/teams/{$team->id}", [
            'name' => 'Unauthorized Update'
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_delete_team()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create([
            'created_by_user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    #[Test]
    public function it_prevents_unauthorized_team_deletion()
    {
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);
        
        $team = Team::factory()->create([
            'created_by_user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_join_open_team()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->open()->withMemberLimit(10)->create();

        $response = $this->postJson("/api/v1/teams/{$team->id}/join");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Te has unido al equipo exitosamente'
        ]);
        
        $this->assertDatabaseHas('team_memberships', [
            'team_id' => $team->id,
            'user_id' => $this->user->id,
            'role' => 'member'
        ]);
    }

    #[Test]
    public function it_cannot_join_closed_team()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->closed()->create();

        $response = $this->postJson("/api/v1/teams/{$team->id}/join");

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'No puedes unirte a este equipo'
        ]);
    }

    #[Test]
    public function it_cannot_join_full_team()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->open()->withMemberLimit(2)->create();
        TeamMembership::factory()->count(2)->create(['team_id' => $team->id]);

        $response = $this->postJson("/api/v1/teams/{$team->id}/join");

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'No puedes unirte a este equipo'
        ]);
    }

    #[Test]
    public function it_cannot_join_team_twice()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->open()->create();
        TeamMembership::factory()->create([
            'team_id' => $team->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->postJson("/api/v1/teams/{$team->id}/join");

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'No puedes unirte a este equipo'
        ]);
    }

    #[Test]
    public function it_can_leave_team()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        TeamMembership::factory()->create([
            'team_id' => $team->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->postJson("/api/v1/teams/{$team->id}/leave");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Has salido del equipo exitosamente'
        ]);
        
        // Verificar que el campo left_at fue actualizado
        $membership = \App\Models\TeamMembership::where([
            'team_id' => $team->id,
            'user_id' => $this->user->id
        ])->first();
        
        $this->assertNotNull($membership->left_at);
    }

    #[Test]
    public function it_cannot_leave_team_if_not_member()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();

        $response = $this->postJson("/api/v1/teams/{$team->id}/leave");

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'No eres miembro de este equipo'
        ]);
    }

    #[Test]
    public function it_can_list_team_members()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        
        TeamMembership::factory()->admin()->count(1)->create(['team_id' => $team->id]);
        TeamMembership::factory()->moderator()->count(2)->create(['team_id' => $team->id]);
        TeamMembership::factory()->member()->count(3)->create(['team_id' => $team->id]);

        $response = $this->getJson("/api/v1/teams/{$team->id}/members");

        $response->assertStatus(200);
        // Total: 1 admin + 2 moderators + 3 members = 6 members
        $this->assertCount(6, $response->json('data'));
        
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'team_id',
                    'user_id',
                    'role',
                    'joined_at',
                    'user'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_can_filter_team_members_by_role()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        
        TeamMembership::factory()->admin()->count(2)->create(['team_id' => $team->id]);
        TeamMembership::factory()->member()->count(3)->create(['team_id' => $team->id]);

        $response = $this->getJson("/api/v1/teams/{$team->id}/members?role=admin");

        $response->assertStatus(200);
        // Solo debe retornar los 2 admins creados explícitamente
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $membership) {
            $this->assertEquals('admin', $membership['role']);
        }
    }

    #[Test]
    public function it_can_get_team_recommendations()
    {
        Sanctum::actingAs($this->user);
        
        // Crear equipos abiertos con espacio
        Team::factory()->open()->withMemberLimit(10)->count(3)->create();
        
        // Crear equipo cerrado (no debe aparecer)
        Team::factory()->closed()->create();
        
        // Crear equipo donde el usuario ya es miembro
        $userTeam = Team::factory()->open()->create(['created_by_user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/teams/recommendations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'is_open',
                    'can_join'
                ]
            ]
        ]);
        
        // No debe incluir el equipo del usuario
        $teamIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($userTeam->id, $teamIds);
    }

    #[Test]
    public function it_can_get_my_teams()
    {
        Sanctum::actingAs($this->user);
        
        // Equipos donde el usuario es miembro
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();
        
        TeamMembership::factory()->create([
            'team_id' => $team1->id,
            'user_id' => $this->user->id
        ]);
        
        TeamMembership::factory()->create([
            'team_id' => $team2->id,
            'user_id' => $this->user->id
        ]);
        
        // Equipo donde no es miembro
        Team::factory()->create();

        $response = $this->getJson('/api/v1/teams/my-teams');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        $teamIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($team1->id, $teamIds);
        $this->assertContains($team2->id, $teamIds);
    }

    #[Test]
    public function it_requires_authentication_for_all_endpoints()
    {
        $team = Team::factory()->create();
        
        $endpoints = [
            'GET' => [
                '/api/v1/teams',
                "/api/v1/teams/{$team->id}",
                "/api/v1/teams/{$team->id}/members",
                '/api/v1/teams/recommendations',
                '/api/v1/teams/my-teams'
            ],
            'POST' => [
                '/api/v1/teams',
                "/api/v1/teams/{$team->id}/join",
                "/api/v1/teams/{$team->id}/leave"
            ],
            'PUT' => [
                "/api/v1/teams/{$team->id}"
            ],
            'DELETE' => [
                "/api/v1/teams/{$team->id}"
            ]
        ];

        foreach ($endpoints as $method => $urls) {
            foreach ($urls as $url) {
                $response = match ($method) {
                    'GET' => $this->getJson($url),
                    'POST' => $this->postJson($url, []),
                    'PUT' => $this->putJson($url, []),
                    'DELETE' => $this->deleteJson($url),
                };
                
                $response->assertStatus(401, "Endpoint {$method} {$url} should require authentication");
            }
        }
    }

    #[Test]
    public function it_handles_pagination_correctly()
    {
        Sanctum::actingAs($this->user);
        
        Team::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/teams?per_page=10');

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
        
        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    #[Test]
    public function team_admin_can_manage_team()
    {
        $teamAdmin = User::factory()->create();
        Sanctum::actingAs($teamAdmin);
        
        $team = Team::factory()->create();
        TeamMembership::factory()->admin()->create([
            'team_id' => $team->id,
            'user_id' => $teamAdmin->id
        ]);

        // Admin del equipo puede actualizar
        $response = $this->putJson("/api/v1/teams/{$team->id}", [
            'name' => 'Updated by Admin'
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function it_returns_404_for_non_existent_team()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/teams/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_validates_max_members_not_less_than_current_members()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create([
            'created_by_user_id' => $this->user->id,
            'max_members' => 10
        ]);
        
        // Agregar 5 miembros
        TeamMembership::factory()->count(5)->create(['team_id' => $team->id]);

        // Intentar reducir límite a menos de los miembros actuales
        $response = $this->putJson("/api/v1/teams/{$team->id}", [
            'max_members' => 3
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['max_members']);
    }
}
