<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'avatar',
        'bio',
        'municipality_id',
        'join_date',
        'role_in_cooperative',
        'profile_completed',
        'newsletter_opt_in',
        'show_in_rankings',
        'co2_avoided_total',
        'kwh_produced_total',
        'points_total',
        'badges_earned',
        'birth_date',
        'organization_id',
        'team_id',
    ];

    protected $casts = [
        'join_date' => 'date',
        'birth_date' => 'date',
        'profile_completed' => 'boolean',
        'newsletter_opt_in' => 'boolean',
        'show_in_rankings' => 'boolean',
        'co2_avoided_total' => 'decimal:2',
        'kwh_produced_total' => 'decimal:2',
        'points_total' => 'integer',
        'badges_earned' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el usuario al que pertenece este perfil
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la organización del perfil
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope para perfiles completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('profile_completed', true);
    }

    /**
     * Scope para perfiles que aparecen en rankings
     */
    public function scopeInRankings($query)
    {
        return $query->where('show_in_rankings', true);
    }

    /**
     * Scope para perfiles por organización
     */
    public function scopeByOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope para perfiles por municipio
     */
    public function scopeByMunicipality($query, string $municipalityId)
    {
        return $query->where('municipality_id', $municipalityId);
    }

    /**
     * Scope para ordenar por puntos (ranking)
     */
    public function scopeOrderedByPoints($query)
    {
        return $query->orderBy('points_total', 'desc');
    }

    /**
     * Verificar si el perfil está completo
     */
    public function isComplete(): bool
    {
        $requiredFields = [
            'bio',
            'municipality_id',
            'join_date',
            'birth_date',
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calcular y actualizar el estado de completitud del perfil
     */
    public function updateCompletionStatus(): void
    {
        $this->update(['profile_completed' => $this->isComplete()]);
    }

    /**
     * Agregar puntos al perfil
     */
    public function addPoints(int $points): void
    {
        $this->increment('points_total', $points);
    }

    /**
     * Agregar badge al perfil
     */
    public function addBadge(string $badge): void
    {
        $badges = $this->badges_earned ?? [];
        if (!in_array($badge, $badges)) {
            $badges[] = $badge;
            $this->update(['badges_earned' => $badges]);
        }
    }

    /**
     * Verificar si el usuario tiene un badge específico
     */
    public function hasBadge(string $badge): bool
    {
        $badges = $this->badges_earned ?? [];
        return in_array($badge, $badges);
    }

    /**
     * Obtener la edad del usuario
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    /**
     * Obtener el ranking del usuario en su organización
     */
    public function getOrganizationRankAttribute(): int
    {
        return static::byOrganization($this->organization_id)
                    ->inRankings()
                    ->where('points_total', '>', $this->points_total)
                    ->count() + 1;
    }

    /**
     * Obtener el ranking del usuario en su municipio
     */
    public function getMunicipalityRankAttribute(): int
    {
        if (!$this->municipality_id) {
            return 0;
        }

        return static::byMunicipality($this->municipality_id)
                    ->inRankings()
                    ->where('points_total', '>', $this->points_total)
                    ->count() + 1;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Actualizar estado de completitud al crear
        static::created(function ($profile) {
            $profile->updateCompletionStatus();
        });

        // Actualizar estado de completitud al actualizar
        static::updated(function ($profile) {
            if (!$profile->wasChanged('profile_completed')) {
                $profile->updateCompletionStatus();
            }
        });
    }
}
