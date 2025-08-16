<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class TeamMembershipControllerTest extends TestCase
{
    use RefreshDatabase, ApiTestHelpers;

    protected User $user;
    protected User $adminUser;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
        $this->team = Team::factory()->create();
    }

    #[Test]
    public function it_can_list_team_memberships()
    {
        Sanctum::actingAs($this->user);
        
        // Crear membresías de prueba
        TeamMembership::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/team-memberships');

        $response->assertStatus(200);
        $this->assertResourceCollectionStructure($response, [
            'id',
            'team_id',
            'user_id',
            'role',
            'joined_at',
            'left_at',
            'created_at',
            'updated_at',
            'team',
            'user'
        ]);
    }

    #[Test]
    public function it_can_filter_memberships_by_team()
    {
        // Limpiar datos existentes
        TeamMembership::truncate();
        
        Sanctum::actingAs($this->user);
        
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();
        
        TeamMembership::factory()->count(3)->create(['team_id' => $team1->id]);
        TeamMembership::factory()->count(2)->create(['team_id' => $team2->id]);

        $response = $this->getJson("/api/v1/team-memberships?team_id={$team1->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertCollectionFiltered($response->json('data'), 'team_id', $team1->id);
    }

    #[Test]
    public function it_can_filter_memberships_by_user()
    {
        Sanctum::actingAs($this->user);
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        TeamMembership::factory()->count(2)->create(['user_id' => $user1->id]);
        TeamMembership::factory()->count(3)->create(['user_id' => $user2->id]);

        $response = $this->getJson("/api/v1/team-memberships?user_id={$user1->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertCollectionFiltered($response->json('data'), 'user_id', $user1->id);
    }

    #[Test]
    public function it_can_filter_memberships_by_role()
    {
        // Limpiar datos existentes
        TeamMembership::truncate();
        
        Sanctum::actingAs($this->user);
        
        TeamMembership::factory()->admin()->count(2)->create();
        TeamMembership::factory()->member()->count(3)->create();
        TeamMembership::factory()->moderator()->count(1)->create();

        $response = $this->getJson('/api/v1/team-memberships?role=admin');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertCollectionFiltered($response->json('data'), 'role', 'admin');
    }

    #[Test]
    public function it_can_filter_memberships_by_active_status()
    {
        // Limpiar datos existentes
        TeamMembership::truncate();
        
        Sanctum::actingAs($this->user);
        
        TeamMembership::factory()->active()->count(3)->create();
        TeamMembership::factory()->inactive()->count(2)->create();

        $response = $this->getJson('/api/v1/team-memberships?is_active=true');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $membership) {
            $this->assertNull($membership['left_at']);
        }
    }

    #[Test]
    public function it_can_filter_memberships_by_inactive_status()
    {
        Sanctum::actingAs($this->user);
        
        TeamMembership::factory()->active()->count(3)->create();
        TeamMembership::factory()->inactive()->count(2)->create();

        $response = $this->getJson('/api/v1/team-memberships?is_active=false');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $membership) {
            $this->assertNotNull($membership['left_at']);
        }
    }

    #[Test]
    public function it_orders_memberships_by_created_at_desc()
    {
        Sanctum::actingAs($this->user);
        
        TeamMembership::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/team-memberships');

        $response->assertStatus(200);
        $this->assertCollectionOrdered($response->json('data'), 'created_at', 'desc');
    }

    #[Test]
    public function it_can_create_team_membership()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        $member = User::factory()->create();

        $membershipData = [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member'
        ];

        $response = $this->postJson('/api/v1/team-memberships', $membershipData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'team_id' => $team->id,
                'user_id' => $member->id,
                'role' => 'member'
            ],
            'message' => 'Membresía creada exitosamente'
        ]);
        
        $this->assertDatabaseHas('team_memberships', [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member'
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_membership()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/team-memberships', []);

        $this->assertValidationErrors($response, ['team_id', 'user_id', 'role']);
    }

    #[Test]
    public function it_validates_team_exists_when_creating_membership()
    {
        Sanctum::actingAs($this->user);
        
        $member = User::factory()->create();

        $membershipData = [
            'team_id' => 999, // ID inexistente
            'user_id' => $member->id,
            'role' => 'member'
        ];

        $response = $this->postJson('/api/v1/team-memberships', $membershipData);

        $this->assertValidationErrors($response, ['team_id']);
    }

    #[Test]
    public function it_validates_user_exists_when_creating_membership()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();

        $membershipData = [
            'team_id' => $team->id,
            'user_id' => 999, // ID inexistente
            'role' => 'member'
        ];

        $response = $this->postJson('/api/v1/team-memberships', $membershipData);

        $this->assertValidationErrors($response, ['user_id']);
    }

    #[Test]
    public function it_validates_role_when_creating_membership()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        $member = User::factory()->create();

        $membershipData = [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'invalid_role'
        ];

        $response = $this->postJson('/api/v1/team-memberships', $membershipData);

        $this->assertValidationErrors($response, ['role']);
    }

    #[Test]
    public function it_prevents_duplicate_active_memberships()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        $member = User::factory()->create();
        
        // Crear membresía existente (activa)
        TeamMembership::factory()->create([
            'team_id' => $team->id,
            'user_id' => $member->id,
            'left_at' => null
        ]);

        $membershipData = [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member'
        ];

        $response = $this->postJson('/api/v1/team-memberships', $membershipData);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'El usuario ya es miembro de este equipo'
        ]);
    }

    #[Test]
    public function it_allows_creating_membership_if_previous_is_inactive()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        $member = User::factory()->create();
        
        // Crear membresía inactiva (usuario se fue)
        TeamMembership::factory()->create([
            'team_id' => $team->id,
            'user_id' => $member->id,
            'left_at' => now()->subDays(1)
        ]);

        $membershipData = [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member'
        ];

        $response = $this->postJson('/api/v1/team-memberships', $membershipData);

        $response->assertStatus(201);
    }

    #[Test]
    public function it_can_show_team_membership()
    {
        Sanctum::actingAs($this->user);
        
        $membership = TeamMembership::factory()->create();

        $response = $this->getJson("/api/v1/team-memberships/{$membership->id}");

        $response->assertStatus(200);
        $this->assertResourceStructure($response, [
            'id',
            'team_id',
            'user_id',
            'role',
            'joined_at',
            'left_at',
            'created_at',
            'updated_at',
            'team',
            'user'
        ]);
        
        $response->assertJson([
            'data' => [
                'id' => $membership->id,
                'team_id' => $membership->team_id,
                'user_id' => $membership->user_id
            ]
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_membership()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/team-memberships/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_team_membership_role()
    {
        Sanctum::actingAs($this->user);
        
        $membership = TeamMembership::factory()->member()->create();

        $updateData = [
            'role' => 'admin'
        ];

        $response = $this->putJson("/api/v1/team-memberships/{$membership->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'role' => 'admin'
            ],
            'message' => 'Membresía actualizada exitosamente'
        ]);
        
        $this->assertDatabaseHas('team_memberships', [
            'id' => $membership->id,
            'role' => 'admin'
        ]);
    }

    #[Test]
    public function it_can_update_team_membership_status()
    {
        Sanctum::actingAs($this->user);
        
        $membership = TeamMembership::factory()->active()->create();

        $updateData = [
            'role' => 'moderator'
        ];

        $response = $this->putJson("/api/v1/team-memberships/{$membership->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'role' => 'moderator'
            ]
        ]);
        $this->assertDatabaseHas('team_memberships', [
            'id' => $membership->id,
            'role' => 'moderator'
        ]);
    }

    #[Test]
    public function it_validates_role_when_updating_membership()
    {
        Sanctum::actingAs($this->user);
        
        $membership = TeamMembership::factory()->create();

        $updateData = [
            'role' => 'invalid_role'
        ];

        $response = $this->putJson("/api/v1/team-memberships/{$membership->id}", $updateData);

        $this->assertValidationErrors($response, ['role']);
    }

    #[Test]
    public function it_can_delete_team_membership()
    {
        Sanctum::actingAs($this->user);
        
        $membership = TeamMembership::factory()->create();

        $response = $this->deleteJson("/api/v1/team-memberships/{$membership->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Membresía eliminada exitosamente'
        ]);
        
        $this->assertDatabaseMissing('team_memberships', [
            'id' => $membership->id
        ]);
    }

    #[Test]
    public function it_can_leave_team()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        TeamMembership::factory()->create([
            'team_id' => $team->id,
            'user_id' => $this->user->id,
            'left_at' => null
        ]);

        $response = $this->postJson('/api/v1/team-memberships/leave', [
            'team_id' => $team->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Has abandonado el equipo exitosamente'
        ]);
        
        // Verificar que la membresía fue marcada como inactiva
        $membership = TeamMembership::where([
            'team_id' => $team->id,
            'user_id' => $this->user->id
        ])->first();
        
        $this->assertFalse($membership->isActive());
        $this->assertNotNull($membership->left_at);
    }

    #[Test]
    public function it_validates_team_id_when_leaving()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/team-memberships/leave', []);

        $this->assertValidationErrors($response, ['team_id']);
    }

    #[Test]
    public function it_validates_team_exists_when_leaving()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/team-memberships/leave', [
            'team_id' => 999 // ID inexistente
        ]);

        $this->assertValidationErrors($response, ['team_id']);
    }

    #[Test]
    public function it_prevents_leaving_team_if_not_member()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();

        $response = $this->postJson('/api/v1/team-memberships/leave', [
            'team_id' => $team->id
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'No eres miembro de este equipo'
        ]);
    }

    #[Test]
    public function it_prevents_leaving_team_if_already_inactive()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        TeamMembership::factory()->create([
            'team_id' => $team->id,
            'user_id' => $this->user->id,
            'left_at' => now()->subDays(1)
        ]);

        $response = $this->postJson('/api/v1/team-memberships/leave', [
            'team_id' => $team->id
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'No eres miembro de este equipo'
        ]);
    }

    #[Test]
    public function it_requires_authentication_for_all_endpoints()
    {
        $membership = TeamMembership::factory()->create();
        
        $endpoints = [
            'GET' => [
                '/api/v1/team-memberships',
                "/api/v1/team-memberships/{$membership->id}"
            ],
            'POST' => [
                '/api/v1/team-memberships',
                '/api/v1/team-memberships/leave'
            ],
            'PUT' => [
                "/api/v1/team-memberships/{$membership->id}"
            ],
            'DELETE' => [
                "/api/v1/team-memberships/{$membership->id}"
            ]
        ];

        $this->assertEndpointsRequireAuth($endpoints);
    }

    #[Test]
    public function it_includes_team_and_user_relationships()
    {
        Sanctum::actingAs($this->user);
        
        $membership = TeamMembership::factory()->create();

        $response = $this->getJson("/api/v1/team-memberships/{$membership->id}");

        $response->assertStatus(200);
        $this->assertIncludesRelationships($response, ['team', 'user']);
    }

    #[Test]
    public function it_handles_multiple_filters_simultaneously()
    {
        // Limpiar datos existentes
        TeamMembership::truncate();
        
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        $user = User::factory()->create();
        
        // Crear membresías de prueba
        TeamMembership::factory()->admin()->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'left_at' => null
        ]);
        
        TeamMembership::factory()->member()->create([
            'team_id' => $team->id,
            'left_at' => null
        ]);
        
        TeamMembership::factory()->admin()->create([
            'team_id' => $team->id,
            'left_at' => now()->subDays(1)
        ]);

        $response = $this->getJson("/api/v1/team-memberships?team_id={$team->id}&role=admin&is_active=true");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        
        $membership = $response->json('data.0');
        $this->assertEquals($team->id, $membership['team_id']);
        $this->assertEquals('admin', $membership['role']);
        $this->assertNull($membership['left_at']);
    }

    #[Test]
    public function it_creates_membership_with_joined_at_automatically()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        $member = User::factory()->create();

        $membershipData = [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member'
        ];

        $response = $this->postJson('/api/v1/team-memberships', $membershipData);

        $response->assertStatus(201);
        
        $membership = TeamMembership::where([
            'team_id' => $team->id,
            'user_id' => $member->id
        ])->first();
        
        $this->assertNotNull($membership->joined_at);
        $this->assertTrue($membership->isActive());
    }

    #[Test]
    public function it_can_create_memberships_with_different_roles()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create();
        $roles = ['member', 'admin', 'moderator'];

        foreach ($roles as $role) {
            $member = User::factory()->create();
            
            $membershipData = [
                'team_id' => $team->id,
                'user_id' => $member->id,
                'role' => $role
            ];

            $response = $this->postJson('/api/v1/team-memberships', $membershipData);

            $response->assertStatus(201);
            $response->assertJson([
                'data' => [
                    'role' => $role
                ]
            ]);
        }
    }

    #[Test]
    public function it_loads_fresh_data_after_update()
    {
        Sanctum::actingAs($this->user);
        
        $membership = TeamMembership::factory()->member()->create();

        $response = $this->putJson("/api/v1/team-memberships/{$membership->id}", [
            'role' => 'admin'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'role' => 'admin'
            ]
        ]);
        
        // Verificar que los datos están actualizados
        $this->assertEquals('admin', $response->json('data.role'));
    }
}
