<?php

namespace App\Policies;

use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('customer-profile.view-any') ||
               $user->hasPermissionTo('customer-profile.view-org') ||
               $user->hasPermissionTo('customer-profile.view-own');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomerProfile $customerProfile): bool
    {
        // Super admin y admin pueden ver todo
        if ($user->hasPermissionTo('customer-profile.manage-all')) {
            return true;
        }

        // Ver perfiles propios
        if ($user->hasPermissionTo('customer-profile.view-own') && 
            $customerProfile->user_id === $user->id) {
            return true;
        }

        // Ver perfiles de la organización
        if ($user->hasPermissionTo('customer-profile.view-org')) {
            $userOrgId = $this->getUserOrganizationId($user);
            return $userOrgId && $customerProfile->organization_id === $userOrgId;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('customer-profile.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomerProfile $customerProfile): bool
    {
        // Super admin y admin pueden actualizar todo
        if ($user->hasPermissionTo('customer-profile.manage-all')) {
            return true;
        }

        // Actualizar perfiles propios
        if ($user->hasPermissionTo('customer-profile.update-own') && 
            $customerProfile->user_id === $user->id) {
            return true;
        }

        // Actualizar perfiles de la organización
        if ($user->hasPermissionTo('customer-profile.update-org')) {
            $userOrgId = $this->getUserOrganizationId($user);
            return $userOrgId && $customerProfile->organization_id === $userOrgId;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomerProfile $customerProfile): bool
    {
        // Super admin y admin pueden eliminar todo
        if ($user->hasPermissionTo('customer-profile.manage-all')) {
            return true;
        }

        // Eliminar perfiles propios
        if ($user->hasPermissionTo('customer-profile.delete-own') && 
            $customerProfile->user_id === $user->id) {
            return true;
        }

        // Eliminar perfiles de la organización
        if ($user->hasPermissionTo('customer-profile.delete-org')) {
            $userOrgId = $this->getUserOrganizationId($user);
            return $userOrgId && $customerProfile->organization_id === $userOrgId;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CustomerProfile $customerProfile): bool
    {
        return $user->hasPermissionTo('customer-profile.manage-all');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CustomerProfile $customerProfile): bool
    {
        return $user->hasPermissionTo('customer-profile.manage-all');
    }

    /**
     * Obtener el ID de la organización del usuario
     */
    private function getUserOrganizationId(User $user): ?int
    {
        // Buscar la organización del usuario a través de sus perfiles
        return CustomerProfile::where('user_id', $user->id)
            ->value('organization_id');
    }

    /**
     * Scope para filtrar perfiles según los permisos del usuario
     */
    public function scopeForUser($query, User $user)
    {
        // Super admin y admin pueden ver todo
        if ($user->hasPermissionTo('customer-profile.manage-all')) {
            return $query;
        }

        // Ver solo perfiles propios
        if ($user->hasPermissionTo('customer-profile.view-own') && 
            !$user->hasPermissionTo('customer-profile.view-org')) {
            return $query->where('user_id', $user->id);
        }

        // Ver perfiles de la organización
        if ($user->hasPermissionTo('customer-profile.view-org')) {
            $userOrgId = $this->getUserOrganizationId($user);
            if ($userOrgId) {
                return $query->where('organization_id', $userOrgId);
            }
        }

        // Si no tiene permisos, no ver nada
        return $query->whereRaw('1 = 0');
    }
}
