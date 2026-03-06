<?php

use App\Models\Auction;
use App\Models\Tournament;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tournament.{tournamentId}', function ($user, int $tournamentId) {
    if ($user->hasRole('SuperAdmin')) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'SuperAdmin'];
    }

    $tournament = Tournament::query()->find($tournamentId);
    if (! $tournament) {
        return false;
    }

    $adminId = $user->hasRole('Admin') ? $user->id : $user->parent_admin_id;
    if ((int) $tournament->admin_id !== (int) $adminId) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->getRoleNames()->first(),
    ];
});

Broadcast::channel('auction.{auctionId}', function ($user, int $auctionId) {
    if ($user->hasRole('SuperAdmin')) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'SuperAdmin'];
    }

    $auction = Auction::query()
        ->with('tournament:id,admin_id')
        ->find($auctionId);

    if (! $auction || ! $auction->tournament) {
        return false;
    }

    $adminId = $user->hasRole('Admin') ? $user->id : $user->parent_admin_id;
    if ((int) $auction->tournament->admin_id !== (int) $adminId) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->getRoleNames()->first(),
    ];
});
