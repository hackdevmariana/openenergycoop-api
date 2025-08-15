<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden ver equipos
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        return true; // Todos los usuarios autenticados pueden ver equipos específicos
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden crear equipos
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        // Super admins pueden editar cualquier equipo
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins pueden editar equipos de su organización
        if ($user->hasRole('admin') && $team->organization_id) {
            // Verificar si el usuario pertenece a la misma organización
            return $this->userBelongsToOrganization($user, $team->organization_id);
        }

        // El creador del equipo puede editarlo
        if ($team->created_by_user_id === $user->id) {
            return true;
        }

        // Los administradores del equipo pueden editarlo
        return $team->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        // Super admins pueden eliminar cualquier equipo
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins pueden eliminar equipos de su organización
        if ($user->hasRole('admin') && $team->organization_id) {
            return $this->userBelongsToOrganization($user, $team->organization_id);
        }

        // Solo el creador del equipo puede eliminarlo
        return $team->created_by_user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Team $team): bool
    {
        return $this->delete($user, $team);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Team $team): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can join the team.
     */
    public function join(User $user, Team $team): bool
    {
        // No puede unirse si ya es miembro
        if ($team->hasMember($user)) {
            return false;
        }

        // No puede unirse si el equipo está lleno
        if ($team->isFull()) {
            return false;
        }

        // Si el equipo es abierto, puede unirse
        if ($team->is_open) {
            return true;
        }

        // Si el equipo es cerrado, solo puede unirse por invitación o si es admin
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can leave the team.
     */
    public function leave(User $user, Team $team): bool
    {
        // Solo puede salir si es miembro
        return $team->hasMember($user);
    }

    /**
     * Determine whether the user can manage team members.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        // Super admins pueden gestionar cualquier equipo
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins pueden gestionar equipos de su organización
        if ($user->hasRole('admin') && $team->organization_id) {
            return $this->userBelongsToOrganization($user, $team->organization_id);
        }

        // El creador del equipo puede gestionar miembros
        if ($team->created_by_user_id === $user->id) {
            return true;
        }

        // Los administradores del equipo pueden gestionar miembros
        return $team->isAdmin($user);
    }

    /**
     * Determine whether the user can invite others to the team.
     */
    public function invite(User $user, Team $team): bool
    {
        return $this->manageMembers($user, $team);
    }

    /**
     * Determine whether the user can remove members from the team.
     */
    public function removeMember(User $user, Team $team): bool
    {
        return $this->manageMembers($user, $team);
    }

    /**
     * Determine whether the user can change member roles.
     */
    public function changeMemberRole(User $user, Team $team): bool
    {
        return $this->manageMembers($user, $team);
    }

    /**
     * Determine whether the user can create teams in a specific organization.
     */
    public function createTeamIn(User $user, \App\Models\Organization $organization): bool
    {
        // Super admins pueden crear equipos en cualquier organización
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins pueden crear equipos en su organización
        if ($user->hasRole('admin')) {
            return $this->userBelongsToOrganization($user, $organization->id);
        }

        // Otros usuarios pueden crear equipos si pertenecen a la organización
        return $this->userBelongsToOrganization($user, $organization->id);
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

        return $belongsToOrg;
    }
}