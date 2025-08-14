<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOrganizationRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'organization_role_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la organización
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener el rol de la organización
     */
    public function organizationRole(): BelongsTo
    {
        return $this->belongsTo(OrganizationRole::class);
    }

    /**
     * Asignar rol a un usuario en una organización
     */
    public static function assignRole(int $userId, int $organizationId, int $roleId): self
    {
        return static::create([
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'organization_role_id' => $roleId,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Remover rol de un usuario en una organización
     */
    public static function removeRole(int $userId, int $organizationId, int $roleId): bool
    {
        return static::where([
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'organization_role_id' => $roleId,
        ])->delete() > 0;
    }

    /**
     * Verificar si un usuario tiene un rol específico en una organización
     */
    public static function hasRole(int $userId, int $organizationId, int $roleId): bool
    {
        return static::where([
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'organization_role_id' => $roleId,
        ])->exists();
    }

    /**
     * Obtener todos los roles de un usuario en una organización
     */
    public static function getUserRoles(int $userId, int $organizationId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where([
            'user_id' => $userId,
            'organization_id' => $organizationId,
        ])->with('organizationRole')->get();
    }

    /**
     * Obtener todos los usuarios con un rol específico en una organización
     */
    public static function getUsersWithRole(int $organizationId, int $roleId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where([
            'organization_id' => $organizationId,
            'organization_role_id' => $roleId,
        ])->with('user')->get();
    }
}
