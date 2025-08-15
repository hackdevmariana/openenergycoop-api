<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Access\Response;

class UserProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserProfile $userProfile): bool
    {
        return true;
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
    public function update(User $user, UserProfile $userProfile): bool
    {
        // Users can update their own profile
        if ($userProfile->user_id === $user->id) {
            return true;
        }
        
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserProfile $userProfile): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserProfile $userProfile): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserProfile $userProfile): bool
    {
        return $user->hasRole('super_admin');
    }
}