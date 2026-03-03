<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;

class PlayerPolicy
{
    public function view(User $user, Player $player): bool
    {
        return $this->ownsPlayerContext($user, $player);
    }

    public function update(User $user, Player $player): bool
    {
        return $this->ownsPlayerContext($user, $player)
            && $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    private function ownsPlayerContext(User $user, Player $player): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        $adminId = $user->hasRole('Admin') ? $user->id : $user->parent_admin_id;

        return (int) $player->admin_id === (int) $adminId;
    }
}
