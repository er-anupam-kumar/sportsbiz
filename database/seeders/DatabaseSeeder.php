<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\Player;
use App\Models\PlayerCategory;
use App\Models\Sport;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['SuperAdmin', 'Admin', 'Team'] as $roleName) {
            Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@sportsbiz.test',
        ]);
        $superAdmin->assignRole('SuperAdmin');

        $admin = User::factory()->create([
            'name' => 'Client Admin',
            'email' => 'admin@sportsbiz.test',
        ]);
        $admin->assignRole('Admin');

        Subscription::query()->create([
            'admin_id' => $admin->id,
            'max_tournaments' => 5,
            'max_teams' => 8,
            'max_players' => 120,
            'expires_at' => now()->addMonths(6),
            'is_active' => true,
        ]);

        $sport = Sport::query()->create([
            'name' => 'Cricket',
            'slug' => 'cricket',
            'created_by' => $superAdmin->id,
            'is_active' => true,
        ]);

        $tournament = Tournament::query()->create([
            'admin_id' => $admin->id,
            'sport_id' => $sport->id,
            'name' => 'Premier Auction Cup',
            'purse_amount' => 10000000,
            'max_players_per_team' => 18,
            'base_increment' => 50000,
            'auction_timer_seconds' => 30,
            'anti_sniping' => true,
            'auction_type' => 'live',
            'status' => 'active',
        ]);

        $categoryA = PlayerCategory::query()->create([
            'tournament_id' => $tournament->id,
            'name' => 'Batsman',
            'max_per_team' => 6,
        ]);

        $teamUsers = User::factory()->count(2)->create([
            'parent_admin_id' => $admin->id,
        ]);

        $teams = collect();
        foreach ($teamUsers as $index => $teamUser) {
            $teamUser->assignRole('Team');
            $teams->push(Team::query()->create([
                'admin_id' => $admin->id,
                'tournament_id' => $tournament->id,
                'user_id' => $teamUser->id,
                'name' => 'Team '.($index + 1),
                'wallet_balance' => 2000000,
                'squad_count' => 0,
            ]));
        }

        $players = Player::factory()->count(5)->create([
            'admin_id' => $admin->id,
            'tournament_id' => $tournament->id,
            'category_id' => $categoryA->id,
            'status' => 'available',
            'base_price' => 100000,
        ]);

        Auction::query()->create([
            'tournament_id' => $tournament->id,
            'current_player_id' => $players->first()->id,
            'current_bid' => 0,
            'started_at' => now(),
            'ends_at' => now()->addSeconds(30),
            'is_paused' => false,
        ]);
    }
}
