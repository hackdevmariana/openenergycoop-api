<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'joined_at',
        'role',
        'left_at',
    ];

    protected $appends = [
        'is_active'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el equipo de la membresía
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Obtener el usuario de la membresía
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para membresías activas
     */
    public function scopeActive($query)
    {
        return $query->whereNull('left_at');
    }

    /**
     * Scope para membresías inactivas (usuarios que se fueron)
     */
    public function scopeInactive($query)
    {
        return $query->whereNotNull('left_at');
    }

    /**
     * Scope para membresías por rol
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope para administradores
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope para moderadores
     */
    public function scopeModerators($query)
    {
        return $query->where('role', 'moderator');
    }

    /**
     * Scope para miembros regulares
     */
    public function scopeMembers($query)
    {
        return $query->where('role', 'member');
    }

    /**
     * Scope para membresías por equipo
     */
    public function scopeByTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope para membresías por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Verificar si la membresía está activa
     */
    public function isActive(): bool
    {
        return is_null($this->left_at);
    }

    /**
     * Accessor para is_active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->isActive();
    }

    /**
     * Verificar si es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verificar si es moderador
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    /**
     * Verificar si es miembro regular
     */
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    /**
     * Verificar si tiene permisos administrativos (admin o moderador)
     */
    public function hasAdminPrivileges(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    /**
     * Marcar como inactiva (usuario se va del equipo)
     */
    public function leave(): void
    {
        $this->update(['left_at' => now()]);
    }

    /**
     * Reactivar membresía (usuario regresa al equipo)
     */
    public function rejoin(): void
    {
        $this->update(['left_at' => null]);
    }

    /**
     * Cambiar rol del miembro
     */
    public function changeRole(string $newRole): void
    {
        $this->update(['role' => $newRole]);
    }

    /**
     * Promover a administrador
     */
    public function promoteToAdmin(): void
    {
        $this->changeRole('admin');
    }

    /**
     * Promover a moderador
     */
    public function promoteToModerator(): void
    {
        $this->changeRole('moderator');
    }

    /**
     * Degradar a miembro regular
     */
    public function demoteToMember(): void
    {
        $this->changeRole('member');
    }

    /**
     * Obtener la duración de la membresía
     */
    public function getDurationAttribute(): ?\DateInterval
    {
        if (!$this->joined_at) {
            return null;
        }

        $endDate = $this->left_at ?? now();
        return $this->joined_at->diff($endDate);
    }

    /**
     * Obtener la duración en días
     */
    public function getDurationInDaysAttribute(): ?int
    {
        $duration = $this->duration;
        return $duration ? $duration->days : null;
    }

    /**
     * Obtener estadísticas de membresías por equipo
     */
    public static function getTeamStats(int $teamId): array
    {
        $memberships = static::byTeam($teamId);
        
        return [
            'total_members' => $memberships->count(),
            'active_members' => $memberships->active()->count(),
            'inactive_members' => $memberships->inactive()->count(),
            'admins' => $memberships->active()->admins()->count(),
            'moderators' => $memberships->active()->moderators()->count(),
            'regular_members' => $memberships->active()->members()->count(),
        ];
    }

    /**
     * Obtener estadísticas de membresías por usuario
     */
    public static function getUserStats(int $userId): array
    {
        $memberships = static::byUser($userId);
        
        return [
            'total_teams' => $memberships->count(),
            'active_teams' => $memberships->active()->count(),
            'teams_as_admin' => $memberships->active()->admins()->count(),
            'teams_as_moderator' => $memberships->active()->moderators()->count(),
        ];
    }

    /**
     * Obtener miembros recientes de un equipo
     */
    public static function getRecentMembers(int $teamId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::byTeam($teamId)
                    ->active()
                    ->with('user')
                    ->orderBy('joined_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Establecer joined_at automáticamente al crear
        static::creating(function ($membership) {
            if (!$membership->joined_at) {
                $membership->joined_at = now();
            }
        });

        // Validar que no haya membresías duplicadas activas antes de crear
        static::creating(function ($membership) {
            // Eliminar cualquier membresía inactiva previa para evitar constraint único
            static::where('team_id', $membership->team_id)
                  ->where('user_id', $membership->user_id)
                  ->whereNotNull('left_at')
                  ->delete();
                             
            $existing = static::where('team_id', $membership->team_id)
                             ->where('user_id', $membership->user_id)
                             ->whereNull('left_at')
                             ->exists();
            
            if ($existing) {
                throw new \Exception('User is already an active member of this team');
            }
        });
    }
}
