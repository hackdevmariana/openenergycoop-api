<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\EnergyChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class EnergyChallengeTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    /** @test */
    public function it_can_create_an_energy_challenge()
    {
        $challengeData = [
            'title' => 'Test Challenge',
            'description' => 'This is a test challenge',
            'type' => 'individual',
            'goal_kwh' => 100.50,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'reward_type' => 'badge',
            'reward_details' => ['points' => 500, 'description' => 'Test badge'],
            'is_active' => true,
        ];

        $challenge = EnergyChallenge::create($challengeData);

        $this->assertInstanceOf(EnergyChallenge::class, $challenge);
        $this->assertEquals('Test Challenge', $challenge->title);
        $this->assertEquals('individual', $challenge->type);
        $this->assertEquals(100.50, $challenge->goal_kwh);
        $this->assertEquals('badge', $challenge->reward_type);
        $this->assertTrue($challenge->is_active);
    }

    /** @test */
    public function it_has_required_fields()
    {
        $challenge = EnergyChallenge::factory()->create();

        $this->assertNotNull($challenge->title);
        $this->assertNotNull($challenge->description);
        $this->assertNotNull($challenge->type);
        $this->assertNotNull($challenge->goal_kwh);
        $this->assertNotNull($challenge->starts_at);
        $this->assertNotNull($challenge->ends_at);
        $this->assertNotNull($challenge->reward_type);
        $this->assertNotNull($challenge->is_active);
    }

    /** @test */
    public function it_has_soft_deletes()
    {
        $challenge = EnergyChallenge::factory()->create();
        $challengeId = $challenge->id;

        $challenge->delete();

        $this->assertSoftDeleted('energy_challenges', ['id' => $challengeId]);
        $this->assertDatabaseHas('energy_challenges', ['id' => $challengeId]);
    }

    /** @test */
    public function it_can_determine_if_active()
    {
        $activeChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $inactiveChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'is_active' => false,
        ]);

        $this->assertTrue($activeChallenge->isActive());
        $this->assertFalse($inactiveChallenge->isActive());
    }

    /** @test */
    public function it_can_determine_if_started()
    {
        $startedChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $notStartedChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $this->assertTrue($startedChallenge->hasStarted());
        $this->assertFalse($notStartedChallenge->hasStarted());
    }

    /** @test */
    public function it_can_determine_if_ended()
    {
        $endedChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $notEndedChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $this->assertTrue($endedChallenge->hasEnded());
        $this->assertFalse($notEndedChallenge->hasEnded());
    }

    /** @test */
    public function it_can_get_status_attribute()
    {
        $activeChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $upcomingChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $completedChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $this->assertEquals('active', $activeChallenge->status);
        $this->assertEquals('draft', $upcomingChallenge->status);
        $this->assertEquals('completed', $completedChallenge->status);
    }

    /** @test */
    public function it_can_get_status_label_attribute()
    {
        $challenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $this->assertEquals('Activo', $challenge->status_label);
    }

    /** @test */
    public function it_can_get_duration_attribute()
    {
        $challenge = EnergyChallenge::factory()->create([
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $this->assertEquals('30 días', $challenge->duration);
    }

    /** @test */
    public function it_can_get_time_until_start_attribute()
    {
        $futureChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(10),
        ]);

        $pastChallenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(5),
        ]);

        $this->assertStringContainsString('Comienza', $futureChallenge->time_until_start);
        $this->assertEquals('Ya comenzó', $pastChallenge->time_until_start);
    }

    /** @test */
    public function it_can_get_time_until_end_attribute()
    {
        $challenge = EnergyChallenge::factory()->create([
            'starts_at' => now(),
            'ends_at' => now()->addDays(10),
        ]);

        $this->assertStringContainsString('Termina', $challenge->time_until_end);
    }

    /** @test */
    public function it_can_scope_active_challenges()
    {
        EnergyChallenge::factory()->create(['is_active' => true]);
        EnergyChallenge::factory()->create(['is_active' => false]);

        $activeChallenges = EnergyChallenge::active()->get();

        $this->assertEquals(1, $activeChallenges->count());
        $this->assertTrue($activeChallenges->first()->is_active);
    }

    /** @test */
    public function it_can_scope_individual_challenges()
    {
        EnergyChallenge::factory()->create(['type' => 'individual']);
        EnergyChallenge::factory()->create(['type' => 'colectivo']);

        $individualChallenges = EnergyChallenge::individual()->get();

        $this->assertEquals(1, $individualChallenges->count());
        $this->assertEquals('individual', $individualChallenges->first()->type);
    }

    /** @test */
    public function it_can_scope_colectivo_challenges()
    {
        EnergyChallenge::factory()->create(['type' => 'individual']);
        EnergyChallenge::factory()->create(['type' => 'colectivo']);

        $colectivoChallenges = EnergyChallenge::colectivo()->get();

        $this->assertEquals(1, $colectivoChallenges->count());
        $this->assertEquals('colectivo', $colectivoChallenges->first()->type);
    }

    /** @test */
    public function it_can_scope_current_challenges()
    {
        EnergyChallenge::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        EnergyChallenge::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $currentChallenges = EnergyChallenge::current()->get();

        $this->assertEquals(1, $currentChallenges->count());
        $this->assertTrue($currentChallenges->first()->isActive());
    }

    /** @test */
    public function it_can_scope_upcoming_challenges()
    {
        EnergyChallenge::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        EnergyChallenge::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $upcomingChallenges = EnergyChallenge::upcoming()->get();

        $this->assertEquals(1, $upcomingChallenges->count());
        $this->assertEquals('draft', $upcomingChallenges->first()->status);
    }

    /** @test */
    public function it_can_scope_past_challenges()
    {
        EnergyChallenge::factory()->create([
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        EnergyChallenge::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $pastChallenges = EnergyChallenge::past()->get();

        $this->assertEquals(1, $pastChallenges->count());
        $this->assertEquals('completed', $pastChallenges->first()->status);
    }

    /** @test */
    public function it_can_scope_by_reward_type()
    {
        EnergyChallenge::factory()->create(['reward_type' => 'badge']);
        EnergyChallenge::factory()->create(['reward_type' => 'symbolic']);

        $badgeChallenges = EnergyChallenge::byRewardType('badge')->get();

        $this->assertEquals(1, $badgeChallenges->count());
        $this->assertEquals('badge', $badgeChallenges->first()->reward_type);
    }

    /** @test */
    public function it_can_search_challenges()
    {
        EnergyChallenge::factory()->create(['title' => 'Energy Saving Challenge']);
        EnergyChallenge::factory()->create(['title' => 'Solar Panel Challenge']);
        EnergyChallenge::factory()->create(['description' => 'Reduce your carbon footprint']);

        $searchResults = EnergyChallenge::search('energy')->get();

        $this->assertEquals(2, $searchResults->count());
    }

    /** @test */
    public function it_validates_ends_at_after_starts_at()
    {
        $this->expectException(\InvalidArgumentException::class);

        EnergyChallenge::create([
            'title' => 'Invalid Challenge',
            'description' => 'This challenge has invalid dates',
            'type' => 'individual',
            'goal_kwh' => 100,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->subDay(), // ends_at before starts_at
            'reward_type' => 'symbolic',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_get_progress_percentage_for_collective_challenges()
    {
        $challenge = EnergyChallenge::factory()->collective()->create([
            'goal_kwh' => 1000,
        ]);

        // Simular progreso de usuarios
        $challenge->userProgress()->createMany([
            ['user_id' => 1, 'progress_kwh' => 300],
            ['user_id' => 2, 'progress_kwh' => 400],
        ]);

        $this->assertEquals(70, $challenge->progress_percentage);
    }

    /** @test */
    public function it_can_get_total_participants()
    {
        $challenge = EnergyChallenge::factory()->create();

        $challenge->userProgress()->createMany([
            ['user_id' => 1, 'progress_kwh' => 50],
            ['user_id' => 2, 'progress_kwh' => 75],
            ['user_id' => 3, 'progress_kwh' => 25],
        ]);

        $this->assertEquals(3, $challenge->total_participants);
    }

    /** @test */
    public function it_can_get_average_progress()
    {
        $challenge = EnergyChallenge::factory()->create();

        $challenge->userProgress()->createMany([
            ['user_id' => 1, 'progress_kwh' => 100],
            ['user_id' => 2, 'progress_kwh' => 200],
            ['user_id' => 3, 'progress_kwh' => 300],
        ]);

        $this->assertEquals(200, $challenge->average_progress);
    }
}
