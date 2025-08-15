<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChallengePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden ver desafíos
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Challenge $challenge): bool
    {
        // Si el desafío es global (sin organización), todos pueden verlo
        if (!$challenge->organization_id) {
            return true;
        }

        // Si es específico de organización, verificar pertenencia
        return $this->userCanAccessChallenge($user, $challenge);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo admins y super_admins pueden crear desafíos
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Challenge $challenge): bool
    {
        // Super admins pueden editar cualquier desafío
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins pueden editar desafíos de su organización
        if ($user->hasRole('admin')) {
            if (!$challenge->organization_id) {
                return false; // Admins no pueden editar desafíos globales
            }
            
            return $this->userBelongsToOrganization($user, $challenge->organization_id);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Challenge $challenge): bool
    {
        // Solo super_admins pueden eliminar desafíos
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins pueden eliminar desafíos de su organización si no han empezado
        if ($user->hasRole('admin') && $challenge->organization_id) {
            return $this->userBelongsToOrganization($user, $challenge->organization_id) 
                   && !$challenge->hasStarted();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Challenge $challenge): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Challenge $challenge): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can participate in the challenge.
     */
    public function participate(User $user, Challenge $challenge): bool
    {
        // El desafío debe estar activo y en curso
        if (!$challenge->isCurrentlyActive()) {
            return false;
        }

        // Si es un desafío específico de organización, verificar pertenencia
        if ($challenge->organization_id) {
            return $this->userBelongsToOrganization($user, $challenge->organization_id);
        }

        // Desafíos globales: todos pueden participar
        return true;
    }

    /**
     * Determine whether the user can view challenge statistics.
     */
    public function viewStatistics(User $user, Challenge $challenge): bool
    {
        return $this->view($user, $challenge);
    }

    /**
     * Determine whether the user can view the challenge leaderboard.
     */
    public function viewLeaderboard(User $user, Challenge $challenge): bool
    {
        return $this->view($user, $challenge);
    }

    /**
     * Determine whether the user can manage challenge progress.
     */
    public function manageProgress(User $user, Challenge $challenge): bool
    {
        // Super admins pueden gestionar cualquier progreso
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins pueden gestionar progreso de desafíos de su organización
        if ($user->hasRole('admin') && $challenge->organization_id) {
            return $this->userBelongsToOrganization($user, $challenge->organization_id);
        }

        return false;
    }

    /**
     * Determine whether the user can create challenges for a specific organization.
     */
    public function createForOrganization(User $user, \App\Models\Organization $organization): bool
    {
        // Super admins pueden crear desafíos para cualquier organización
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins pueden crear desafíos para su organización
        if ($user->hasRole('admin')) {
            return $this->userBelongsToOrganization($user, $organization->id);
        }

        return false;
    }

    /**
     * Determine whether the user can create global challenges.
     */
    public function createGlobal(User $user): bool
    {
        // Solo super_admins pueden crear desafíos globales
        return $user->hasRole('super_admin');
    }

    /**
     * Verificar si el usuario puede acceder a un desafío específico.
     */
    private function userCanAccessChallenge(User $user, Challenge $challenge): bool
    {
        // Si es global, todos pueden acceder
        if (!$challenge->organization_id) {
            return true;
        }

        // Si es específico de organización, verificar pertenencia
        return $this->userBelongsToOrganization($user, $challenge->organization_id);
    }

    /**
     * Verificar si el usuario pertenece a una organización específica.
     */
    private function userBelongsToOrganization(User $user, int $organizationId): bool
    {
        // Verificar a través del perfil del usuario
        $userProfile = $user->userProfile;
        if ($userProfile && $userProfile->organization_id === $organizationId) {
            return true;
        }

        // Verificar a través de membresías de equipos
        $belongsToOrg = \App\Models\Team::where('organization_id', $organizationId)
            ->whereHas('activeMemberships', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if ($belongsToOrg) {
            return true;
        }

        // Verificar a través de roles de organización
        $hasOrgRole = \App\Models\UserOrganizationRole::where('user_id', $user->id)
            ->whereHas('organizationRole', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->exists();

        return $hasOrgRole;
    }
}