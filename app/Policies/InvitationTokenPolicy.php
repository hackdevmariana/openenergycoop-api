<?php

namespace App\Policies;

use App\Models\InvitationToken;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvitationTokenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InvitationToken $invitationToken): bool
    {
        // Super admins can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admins can view tokens they created or from their organization
        if ($user->hasRole('admin')) {
            return $invitationToken->invited_by === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InvitationToken $invitationToken): bool
    {
        // Super admins can update all
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Admins can update tokens they created
        if ($user->hasRole('admin')) {
            return $invitationToken->invited_by === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InvitationToken $invitationToken): bool
    {
        return $this->update($user, $invitationToken);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InvitationToken $invitationToken): bool
    {
        return $this->update($user, $invitationToken);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InvitationToken $invitationToken): bool
    {
        return $user->hasRole('super_admin');
    }
}