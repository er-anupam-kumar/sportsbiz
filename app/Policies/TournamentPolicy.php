<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;

class TournamentPolicy
{
    public function view(User $user, Tournament $tournament): bool
    {
        return $this->ownsTournament($user, $tournament);
    }

    public function update(User $user, Tournament $tournament): bool
    {
        return $this->ownsTournament($user, $tournament);
    }

    public function manageAuction(User $user, Tournament $tournament): bool
    {
        return $this->ownsTournament($user, $tournament) && $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    private function ownsTournament(User $user, Tournament $tournament): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        $adminId = $user->hasRole('Admin') ? $user->id : $user->parent_admin_id;

        return (int) $tournament->admin_id === (int) $adminId;
    }
}
