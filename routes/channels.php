<?php

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
    return ['id' => $user->id, 'name' => $user->name];
});
