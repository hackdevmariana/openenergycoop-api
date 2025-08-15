<?php

namespace Tests\Traits;

use App\Models\Achievement;
use App\Models\InvitationToken;
use App\Models\Organization;
use App\Models\OrganizationRole;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserProfile;

trait GamificationTestHelpers
{
    /**
     * Create a complete gamification setup with user, profile, and achievements
     */
    protected function createGamificationSetup(): array
    {
        $organization = Organization::factory()->create();
        $role = OrganizationRole::factory()->create(['organization_id' => $organization->id]);
        
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $profile = UserProfile::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'points_total' => 1000
        ]);
        
        $achievements = Achievement::factory()->count(5)->create();
        
        return [
            'user' => $user,
            'organization' => $organization,
            'role' => $role,
            'profile' => $profile,
            'achievements' => $achievements
        ];
    }

    /**
     * Create achievements of different types
     */
    protected function createAchievementsByType(): array
    {
        return [
            'energy' => Achievement::factory()->energy()->count(3)->create(),
            'participation' => Achievement::factory()->participation()->count(2)->create(),
            'community' => Achievement::factory()->community()->count(2)->create(),
            'milestone' => Achievement::factory()->milestone()->count(1)->create(),
        ];
    }

    /**
     * Create user achievements with different reward statuses
     */
    protected function createUserAchievementsWithRewards(User $user, Achievement $achievement): array
    {
        return [
            'granted' => UserAchievement::factory()->rewardGranted()->count(3)->create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id
            ]),
            'pending' => UserAchievement::factory()->pendingReward()->count(2)->create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id
            ])
        ];
    }

    /**
     * Create invitation tokens with different statuses
     */
    protected function createInvitationTokensWithStatuses(Organization $organization, OrganizationRole $role, User $inviter): array
    {
        return [
            'pending' => InvitationToken::factory()->pending()->count(2)->create([
                'organization_id' => $organization->id,
                'organization_role_id' => $role->id,
                'invited_by' => $inviter->id
            ]),
            'used' => InvitationToken::factory()->used()->count(1)->create([
                'organization_id' => $organization->id,
                'organization_role_id' => $role->id,
                'invited_by' => $inviter->id
            ]),
            'expired' => InvitationToken::factory()->expired()->count(1)->create([
                'organization_id' => $organization->id,
                'organization_role_id' => $role->id,
                'invited_by' => $inviter->id
            ]),
            'revoked' => InvitationToken::factory()->revoked()->count(1)->create([
                'organization_id' => $organization->id,
                'organization_role_id' => $role->id,
                'invited_by' => $inviter->id
            ])
        ];
    }

    /**
     * Create user profiles for ranking tests
     */
    protected function createRankingProfiles(Organization $organization): array
    {
        return [
            'top' => UserProfile::factory()->create([
                'organization_id' => $organization->id,
                'points_total' => 5000,
                'show_in_rankings' => true
            ]),
            'middle' => UserProfile::factory()->create([
                'organization_id' => $organization->id,
                'points_total' => 2500,
                'show_in_rankings' => true
            ]),
            'bottom' => UserProfile::factory()->create([
                'organization_id' => $organization->id,
                'points_total' => 1000,
                'show_in_rankings' => true
            ]),
            'hidden' => UserProfile::factory()->create([
                'organization_id' => $organization->id,
                'points_total' => 3000,
                'show_in_rankings' => false // No aparece en rankings
            ])
        ];
    }

    /**
     * Assert that a collection is ordered by a specific field
     */
    protected function assertOrderedBy(array $collection, string $field, string $direction = 'desc'): void
    {
        $values = array_column($collection, $field);
        $sorted = $direction === 'desc' 
            ? array_reverse(sort($values)) 
            : sort($values);
            
        $this->assertEquals($sorted, $values, "Collection is not properly ordered by {$field} {$direction}");
    }

    /**
     * Assert that response contains pagination structure
     */
    protected function assertHasPaginationStructure($response): void
    {
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

    /**
     * Assert that all items in collection have a specific field value
     */
    protected function assertAllItemsHave(array $collection, string $field, $expectedValue): void
    {
        foreach ($collection as $item) {
            $this->assertEquals(
                $expectedValue, 
                $item[$field], 
                "Item does not have expected value for field {$field}"
            );
        }
    }

    /**
     * Create a user with specific gamification stats
     */
    protected function createUserWithStats(array $stats = []): array
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        
        $defaultStats = [
            'points_total' => 1000,
            'kwh_produced_total' => 500.0,
            'co2_avoided_total' => 250.0,
            'show_in_rankings' => true,
            'profile_completed' => true
        ];
        
        $profileStats = array_merge($defaultStats, $stats);
        $profileStats['user_id'] = $user->id;
        $profileStats['organization_id'] = $organization->id;
        
        $profile = UserProfile::factory()->create($profileStats);
        
        return [
            'user' => $user,
            'organization' => $organization,
            'profile' => $profile
        ];
    }

    /**
     * Create achievements with user unlocks for leaderboard testing
     */
    protected function createAchievementLeaderboardData(): array
    {
        $achievements = [
            'popular' => Achievement::factory()->create(['name' => 'Popular Achievement']),
            'moderate' => Achievement::factory()->create(['name' => 'Moderate Achievement']),
            'rare' => Achievement::factory()->create(['name' => 'Rare Achievement'])
        ];
        
        $users = User::factory()->count(10)->create();
        
        // Popular: 8 users have it
        foreach ($users->take(8) as $user) {
            UserAchievement::factory()->create([
                'user_id' => $user->id,
                'achievement_id' => $achievements['popular']->id
            ]);
        }
        
        // Moderate: 4 users have it
        foreach ($users->take(4) as $user) {
            UserAchievement::factory()->create([
                'user_id' => $user->id,
                'achievement_id' => $achievements['moderate']->id
            ]);
        }
        
        // Rare: 1 user has it
        UserAchievement::factory()->create([
            'user_id' => $users->first()->id,
            'achievement_id' => $achievements['rare']->id
        ]);
        
        return [
            'achievements' => $achievements,
            'users' => $users
        ];
    }

    /**
     * Assert achievement leaderboard order
     */
    protected function assertAchievementLeaderboardOrder(array $data): void
    {
        $unlockCounts = array_column($data, 'unlocks_count');
        
        for ($i = 0; $i < count($unlockCounts) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $unlockCounts[$i + 1], 
                $unlockCounts[$i],
                'Achievement leaderboard is not properly ordered by unlock count'
            );
        }
    }

    /**
     * Assert user leaderboard order
     */
    protected function assertUserLeaderboardOrder(array $data): void
    {
        $achievementCounts = array_column($data, 'achievements_count');
        
        for ($i = 0; $i < count($achievementCounts) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $achievementCounts[$i + 1], 
                $achievementCounts[$i],
                'User leaderboard is not properly ordered by achievement count'
            );
        }
    }
}
