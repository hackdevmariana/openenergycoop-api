<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'type',
        'criteria',
        'points_reward',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'criteria' => 'array',
        'points_reward' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener los logros de usuarios que han desbloqueado este achievement
     */
    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Obtener los usuarios que han desbloqueado este achievement
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withPivot(['earned_at', 'custom_message', 'reward_granted'])
                    ->withTimestamps();
    }

    /**
     * Scope para obtener solo achievements activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para obtener achievements por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para ordenar por sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Verificar si un usuario ha desbloqueado este achievement
     */
    public function isUnlockedByUser(User $user): bool
    {
        return $this->userAchievements()
                    ->where('user_id', $user->id)
                    ->exists();
    }

    /**
     * Obtener el número total de usuarios que han desbloqueado este achievement
     */
    public function getTotalUnlocksAttribute(): int
    {
        return $this->userAchievements()->count();
    }
}
