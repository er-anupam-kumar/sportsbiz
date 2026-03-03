<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;

class AuctionPolicy
{
    public function view(User $user, Auction $auction): bool
    {
        return $this->ownsAuctionContext($user, $auction);
    }

    public function control(User $user, Auction $auction): bool
    {
        return $this->ownsAuctionContext($user, $auction)
            && $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    private function ownsAuctionContext(User $user, Auction $auction): bool
    {
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        $adminId = $user->hasRole('Admin') ? $user->id : $user->parent_admin_id;

        return (int) $auction->tournament->admin_id === (int) $adminId;
    }
}
