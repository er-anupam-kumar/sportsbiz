<?php

namespace App\Providers;

use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Policies\AuctionPolicy;
use App\Policies\PlayerPolicy;
use App\Policies\TeamPolicy;
use App\Policies\TournamentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Tournament::class => TournamentPolicy::class,
        Team::class => TeamPolicy::class,
        Player::class => PlayerPolicy::class,
        Auction::class => AuctionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
