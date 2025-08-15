<?php

namespace App\Services;

use App\Models\UserProfile;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\Team;
use App\Models\Challenge;
use App\Models\TeamChallengeProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GamificationService
{
    /**
     * Cache duration for rankings (in minutes)
     */
    public const RANKING_CACHE_DURATION = 15;

    /**
     * Get municipality ranking
     */
    public function getMunicipalityRanking(?string $municipalityId = null, int $limit = 50): Collection
    {
        $cacheKey = "ranking.municipality.{$municipalityId}.{$limit}";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::RANKING_CACHE_DURATION), function () use ($municipalityId, $limit) {
            $query = UserProfile::with('user')
                ->where('show_in_rankings', true)
                ->when($municipalityId, function ($q) use ($municipalityId) {
                    return $q->where('municipality_id', $municipalityId);
                })
                ->orderBy('points_total', 'desc')
                ->orderBy('co2_avoided_total', 'desc')
                ->orderBy('kwh_produced_total', 'desc')
                ->limit($limit);

            return $query->get()->map(function ($profile, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $profile->user,
                    'profile' => $profile,
                    'points' => $profile->points_total,
                    'co2_avoided' => $profile->co2_avoided_total,
                    'kwh_produced' => $profile->kwh_produced_total,
                    'municipality' => $profile->municipality_id,
                ];
            });
        });
    }

    /**
     * Get organization ranking
     */
    public function getOrganizationRanking(int $organizationId, int $limit = 50): Collection
    {
        $cacheKey = "ranking.organization.{$organizationId}.{$limit}";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::RANKING_CACHE_DURATION), function () use ($organizationId, $limit) {
            return UserProfile::with('user')
                ->where('organization_id', $organizationId)
                ->where('show_in_rankings', true)
                ->orderBy('points_total', 'desc')
                ->orderBy('co2_avoided_total', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($profile, $index) {
                    return [
                        'rank' => $index + 1,
                        'user' => $profile->user,
                        'profile' => $profile,
                        'points' => $profile->points_total,
                        'co2_avoided' => $profile->co2_avoided_total,
                        'kwh_produced' => $profile->kwh_produced_total,
                    ];
                });
        });
    }

    /**
     * Get team ranking
     */
    public function getTeamRanking(?int $organizationId = null, int $limit = 20): Collection
    {
        $cacheKey = "ranking.teams.{$organizationId}.{$limit}";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::RANKING_CACHE_DURATION), function () use ($organizationId, $limit) {
            return Team::with(['activeMemberships.user.profile'])
                ->when($organizationId, function ($q) use ($organizationId) {
                    return $q->where('organization_id', $organizationId);
                })
                ->get()
                ->map(function ($team) {
                    $totalPoints = $team->activeMemberships->sum(function ($membership) {
                        return $membership->user->profile?->points_total ?? 0;
                    });
                    
                    $totalCo2 = $team->activeMemberships->sum(function ($membership) {
                        return $membership->user->profile?->co2_avoided_total ?? 0;
                    });
                    
                    $totalKwh = $team->activeMemberships->sum(function ($membership) {
                        return $membership->user->profile?->kwh_produced_total ?? 0;
                    });
                    
                    return [
                        'team' => $team,
                        'total_points' => $totalPoints,
                        'total_co2_avoided' => $totalCo2,
                        'total_kwh_produced' => $totalKwh,
                        'member_count' => $team->activeMemberships->count(),
                        'average_points' => $team->activeMemberships->count() > 0 
                            ? $totalPoints / $team->activeMemberships->count() 
                            : 0,
                    ];
                })
                ->sortByDesc('total_points')
                ->take($limit)
                ->values()
                ->map(function ($teamData, $index) {
                    $teamData['rank'] = $index + 1;
                    return $teamData;
                });
        });
    }

    /**
     * Get user's ranking position
     */
    public function getUserRankingPosition(int $userId, string $scope = 'global'): array
    {
        $profile = UserProfile::where('user_id', $userId)->first();
        
        if (!$profile) {
            return ['rank' => null, 'total' => 0, 'percentile' => 0];
        }

        $query = UserProfile::where('show_in_rankings', true);
        
        switch ($scope) {
            case 'municipality':
                if ($profile->municipality_id) {
                    $query->where('municipality_id', $profile->municipality_id);
                }
                break;
            case 'organization':
                if ($profile->organization_id) {
                    $query->where('organization_id', $profile->organization_id);
                }
                break;
        }

        $total = $query->count();
        $rank = $query->where('points_total', '>', $profile->points_total)->count() + 1;
        $percentile = $total > 0 ? round((($total - $rank) / $total) * 100, 1) : 0;

        return [
            'rank' => $rank,
            'total' => $total,
            'percentile' => $percentile,
        ];
    }

    /**
     * Award points to user
     */
    public function awardPoints(int $userId, int $points, string $reason = 'General'): bool
    {
        $profile = UserProfile::where('user_id', $userId)->first();
        
        if (!$profile) {
            return false;
        }

        $profile->increment('points_total', $points);
        
        // Clear relevant caches
        $this->clearUserRankingCache($profile);
        
        return true;
    }

    /**
     * Update energy metrics
     */
    public function updateEnergyMetrics(int $userId, float $kwhProduced = 0, float $co2Avoided = 0): bool
    {
        $profile = UserProfile::where('user_id', $userId)->first();
        
        if (!$profile) {
            return false;
        }

        $profile->increment('kwh_produced_total', $kwhProduced);
        $profile->increment('co2_avoided_total', $co2Avoided);
        
        // Award points based on energy production
        $energyPoints = $this->calculateEnergyPoints($kwhProduced, $co2Avoided);
        if ($energyPoints > 0) {
            $this->awardPoints($userId, $energyPoints, 'Energy Production');
        }
        
        return true;
    }

    /**
     * Calculate points based on energy metrics
     */
    private function calculateEnergyPoints(float $kwhProduced, float $co2Avoided): int
    {
        // 1 point per kWh produced
        $kwhPoints = floor($kwhProduced);
        
        // 2 points per kg of CO2 avoided
        $co2Points = floor($co2Avoided * 2);
        
        return $kwhPoints + $co2Points;
    }

    /**
     * Get organization statistics
     */
    public function getOrganizationStats(int $organizationId): array
    {
        $cacheKey = "stats.organization.{$organizationId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::RANKING_CACHE_DURATION), function () use ($organizationId) {
            $profiles = UserProfile::where('organization_id', $organizationId)->get();
            
            $totalUsers = $profiles->count();
            $totalPoints = $profiles->sum('points_total');
            $totalCo2 = $profiles->sum('co2_avoided_total');
            $totalKwh = $profiles->sum('kwh_produced_total');
            
            $avgPoints = $totalUsers > 0 ? $totalPoints / $totalUsers : 0;
            $avgCo2 = $totalUsers > 0 ? $totalCo2 / $totalUsers : 0;
            $avgKwh = $totalUsers > 0 ? $totalKwh / $totalUsers : 0;
            
            return [
                'total_users' => $totalUsers,
                'total_points' => $totalPoints,
                'total_co2_avoided' => $totalCo2,
                'total_kwh_produced' => $totalKwh,
                'average_points' => round($avgPoints, 1),
                'average_co2_avoided' => round($avgCo2, 2),
                'average_kwh_produced' => round($avgKwh, 2),
            ];
        });
    }

    /**
     * Get municipality statistics
     */
    public function getMunicipalityStats(string $municipalityId): array
    {
        $cacheKey = "stats.municipality.{$municipalityId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::RANKING_CACHE_DURATION), function () use ($municipalityId) {
            $profiles = UserProfile::where('municipality_id', $municipalityId)->get();
            
            $totalUsers = $profiles->count();
            $totalPoints = $profiles->sum('points_total');
            $totalCo2 = $profiles->sum('co2_avoided_total');
            $totalKwh = $profiles->sum('kwh_produced_total');
            
            return [
                'total_users' => $totalUsers,
                'total_points' => $totalPoints,
                'total_co2_avoided' => $totalCo2,
                'total_kwh_produced' => $totalKwh,
                'average_points' => $totalUsers > 0 ? round($totalPoints / $totalUsers, 1) : 0,
                'average_co2_avoided' => $totalUsers > 0 ? round($totalCo2 / $totalUsers, 2) : 0,
                'average_kwh_produced' => $totalUsers > 0 ? round($totalKwh / $totalUsers, 2) : 0,
            ];
        });
    }

    /**
     * Get user's comparison with organization average
     */
    public function getUserComparison(int $userId): array
    {
        $profile = UserProfile::where('user_id', $userId)->first();
        
        if (!$profile || !$profile->organization_id) {
            return [];
        }

        $orgStats = $this->getOrganizationStats($profile->organization_id);
        
        return [
            'points_vs_average' => [
                'user' => $profile->points_total,
                'average' => $orgStats['average_points'],
                'percentage' => $orgStats['average_points'] > 0 
                    ? round(($profile->points_total / $orgStats['average_points']) * 100, 1) 
                    : 0,
            ],
            'co2_vs_average' => [
                'user' => $profile->co2_avoided_total,
                'average' => $orgStats['average_co2_avoided'],
                'percentage' => $orgStats['average_co2_avoided'] > 0 
                    ? round(($profile->co2_avoided_total / $orgStats['average_co2_avoided']) * 100, 1) 
                    : 0,
            ],
            'kwh_vs_average' => [
                'user' => $profile->kwh_produced_total,
                'average' => $orgStats['average_kwh_produced'],
                'percentage' => $orgStats['average_kwh_produced'] > 0 
                    ? round(($profile->kwh_produced_total / $orgStats['average_kwh_produced']) * 100, 1) 
                    : 0,
            ],
        ];
    }

    /**
     * Clear user-related ranking caches
     */
    private function clearUserRankingCache(UserProfile $profile): void
    {
        // Clear municipality cache
        if ($profile->municipality_id) {
            Cache::forget("ranking.municipality.{$profile->municipality_id}.*");
            Cache::forget("stats.municipality.{$profile->municipality_id}");
        }
        
        // Clear organization cache
        if ($profile->organization_id) {
            Cache::forget("ranking.organization.{$profile->organization_id}.*");
            Cache::forget("stats.organization.{$profile->organization_id}");
        }
        
        // Clear team caches if user is in a team
        if ($profile->team_id) {
            Cache::forget("ranking.teams.*");
        }
    }

    /**
     * Clear all ranking caches
     */
    public function clearAllRankingCaches(): void
    {
        Cache::flush(); // In production, use more targeted cache clearing
    }
}
