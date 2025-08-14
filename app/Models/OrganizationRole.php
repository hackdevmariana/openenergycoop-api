<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener la organización a la que pertenece este rol
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener los usuarios que tienen este rol
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserOrganizationRole::class);
    }

    /**
     * Obtener los usuarios que tienen este rol
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_organization_roles')
                    ->withPivot('assigned_at')
                    ->withTimestamps();
    }

    /**
     * Relación virtual para contar permisos (usado por Filament)
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Esta es una relación virtual que devuelve una colección vacía
        // pero permite que Filament pueda usar count() en la tabla
        return $this->hasMany(\App\Models\Permission::class, 'id', 'id');
    }

    /**
     * Verificar si el rol tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Agregar un permiso al rol
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Remover un permiso del rol
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_diff($permissions, [$permission]);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Generar slug automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = \Str::slug($role->name);
            }
        });
    }

    /**
     * Buscar roles por organización
     */
    public static function findByOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('organization_id', $organizationId)->get();
    }

    /**
     * Buscar rol por slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
