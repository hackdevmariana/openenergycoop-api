<?php

namespace App\Policies;

use App\Models\OrganizationRole;
use App\Models\User;

class OrganizationRolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('organization-role.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrganizationRole $organizationRole): bool
    {
        return $user->hasPermissionTo('organization-role.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('organization-role.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrganizationRole $organizationRole): bool
    {
        return $user->hasPermissionTo('organization-role.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrganizationRole $organizationRole): bool
    {
        return $user->hasPermissionTo('organization-role.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrganizationRole $organizationRole): bool
    {
        return $user->hasPermissionTo('organization-role.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrganizationRole $organizationRole): bool
    {
        return $user->hasPermissionTo('organization-role.delete');
    }
}
