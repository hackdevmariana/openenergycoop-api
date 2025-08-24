<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\UserChallengeProgress;
use App\Models\EnergyChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class UserChallengeProgressTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    /** @test */
    public function it_can_create_user_challenge_progress()
    {
        $user = User::factory()->create();
        $challenge = EnergyChallenge::factory()->create();

        $progressData = [
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'progress_kwh' => 50.25,
            'completed_at' => null,
        ];

        $progress = UserChallengeProgress::create($progressData);

        $this->assertInstanceOf(UserChallengeProgress::class, $progress);
        $this->assertEquals($user->id, $progress->user_id);
        $this->assertEquals($challenge->id, $progress->challenge_id);
        $this->assertEquals(50.25, $progress->progress_kwh);
        $this->assertNull($progress->completed_at);
    }

    /** @test */
    public function it_has_required_fields()
    {
        $progress = UserChallengeProgress::factory()->create();

        $this->assertNotNull($progress->user_id);
        $this->assertNotNull($progress->challenge_id);
        $this->assertNotNull($progress->progress_kwh);
        $this->assertNotNull($progress->created_at);
        $this->assertNotNull($progress->updated_at);
    }

    /** @test */
    public function it_casts_progress_kwh_to_decimal()
    {
        $progress = UserChallengeProgress::factory()->create([
            'progress_kwh' => 75.50,
        ]);

        $this->assertIsFloat($progress->progress_kwh);
        $this->assertEquals(75.50, $progress->progress_kwh);
    }

    /** @test */
    public function it_can_get_progress_percentage()
    {
        $challenge = EnergyChallenge::factory()->create(['goal_kwh' => 100]);
        $progress = UserChallengeProgress::factory()->create([
            'challenge_id' => $challenge->id,
            'progress_kwh' => 75,
        ]);

        $this->assertEquals(75, $progress->progress_percentage);
    }

    /** @test */
    public function it_can_get_remaining_kwh()
    {
        $challenge = EnergyChallenge::factory()->create(['goal_kwh' => 100]);
        $progress = UserChallengeProgress::factory()->create([
            'challenge_id' => $challenge->id,
            'progress_kwh' => 75,
        ]);

        $this->assertEquals(25, $progress->remaining_kwh);
    }

    /** @test */
    public function it_can_get_days_remaining()
    {
        $challenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(5),
        ]);
        
        $progress = UserChallengeProgress::factory()->create([
            'challenge_id' => $challenge->id,
            'progress_kwh' => 50,
        ]);

        $this->assertEquals(5, $progress->days_remaining);
    }

    /** @test */
    public function it_can_get_estimated_completion_date()
    {
        $challenge = EnergyChallenge::factory()->create([
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(20),
        ]);
        
        $progress = UserChallengeProgress::factory()->create([
            'challenge_id' => $challenge->id,
            'progress_kwh' => 50,
        ]);

        $estimatedDate = $progress->estimated_completion_date;
        $this->assertNotNull($estimatedDate);
        $this->assertInstanceOf(Carbon::class, $estimatedDate);
    }

    /** @test */
    public function it_can_update_progress()
    {
        $challenge = EnergyChallenge::factory()->create(['goal_kwh' => 100]);
        $progress = UserChallengeProgress::factory()->create([
            'challenge_id' => $challenge->id,
            'progress_kwh' => 50,
        ]);

        $progress->updateProgress(25);

        $this->assertEquals(75, $progress->progress_kwh);
        $this->assertFalse($progress->isCompleted());
    }

    /** @test */
    public function it_can_complete_challenge()
    {
        $challenge = EnergyChallenge::factory()->create(['goal_kwh' => 100]);
        $progress = UserChallengeProgress::factory()->create([
            'challenge_id' => $challenge->id,
            'progress_kwh' => 100,
        ]);

        $progress->complete();

        $this->assertTrue($progress->isCompleted());
        $this->assertNotNull($progress->completed_at);
    }

    /** @test */
    public function it_can_reset_progress()
    {
        $progress = UserChallengeProgress::factory()->create([
            'progress_kwh' => 75,
            'completed_at' => now(),
        ]);

        $progress->reset();

        $this->assertEquals(0, $progress->progress_kwh);
        $this->assertNull($progress->completed_at);
        $this->assertFalse($progress->isCompleted());
    }

    /** @test */
    public function it_can_scope_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        UserChallengeProgress::factory()->create(['user_id' => $user1->id]);
        UserChallengeProgress::factory()->create(['user_id' => $user2->id]);

        $user1Progress = UserChallengeProgress::byUser($user1->id)->get();

        $this->assertEquals(1, $user1Progress->count());
        $this->assertEquals($user1->id, $user1Progress->first()->user_id);
    }

    /** @test */
    public function it_can_scope_by_challenge()
    {
        $challenge1 = EnergyChallenge::factory()->create();
        $challenge2 = EnergyChallenge::factory()->create();
        
        UserChallengeProgress::factory()->create(['challenge_id' => $challenge1->id]);
        UserChallengeProgress::factory()->create(['challenge_id' => $challenge2->id]);

        $challenge1Progress = UserChallengeProgress::byChallenge($challenge1->id)->get();

        $this->assertEquals(1, $challenge1Progress->count());
        $this->assertEquals($challenge1->id, $challenge1Progress->first()->challenge_id);
    }

    /** @test */
    public function it_can_scope_completed_progress()
    {
        UserChallengeProgress::factory()->completed()->create();
        UserChallengeProgress::factory()->inProgress()->create();

        $completedProgress = UserChallengeProgress::completed()->get();

        $this->assertEquals(1, $completedProgress->count());
        $this->assertTrue($completedProgress->first()->isCompleted());
    }

    /** @test */
    public function it_can_scope_in_progress()
    {
        UserChallengeProgress::factory()->completed()->create();
        UserChallengeProgress::factory()->inProgress()->create();

        $inProgress = UserChallengeProgress::inProgress()->get();

        $this->assertEquals(1, $inProgress->count());
        $this->assertFalse($inProgress->first()->isCompleted());
    }

    /** @test */
    public function it_can_scope_recent_progress()
    {
        UserChallengeProgress::factory()->create(['created_at' => now()->subDays(10)]);
        UserChallengeProgress::factory()->create(['created_at' => now()]);

        $recentProgress = UserChallengeProgress::recent(5)->get();

        $this->assertEquals(1, $recentProgress->count());
        $this->assertTrue($recentProgress->first()->created_at->isToday());
    }

    /** @test */
    public function it_can_search_progress()
    {
        $challenge = EnergyChallenge::factory()->create(['title' => 'Energy Saving Challenge']);
        UserChallengeProgress::factory()->create(['challenge_id' => $challenge->id]);

        $searchResults = UserChallengeProgress::search('energy')->get();

        $this->assertEquals(1, $searchResults->count());
        $this->assertEquals($challenge->id, $searchResults->first()->challenge_id);
    }

    /** @test */
    public function it_automatically_completes_when_goal_reached()
    {
        $challenge = EnergyChallenge::factory()->create(['goal_kwh' => 100]);
        $progress = UserChallengeProgress::factory()->create([
            'challenge_id' => $challenge->id,
            'progress_kwh' => 90,
        ]);

        $progress->updateProgress(10);

        $this->assertTrue($progress->isCompleted());
        $this->assertEquals(100, $progress->progress_kwh);
        $this->assertNotNull($progress->completed_at);
    }
}
