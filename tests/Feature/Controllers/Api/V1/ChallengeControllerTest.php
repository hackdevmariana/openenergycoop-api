<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Challenge;
use App\Models\Organization;
use App\Models\Team;
use App\Models\TeamChallengeProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChallengeControllerTest extends TestCase
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
    public function it_can_list_challenges()
    {
        Sanctum::actingAs($this->user);
        
        Challenge::factory()->count(5)->create([
            'organization_id' => $this->organization->id
        ]);

        $response = $this->getJson('/api/v1/challenges');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'type',
                    'target_kwh',
                    'points_reward',
                    'start_date',
                    'end_date',
                    'is_active',
                    'criteria',
                    'icon',
                    'is_currently_active',
                    'has_started',
                    'has_ended',
                    'days_remaining',
                    'duration_in_days',
                    'created_at',
                    'updated_at',
                    'organization',
                    'meta'
                ]
            ],
            'links',
            'meta'
        ]);
    }

    #[Test]
    public function it_can_filter_challenges_by_type()
    {
        Sanctum::actingAs($this->user);
        
        Challenge::factory()->team()->count(3)->create();
        Challenge::factory()->individual()->count(2)->create();
        Challenge::factory()->organization()->count(1)->create();

        $response = $this->getJson('/api/v1/challenges?type=team');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $challenge) {
            $this->assertEquals('team', $challenge['type']);
        }
    }

    #[Test]
    public function it_can_filter_challenges_by_organization()
    {
        Sanctum::actingAs($this->user);
        
        $anotherOrg = Organization::factory()->create();
        
        Challenge::factory()->count(3)->create([
            'organization_id' => $this->organization->id
        ]);
        
        Challenge::factory()->count(2)->create([
            'organization_id' => $anotherOrg->id
        ]);

        $response = $this->getJson("/api/v1/challenges?organization_id={$this->organization->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $challenge) {
            $this->assertEquals($this->organization->id, $challenge['organization']['id']);
        }
    }

    #[Test]
    public function it_can_filter_challenges_by_active_status()
    {
        Sanctum::actingAs($this->user);
        
        Challenge::factory()->active()->count(4)->create();
        Challenge::factory()->inactive()->count(2)->create();

        $response = $this->getJson('/api/v1/challenges?is_active=true');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
        
        foreach ($response->json('data') as $challenge) {
            $this->assertTrue($challenge['is_active']);
        }
    }

    #[Test]
    public function it_can_filter_challenges_by_temporal_status()
    {
        Sanctum::actingAs($this->user);
        
        Challenge::factory()->current()->count(2)->create();
        Challenge::factory()->upcoming()->count(3)->create();
        Challenge::factory()->past()->count(1)->create();

        // Test current challenges
        $response = $this->getJson('/api/v1/challenges?status=current');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $challenge) {
            $this->assertTrue($challenge['is_currently_active']);
        }

        // Test upcoming challenges
        $response = $this->getJson('/api/v1/challenges?status=upcoming');
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $challenge) {
            $this->assertFalse($challenge['has_started']);
        }

        // Test past challenges
        $response = $this->getJson('/api/v1/challenges?status=past');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        
        foreach ($response->json('data') as $challenge) {
            $this->assertTrue($challenge['has_ended']);
        }
    }

    #[Test]
    public function it_can_show_challenge_with_detailed_info()
    {
        Sanctum::actingAs($this->user);
        
        $challenge = Challenge::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Challenge'
        ]);
        
        // Agregar algunos equipos participando
        $teams = Team::factory()->count(3)->create();
        foreach ($teams as $team) {
            TeamChallengeProgress::factory()->create([
                'team_id' => $team->id,
                'challenge_id' => $challenge->id
            ]);
        }

        $response = $this->getJson("/api/v1/challenges/{$challenge->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $challenge->id,
                'name' => 'Test Challenge'
            ]
        ]);
        
        $response->assertJsonStructure([
            'data' => [
                'top_teams',
                'detailed_stats'
            ]
        ]);
    }

    #[Test]
    public function it_can_get_challenge_leaderboard()
    {
        Sanctum::actingAs($this->user);
        
        $challenge = Challenge::factory()->create(['target_kwh' => 2000]);
        
        // Crear equipos con diferentes progresos
        $team1 = Team::factory()->create(['name' => 'Top Team']);
        $team2 = Team::factory()->create(['name' => 'Second Team']);
        $team3 = Team::factory()->create(['name' => 'Third Team']);
        
        TeamChallengeProgress::factory()->create([
            'team_id' => $team1->id,
            'challenge_id' => $challenge->id,
            'progress_kwh' => 1800
        ]);
        
        TeamChallengeProgress::factory()->create([
            'team_id' => $team2->id,
            'challenge_id' => $challenge->id,
            'progress_kwh' => 1500
        ]);
        
        TeamChallengeProgress::factory()->create([
            'team_id' => $team3->id,
            'challenge_id' => $challenge->id,
            'progress_kwh' => 1200
        ]);

        $response = $this->getJson("/api/v1/challenges/{$challenge->id}/leaderboard");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'rank',
                    'team_id',
                    'team_name',
                    'team_slug',
                    'team_logo',
                    'progress_kwh',
                    'progress_percentage',
                    'is_completed',
                    'completed_at',
                    'members_count'
                ]
            ]
        ]);
        
        $data = $response->json('data');
        
        // Verificar orden correcto (mayor progreso primero)
        $this->assertEquals('Top Team', $data[0]['team_name']);
        $this->assertEquals(1, $data[0]['rank']);
        $this->assertEquals(1800, $data[0]['progress_kwh']);
        
        $this->assertEquals('Second Team', $data[1]['team_name']);
        $this->assertEquals(2, $data[1]['rank']);
        $this->assertEquals(1500, $data[1]['progress_kwh']);
    }

    #[Test]
    public function it_can_limit_leaderboard_results()
    {
        Sanctum::actingAs($this->user);
        
        $challenge = Challenge::factory()->create();
        
        // Crear 10 equipos con progreso
        $teams = Team::factory()->count(10)->create();
        foreach ($teams as $team) {
            TeamChallengeProgress::factory()->create([
                'team_id' => $team->id,
                'challenge_id' => $challenge->id
            ]);
        }

        $response = $this->getJson("/api/v1/challenges/{$challenge->id}/leaderboard?limit=5");

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function it_can_get_current_challenges()
    {
        Sanctum::actingAs($this->user);
        
        Challenge::factory()->current()->active()->count(3)->create();
        Challenge::factory()->past()->count(2)->create();
        Challenge::factory()->upcoming()->count(1)->create();

        $response = $this->getJson('/api/v1/challenges/current');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $challenge) {
            $this->assertTrue($challenge['is_currently_active']);
            $this->assertTrue($challenge['is_active']);
        }
    }

    #[Test]
    public function it_can_filter_current_challenges_by_type()
    {
        Sanctum::actingAs($this->user);
        
        Challenge::factory()->current()->active()->team()->count(2)->create();
        Challenge::factory()->current()->active()->individual()->count(1)->create();

        $response = $this->getJson('/api/v1/challenges/current?type=team');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $challenge) {
            $this->assertEquals('team', $challenge['type']);
        }
    }

    #[Test]
    public function it_can_get_challenge_recommendations_for_team()
    {
        Sanctum::actingAs($this->user);
        
        $team = Team::factory()->create(['organization_id' => $this->organization->id]);
        
        // Desafíos que el equipo puede participar
        Challenge::factory()->current()->active()->team()->count(3)->create([
            'organization_id' => $this->organization->id
        ]);
        
        // Desafío global
        Challenge::factory()->current()->active()->team()->count(1)->create([
            'organization_id' => null
        ]);
        
        // Desafío de otra organización (no debe aparecer)
        $anotherOrg = Organization::factory()->create();
        Challenge::factory()->current()->active()->team()->count(1)->create([
            'organization_id' => $anotherOrg->id
        ]);
        
        // Desafío donde el equipo ya participa
        $existingChallenge = Challenge::factory()->current()->active()->team()->create([
            'organization_id' => $this->organization->id
        ]);
        TeamChallengeProgress::factory()->create([
            'team_id' => $team->id,
            'challenge_id' => $existingChallenge->id
        ]);

        $response = $this->getJson("/api/v1/challenges/recommendations/{$team->id}");

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data')); // 3 de la org + 1 global
        
        // No debe incluir el desafío donde ya participa
        $challengeIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($existingChallenge->id, $challengeIds);
    }

    #[Test]
    public function it_can_get_challenge_statistics()
    {
        Sanctum::actingAs($this->user);
        
        // Crear desafíos con diferentes estados
        Challenge::factory()->active()->count(5)->create();
        Challenge::factory()->inactive()->count(2)->create();
        Challenge::factory()->current()->count(3)->create();
        
        // Crear progreso para algunos desafíos
        $challenge = Challenge::factory()->create(['target_kwh' => 1000]);
        $teams = Team::factory()->count(3)->create();
        
        TeamChallengeProgress::factory()->completed()->create([
            'team_id' => $teams[0]->id,
            'challenge_id' => $challenge->id,
            'progress_kwh' => 1200
        ]);
        
        TeamChallengeProgress::factory()->inProgress()->create([
            'team_id' => $teams[1]->id,
            'challenge_id' => $challenge->id,
            'progress_kwh' => 500
        ]);

        $response = $this->getJson('/api/v1/challenges/statistics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_challenges',
                'active_challenges',
                'current_challenges',
                'completed_challenges',
                'total_participants',
                'total_kwh_target',
                'total_kwh_achieved',
                'completion_rate',
                'by_type' => [
                    'individual',
                    'team',
                    'organization'
                ]
            ]
        ]);
        
        $data = $response->json('data');
        $this->assertIsInt($data['total_challenges']);
        $this->assertIsInt($data['active_challenges']);
        $this->assertIsNumeric($data['completion_rate']);
        $this->assertGreaterThan(0, $data['total_challenges']);
    }

    #[Test]
    public function it_calculates_statistics_correctly()
    {
        Sanctum::actingAs($this->user);
        
        // Crear desafíos específicos para verificar cálculos
        Challenge::factory()->team()->count(2)->create();
        Challenge::factory()->individual()->count(1)->create();
        Challenge::factory()->organization()->count(1)->create();

        $response = $this->getJson('/api/v1/challenges/statistics');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertEquals(4, $data['total_challenges']);
        $this->assertEquals(2, $data['by_type']['team']);
        $this->assertEquals(1, $data['by_type']['individual']);
        $this->assertEquals(1, $data['by_type']['organization']);
    }

    #[Test]
    public function it_requires_authentication_for_all_endpoints()
    {
        $challenge = Challenge::factory()->create();
        $team = Team::factory()->create();
        
        $endpoints = [
            'GET' => [
                '/api/v1/challenges',
                "/api/v1/challenges/{$challenge->id}",
                "/api/v1/challenges/{$challenge->id}/leaderboard",
                '/api/v1/challenges/current',
                "/api/v1/challenges/recommendations/{$team->id}",
                '/api/v1/challenges/statistics'
            ]
        ];

        foreach ($endpoints as $method => $urls) {
            foreach ($urls as $url) {
                $response = $this->getJson($url);
                $response->assertStatus(401, "Endpoint {$method} {$url} should require authentication");
            }
        }
    }

    #[Test]
    public function it_handles_pagination_correctly()
    {
        Sanctum::actingAs($this->user);
        
        Challenge::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/challenges?per_page=10');

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
    public function it_returns_404_for_non_existent_challenge()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/challenges/999');
        $response->assertStatus(404);
        
        $response = $this->getJson('/api/v1/challenges/999/leaderboard');
        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_for_non_existent_team_in_recommendations()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/challenges/recommendations/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_orders_challenges_by_start_date()
    {
        Sanctum::actingAs($this->user);
        
        $oldChallenge = Challenge::factory()->create([
            'name' => 'Old Challenge',
            'start_date' => now()->subMonths(2)
        ]);
        
        $newChallenge = Challenge::factory()->create([
            'name' => 'New Challenge',
            'start_date' => now()->subDays(1)
        ]);

        $response = $this->getJson('/api/v1/challenges');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // El más reciente debe aparecer primero (orden descendente por start_date)
        $this->assertEquals('New Challenge', $data[0]['name']);
        $this->assertEquals('Old Challenge', $data[1]['name']);
    }

    #[Test]
    public function it_includes_participation_counts_in_listing()
    {
        Sanctum::actingAs($this->user);
        
        $challenge = Challenge::factory()->create();
        
        // Agregar equipos participando
        $teams = Team::factory()->count(5)->create();
        foreach ($teams->take(3) as $team) {
            TeamChallengeProgress::factory()->create([
                'team_id' => $team->id,
                'challenge_id' => $challenge->id
            ]);
        }
        
        // Marcar algunos como completados
        TeamChallengeProgress::factory()->completed()->create([
            'team_id' => $teams[3]->id,
            'challenge_id' => $challenge->id
        ]);

        $response = $this->getJson('/api/v1/challenges');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $challengeData = collect($data)->firstWhere('id', $challenge->id);
        $this->assertEquals(4, $challengeData['participating_teams_count']);
        $this->assertEquals(1, $challengeData['completed_teams_count']);
        $this->assertEquals(25.0, $challengeData['completion_rate']); // 1/4 * 100
    }

    #[Test]
    public function it_limits_current_challenges_results()
    {
        Sanctum::actingAs($this->user);
        
        Challenge::factory()->current()->active()->count(15)->create();

        $response = $this->getJson('/api/v1/challenges/current?limit=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function it_orders_current_challenges_by_end_date()
    {
        Sanctum::actingAs($this->user);
        
        $endingSoon = Challenge::factory()->current()->active()->create([
            'name' => 'Ending Soon',
            'end_date' => now()->addDays(2)
        ]);
        
        $endingLater = Challenge::factory()->current()->active()->create([
            'name' => 'Ending Later',
            'end_date' => now()->addDays(10)
        ]);

        $response = $this->getJson('/api/v1/challenges/current');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // El que termina más pronto debe aparecer primero
        $this->assertEquals('Ending Soon', $data[0]['name']);
        $this->assertEquals('Ending Later', $data[1]['name']);
    }

    #[Test]
    public function it_includes_team_counts_in_leaderboard()
    {
        Sanctum::actingAs($this->user);
        
        $challenge = Challenge::factory()->create();
        $team = Team::factory()->create();
        
        // Agregar miembros al equipo
        \App\Models\TeamMembership::factory()->count(5)->create(['team_id' => $team->id]);
        
        TeamChallengeProgress::factory()->create([
            'team_id' => $team->id,
            'challenge_id' => $challenge->id
        ]);

        $response = $this->getJson("/api/v1/challenges/{$challenge->id}/leaderboard");

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertEquals(5, $data[0]['members_count']);
    }
}
