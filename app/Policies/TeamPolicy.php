<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return $this->ownsTeamContext($user, $team);
    }

    public function bid(User $user, Team $team): bool
    {
        return $this->ownsTeamContext($user, $team)
            && ! $team->is_locked
            && $user->hasAnyRole(['Admin', 'Team']);
    }

    public function manage(User $user, Team $team): bool
    {
        return $this->ownsTeamContext($user, $team)
            && $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    private function ownsTeamContext(User $user, Team $team): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        $adminId = $user->hasRole('Admin') ? $user->id : $user->parent_admin_id;

        return (int) $team->admin_id === (int) $adminId;
    }
}
