<?php

namespace App\Policies;

use App\Models\UserOrganizationRole;
use App\Models\User;

class UserOrganizationRolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('user-organization-role.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserOrganizationRole $userOrganizationRole): bool
    {
        return $user->hasPermissionTo('user-organization-role.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('user-organization-role.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserOrganizationRole $userOrganizationRole): bool
    {
        return $user->hasPermissionTo('user-organization-role.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserOrganizationRole $userOrganizationRole): bool
    {
        return $user->hasPermissionTo('user-organization-role.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserOrganizationRole $userOrganizationRole): bool
    {
        return $user->hasPermissionTo('user-organization-role.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserOrganizationRole $userOrganizationRole): bool
    {
        return $user->hasPermissionTo('user-organization-role.delete');
    }
}
