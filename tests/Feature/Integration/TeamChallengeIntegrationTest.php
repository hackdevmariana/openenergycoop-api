<?php

namespace Tests\Feature\Integration;

use App\Models\Challenge;
use App\Models\Organization;
use App\Models\Team;
use App\Models\TeamChallengeProgress;
use App\Models\TeamMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeamChallengeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $anotherUser;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
    }

    #[Test]
    public function complete_team_creation_and_membership_flow()
    {
        Sanctum::actingAs($this->user);

        // 1. Create a team
        $teamData = [
            'name' => 'Green Warriors',
            'description' => 'A team dedicated to renewable energy',
            'organization_id' => $this->organization->id,
            'is_open' => true,
            'max_members' => 10
        ];

        $response = $this->postJson('/api/v1/teams', $teamData);
        $response->assertStatus(201);
        
        $teamId = $response->json('data.id');
        $team = Team::find($teamId);

        // 2. Verify creator is automatically added as admin
        $this->assertDatabaseHas('team_memberships', [
            'team_id' => $teamId,
            'user_id' => $this->user->id,
            'role' => 'admin'
        ]);

        // 3. Another user joins the team
        Sanctum::actingAs($this->anotherUser);
        $response = $this->postJson("/api/v1/teams/{$teamId}/join");
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Te has unido al equipo exitosamente']);

        // 4. Verify membership was created
        $this->assertDatabaseHas('team_memberships', [
            'team_id' => $teamId,
            'user_id' => $this->anotherUser->id,
            'role' => 'member'
        ]);

        // 5. Check team members endpoint
        $response = $this->getJson("/api/v1/teams/{$teamId}/members");
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 6. User leaves the team
        $response = $this->postJson("/api/v1/teams/{$teamId}/leave");
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Has salido del equipo exitosamente']);

        // 7. Verify membership is marked as left
        $this->assertDatabaseHas('team_memberships', [
            'team_id' => $teamId,
            'user_id' => $this->anotherUser->id,
            'left_at' => now()->toDateString()
        ]);

        // 8. Check that user no longer appears in active members
        $response = $this->getJson("/api/v1/teams/{$teamId}/members");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data')); // Only the admin remains
    }

    #[Test]
    public function complete_challenge_participation_flow()
    {
        Sanctum::actingAs($this->user);

        // 1. Create a team
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'created_by_user_id' => $this->user->id
        ]);

        // 2. Create a challenge
        $challenge = Challenge::factory()->current()->team()->create([
            'name' => 'Solar Energy Challenge',
            'target_kwh' => 2000,
            'points_reward' => 500,
            'organization_id' => $this->organization->id
        ]);

        // 3. Get challenge recommendations for the team
        $response = $this->getJson("/api/v1/challenges/recommendations/{$team->id}");
        $response->assertStatus(200);
        
        $recommendedChallenges = $response->json('data');
        $this->assertGreaterThan(0, count($recommendedChallenges));
        
        $challengeIds = collect($recommendedChallenges)->pluck('id')->toArray();
        $this->assertContains($challenge->id, $challengeIds);

        // 4. Simulate team participating in challenge (normally done through business logic)
        TeamChallengeProgress::factory()->create([
            'team_id' => $team->id,
            'challenge_id' => $challenge->id,
            'progress_kwh' => 1500 // 75% progress
        ]);

        // 5. Check challenge leaderboard
        $response = $this->getJson("/api/v1/challenges/{$challenge->id}/leaderboard");
        $response->assertStatus(200);
        
        $leaderboard = $response->json('data');
        $this->assertCount(1, $leaderboard);
        $this->assertEquals($team->id, $leaderboard[0]['team_id']);
        $this->assertEquals(1500, $leaderboard[0]['progress_kwh']);
        $this->assertEquals(75.0, $leaderboard[0]['progress_percentage']);

        // 6. Check team details include challenge progress
        $response = $this->getJson("/api/v1/teams/{$team->id}");
        $response->assertStatus(200);
        
        $teamData = $response->json('data');
        $this->assertArrayHasKey('current_challenges', $teamData);
        $this->assertCount(1, $teamData['current_challenges']);
        $this->assertEquals($challenge->id, $teamData['current_challenges'][0]['id']);
    }

    #[Test]
    public function team_recommendations_exclude_user_teams_and_full_teams()
    {
        Sanctum::actingAs($this->user);

        // 1. Create team where user is member (should be excluded)
        $userTeam = Team::factory()->open()->create([
            'created_by_user_id' => $this->user->id
        ]);

        // 2. Create full team (should be excluded)
        $fullTeam = Team::factory()->open()->withMemberLimit(2)->create();
        TeamMembership::factory()->count(2)->create(['team_id' => $fullTeam->id]);

        // 3. Create closed team (should be excluded)
        $closedTeam = Team::factory()->closed()->create();

        // 4. Create valid team for recommendations
        $validTeam = Team::factory()->open()->withMemberLimit(10)->create();
        TeamMembership::factory()->count(3)->create(['team_id' => $validTeam->id]);

        // 5. Get recommendations
        $response = $this->getJson('/api/v1/teams/recommendations');
        $response->assertStatus(200);

        $recommendations = $response->json('data');
        $recommendedIds = collect($recommendations)->pluck('id')->toArray();

        // 6. Verify exclusions and inclusions
        $this->assertNotContains($userTeam->id, $recommendedIds);
        $this->assertNotContains($fullTeam->id, $recommendedIds);
        $this->assertNotContains($closedTeam->id, $recommendedIds);
        $this->assertContains($validTeam->id, $recommendedIds);
    }

    #[Test]
    public function challenge_statistics_reflect_real_data()
    {
        Sanctum::actingAs($this->user);

        // 1. Create challenges of different types
        $teamChallenges = Challenge::factory()->team()->count(3)->create();
        $individualChallenges = Challenge::factory()->individual()->count(2)->create();
        $orgChallenges = Challenge::factory()->organization()->count(1)->create();

        // 2. Create teams and progress
        $teams = Team::factory()->count(4)->create();
        
        // 3. Add progress to some challenges
        foreach ($teamChallenges->take(2) as $challenge) {
            foreach ($teams->take(2) as $team) {
                TeamChallengeProgress::factory()->create([
                    'team_id' => $team->id,
                    'challenge_id' => $challenge->id,
                    'progress_kwh' => $challenge->target_kwh * 0.8 // 80% progress
                ]);
            }
        }

        // 4. Complete one challenge
        TeamChallengeProgress::factory()->completed()->create([
            'team_id' => $teams[0]->id,
            'challenge_id' => $teamChallenges[0]->id,
            'progress_kwh' => $teamChallenges[0]->target_kwh
        ]);

        // 5. Get statistics
        $response = $this->getJson('/api/v1/challenges/statistics');
        $response->assertStatus(200);

        $stats = $response->json('data');

        // 6. Verify statistics
        $this->assertEquals(6, $stats['total_challenges']); // 3+2+1
        $this->assertEquals(3, $stats['by_type']['team']);
        $this->assertEquals(2, $stats['by_type']['individual']);
        $this->assertEquals(1, $stats['by_type']['organization']);
        $this->assertGreaterThan(0, $stats['total_participants']);
        $this->assertGreaterThan(0, $stats['completed_challenges']);
    }

    #[Test]
    public function team_filtering_works_across_all_parameters()
    {
        Sanctum::actingAs($this->user);

        $anotherOrg = Organization::factory()->create();

        // 1. Create teams with different characteristics
        $openTeamWithSpace = Team::factory()->open()->withMemberLimit(10)->create([
            'name' => 'Open Team',
            'organization_id' => $this->organization->id
        ]);
        TeamMembership::factory()->count(5)->create(['team_id' => $openTeamWithSpace->id]);

        $closedTeam = Team::factory()->closed()->create([
            'name' => 'Closed Team',
            'organization_id' => $this->organization->id
        ]);

        $fullTeam = Team::factory()->open()->withMemberLimit(3)->create([
            'name' => 'Full Team',
            'organization_id' => $this->organization->id
        ]);
        TeamMembership::factory()->count(3)->create(['team_id' => $fullTeam->id]);

        $anotherOrgTeam = Team::factory()->open()->create([
            'name' => 'Another Org Team',
            'organization_id' => $anotherOrg->id
        ]);

        // 2. Test organization filter
        $response = $this->getJson("/api/v1/teams?organization_id={$this->organization->id}");
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));

        // 3. Test open status filter
        $response = $this->getJson('/api/v1/teams?is_open=true');
        $response->assertStatus(200);
        $openTeams = $response->json('data');
        foreach ($openTeams as $team) {
            $this->assertTrue($team['is_open']);
        }

        // 4. Test has_space filter
        $response = $this->getJson('/api/v1/teams?has_space=true');
        $response->assertStatus(200);
        $teamsWithSpace = $response->json('data');
        
        $teamIds = collect($teamsWithSpace)->pluck('id')->toArray();
        $this->assertContains($openTeamWithSpace->id, $teamIds);
        $this->assertNotContains($fullTeam->id, $teamIds);

        // 5. Test search filter
        $response = $this->getJson('/api/v1/teams?search=open');
        $response->assertStatus(200);
        $searchResults = $response->json('data');
        $this->assertGreaterThan(0, count($searchResults));
        
        $foundOpenTeam = collect($searchResults)->firstWhere('name', 'Open Team');
        $this->assertNotNull($foundOpenTeam);
    }

    #[Test]
    public function challenge_filtering_works_across_all_parameters()
    {
        Sanctum::actingAs($this->user);

        $anotherOrg = Organization::factory()->create();

        // 1. Create challenges with different characteristics
        $currentTeamChallenge = Challenge::factory()->current()->team()->create([
            'name' => 'Current Team Challenge',
            'organization_id' => $this->organization->id
        ]);

        $upcomingIndividualChallenge = Challenge::factory()->upcoming()->individual()->create([
            'name' => 'Upcoming Individual Challenge',
            'organization_id' => $this->organization->id
        ]);

        $pastOrgChallenge = Challenge::factory()->past()->organization()->create([
            'name' => 'Past Org Challenge',
            'organization_id' => $this->organization->id
        ]);

        $inactiveChallenge = Challenge::factory()->inactive()->create([
            'name' => 'Inactive Challenge',
            'organization_id' => $this->organization->id
        ]);

        $anotherOrgChallenge = Challenge::factory()->current()->team()->create([
            'name' => 'Another Org Challenge',
            'organization_id' => $anotherOrg->id
        ]);

        // 2. Test type filter
        $response = $this->getJson('/api/v1/challenges?type=team');
        $response->assertStatus(200);
        $teamChallenges = $response->json('data');
        foreach ($teamChallenges as $challenge) {
            $this->assertEquals('team', $challenge['type']);
        }

        // 3. Test organization filter
        $response = $this->getJson("/api/v1/challenges?organization_id={$this->organization->id}");
        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data')); // Excluding another org challenge

        // 4. Test active status filter
        $response = $this->getJson('/api/v1/challenges?is_active=true');
        $response->assertStatus(200);
        $activeChallenges = $response->json('data');
        foreach ($activeChallenges as $challenge) {
            $this->assertTrue($challenge['is_active']);
        }

        // 5. Test temporal status filters
        $response = $this->getJson('/api/v1/challenges?status=current');
        $response->assertStatus(200);
        $currentChallenges = $response->json('data');
        foreach ($currentChallenges as $challenge) {
            $this->assertTrue($challenge['is_currently_active']);
        }

        $response = $this->getJson('/api/v1/challenges?status=upcoming');
        $response->assertStatus(200);
        $upcomingChallenges = $response->json('data');
        foreach ($upcomingChallenges as $challenge) {
            $this->assertFalse($challenge['has_started']);
        }

        $response = $this->getJson('/api/v1/challenges?status=past');
        $response->assertStatus(200);
        $pastChallenges = $response->json('data');
        foreach ($pastChallenges as $challenge) {
            $this->assertTrue($challenge['has_ended']);
        }
    }

    #[Test]
    public function pagination_works_consistently_across_endpoints()
    {
        Sanctum::actingAs($this->user);

        // 1. Create enough data for pagination
        Team::factory()->count(25)->create();
        Challenge::factory()->count(30)->create();

        // 2. Test team pagination
        $response = $this->getJson('/api/v1/teams?per_page=10&page=1');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(1, $response->json('meta.current_page'));
        $this->assertEquals(25, $response->json('meta.total'));

        $response = $this->getJson('/api/v1/teams?per_page=10&page=2');
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(2, $response->json('meta.current_page'));

        // 3. Test challenge pagination
        $response = $this->getJson('/api/v1/challenges?per_page=15&page=1');
        $response->assertStatus(200);
        $this->assertCount(15, $response->json('data'));
        $this->assertEquals(1, $response->json('meta.current_page'));
        $this->assertEquals(30, $response->json('meta.total'));

        // 4. Test pagination structure consistency
        $teamResponse = $this->getJson('/api/v1/teams?per_page=5');
        $challengeResponse = $this->getJson('/api/v1/challenges?per_page=5');

        $this->assertEquals(
            array_keys($teamResponse->json('meta')),
            array_keys($challengeResponse->json('meta'))
        );

        $this->assertEquals(
            array_keys($teamResponse->json('links')),
            array_keys($challengeResponse->json('links'))
        );
    }

    #[Test]
    public function user_permissions_are_respected_across_endpoints()
    {
        // 1. Create team owned by user
        $userTeam = Team::factory()->create([
            'created_by_user_id' => $this->user->id
        ]);

        // 2. Create team owned by another user
        $otherUserTeam = Team::factory()->create([
            'created_by_user_id' => $this->anotherUser->id
        ]);

        // 3. Test as team owner
        Sanctum::actingAs($this->user);
        
        $response = $this->putJson("/api/v1/teams/{$userTeam->id}", [
            'name' => 'Updated by Owner'
        ]);
        $response->assertStatus(200);

        $response = $this->deleteJson("/api/v1/teams/{$userTeam->id}");
        $response->assertStatus(204);

        // 4. Test as non-owner
        $response = $this->putJson("/api/v1/teams/{$otherUserTeam->id}", [
            'name' => 'Unauthorized Update'
        ]);
        $response->assertStatus(403);

        $response = $this->deleteJson("/api/v1/teams/{$otherUserTeam->id}");
        $response->assertStatus(403);

        // 5. Test unauthenticated access
        auth()->logout();
        
        $response = $this->getJson('/api/v1/teams');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/challenges');
        $response->assertStatus(401);
    }
}
