<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'created_by_user_id',
        'organization_id',
        'is_open',
        'max_members',
        'logo_path',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'max_members' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el usuario que creó el equipo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Obtener la organización del equipo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener las membresías del equipo
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(TeamMembership::class);
    }

    /**
     * Obtener las membresías activas del equipo
     */
    public function activeMemberships(): HasMany
    {
        return $this->hasMany(TeamMembership::class)->whereNull('left_at');
    }

    /**
     * Obtener los miembros del equipo
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_memberships')
                    ->withPivot(['joined_at', 'role', 'left_at'])
                    ->whereNull('team_memberships.left_at')
                    ->withTimestamps();
    }

    /**
     * Obtener todos los miembros (incluyendo los que se fueron)
     */
    public function allMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_memberships')
                    ->withPivot(['joined_at', 'role', 'left_at'])
                    ->withTimestamps();
    }

    /**
     * Obtener los administradores del equipo
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_memberships')
                    ->withPivot(['joined_at', 'role', 'left_at'])
                    ->wherePivot('role', 'admin')
                    ->whereNull('team_memberships.left_at')
                    ->withTimestamps();
    }

    /**
     * Obtener el progreso en desafíos del equipo
     */
    public function challengeProgress(): HasMany
    {
        return $this->hasMany(TeamChallengeProgress::class);
    }

    /**
     * Obtener los desafíos del equipo
     */
    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany(Challenge::class, 'team_challenge_progress')
                    ->withPivot(['progress_kwh', 'completed_at'])
                    ->withTimestamps();
    }

    /**
     * Scope para equipos abiertos
     */
    public function scopeOpen($query)
    {
        return $query->where('is_open', true);
    }

    /**
     * Scope para equipos cerrados
     */
    public function scopeClosed($query)
    {
        return $query->where('is_open', false);
    }

    /**
     * Scope para equipos por organización
     */
    public function scopeByOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope para equipos con espacio disponible
     */
    public function scopeWithSpace($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('max_members')
              ->orWhereColumn('max_members', '>', function ($subQuery) {
                  $subQuery->selectRaw('COUNT(*)')
                           ->from('team_memberships')
                           ->whereColumn('team_id', 'teams.id')
                           ->whereNull('left_at');
              });
        });
    }

    /**
     * Verificar si el equipo está lleno
     */
    public function isFull(): bool
    {
        if (!$this->max_members) {
            return false;
        }

        return $this->activeMemberships()->count() >= $this->max_members;
    }

    /**
     * Verificar si un usuario es miembro del equipo
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('users.id', $user->id)->exists();
    }

    /**
     * Verificar si un usuario es administrador del equipo
     */
    public function isAdmin(User $user): bool
    {
        return $this->activeMemberships()
                    ->where('user_id', $user->id)
                    ->where('role', 'admin')
                    ->exists();
    }

    /**
     * Verificar si un usuario puede unirse al equipo
     */
    public function canJoin(User $user): bool
    {
        // No puede unirse si ya es miembro
        if ($this->hasMember($user)) {
            return false;
        }

        // No puede unirse si el equipo está lleno
        if ($this->isFull()) {
            return false;
        }

        // Si el equipo es abierto, puede unirse
        if ($this->is_open) {
            return true;
        }

        // Si el equipo es cerrado, solo puede unirse por invitación
        return false;
    }

    /**
     * Agregar un miembro al equipo
     */
    public function addMember(User $user, string $role = 'member'): TeamMembership
    {
        return TeamMembership::create([
            'team_id' => $this->id,
            'user_id' => $user->id,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remover un miembro del equipo
     */
    public function removeMember(User $user): bool
    {
        $membership = $this->activeMemberships()
                           ->where('user_id', $user->id)
                           ->first();

        if ($membership) {
            $membership->update(['left_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Obtener el número de miembros activos
     */
    public function getMembersCountAttribute(): int
    {
        return $this->activeMemberships()->count();
    }

    /**
     * Obtener espacios disponibles
     */
    public function getAvailableSlotsAttribute(): ?int
    {
        if (!$this->max_members) {
            return null;
        }

        return max(0, $this->max_members - $this->members_count);
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar slug automáticamente
        static::creating(function ($team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
                
                // Asegurar que el slug sea único dentro de la organización
                $originalSlug = $team->slug;
                $counter = 1;
                
                while (static::where('organization_id', $team->organization_id)
                            ->where('slug', $team->slug)
                            ->exists()) {
                    $team->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        // Agregar al creador como admin automáticamente
        static::created(function ($team) {
            $team->addMember($team->createdBy, 'admin');
        });
    }

    /**
     * Buscar equipo por slug dentro de una organización
     */
    public static function findBySlug(string $slug, ?int $organizationId = null): ?self
    {
        $query = static::where('slug', $slug);
        
        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }
        
        return $query->first();
    }

    /**
     * Obtener equipos recomendados para un usuario
     */
    public static function getRecommendedForUser(User $user, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        // Buscar equipos abiertos con espacio disponible
        // que no sean del usuario y donde el usuario no sea miembro
        return static::open()
                    ->withSpace()
                    ->where('created_by_user_id', '!=', $user->id)
                    ->whereDoesntHave('members', function ($query) use ($user) {
                        $query->where('users.id', $user->id);
                    })
                    ->withCount('activeMemberships')
                    ->orderBy('active_memberships_count', 'desc')
                    ->limit($limit)
                    ->get();
    }
}
