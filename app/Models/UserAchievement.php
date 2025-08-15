<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_id',
        'earned_at',
        'custom_message',
        'reward_granted',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'reward_granted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el usuario que desbloqueó el logro
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el achievement desbloqueado
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    /**
     * Scope para logros con recompensa otorgada
     */
    public function scopeRewardGranted($query)
    {
        return $query->where('reward_granted', true);
    }

    /**
     * Scope para logros pendientes de recompensa
     */
    public function scopePendingReward($query)
    {
        return $query->where('reward_granted', false);
    }

    /**
     * Scope para logros por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para logros por achievement
     */
    public function scopeByAchievement($query, int $achievementId)
    {
        return $query->where('achievement_id', $achievementId);
    }

    /**
     * Scope para ordenar por fecha de obtención
     */
    public function scopeOrderedByEarnedDate($query, string $direction = 'desc')
    {
        return $query->orderBy('earned_at', $direction);
    }

    /**
     * Marcar la recompensa como otorgada
     */
    public function grantReward(): void
    {
        $this->update(['reward_granted' => true]);
    }

    /**
     * Crear un nuevo logro de usuario
     */
    public static function earnAchievement(
        int $userId,
        int $achievementId,
        ?string $customMessage = null
    ): self {
        // Verificar si ya existe
        $existing = static::where('user_id', $userId)
                          ->where('achievement_id', $achievementId)
                          ->first();

        if ($existing) {
            return $existing;
        }

        // Obtener el achievement para los puntos
        $achievement = Achievement::find($achievementId);
        
        // Crear el logro de usuario
        $userAchievement = static::create([
            'user_id' => $userId,
            'achievement_id' => $achievementId,
            'earned_at' => now(),
            'custom_message' => $customMessage,
            'reward_granted' => false,
        ]);

        // Agregar puntos al perfil del usuario si existe
        if ($achievement && $achievement->points_reward > 0) {
            $userProfile = UserProfile::where('user_id', $userId)->first();
            if ($userProfile) {
                $userProfile->addPoints($achievement->points_reward);
            }
        }

        return $userAchievement;
    }

    /**
     * Obtener logros recientes de un usuario
     */
    public static function getRecentByUser(int $userId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::byUser($userId)
                    ->with('achievement')
                    ->orderedByEarnedDate()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Obtener estadísticas de logros por usuario
     */
    public static function getUserStats(int $userId): array
    {
        $total = static::byUser($userId)->count();
        $withReward = static::byUser($userId)->rewardGranted()->count();
        $pendingReward = static::byUser($userId)->pendingReward()->count();

        return [
            'total_achievements' => $total,
            'rewards_granted' => $withReward,
            'pending_rewards' => $pendingReward,
        ];
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Evitar duplicados
        static::creating(function ($userAchievement) {
            $existing = static::where('user_id', $userAchievement->user_id)
                              ->where('achievement_id', $userAchievement->achievement_id)
                              ->exists();

            if ($existing) {
                return false; // Cancelar la creación
            }
        });
    }
}
