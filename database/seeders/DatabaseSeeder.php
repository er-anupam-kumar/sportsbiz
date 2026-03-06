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

        $admins = collect([
            ['name' => 'Admin One', 'email' => 'admin1@sportsbiz.test'],
            ['name' => 'Admin Two', 'email' => 'admin2@sportsbiz.test'],
        ])->map(function (array $adminData): User {
            $admin = User::factory()->create($adminData);
            $admin->assignRole('Admin');

            Subscription::query()->create([
                'admin_id' => $admin->id,
                'max_tournaments' => 2,
                'max_teams' => 20,
                'max_players' => 100,
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ]);

            return $admin;
        });

        $sport = Sport::query()->create([
            'name' => 'Cricket',
            'slug' => 'cricket',
            'created_by' => $superAdmin->id,
            'is_active' => true,
        ]);

        $colorPairs = [
            ['#1d4ed8', '#93c5fd'],
            ['#be123c', '#fecdd3'],
            ['#047857', '#a7f3d0'],
            ['#7c3aed', '#ddd6fe'],
            ['#b45309', '#fde68a'],
            ['#0f766e', '#99f6e4'],
            ['#475569', '#cbd5e1'],
            ['#0369a1', '#bae6fd'],
            ['#4338ca', '#c7d2fe'],
            ['#334155', '#e2e8f0'],
        ];

        $tournamentsPerAdmin = 2;
        $teamsPerTournament = 10;
        $playersPerTournament = 50;

        foreach ($admins as $adminIndex => $admin) {
            for ($t = 1; $t <= $tournamentsPerAdmin; $t++) {
                $tournamentNumber = ($adminIndex * $tournamentsPerAdmin) + $t;

                $tournament = Tournament::query()->create([
                    'admin_id' => $admin->id,
                    'sport_id' => $sport->id,
                    'name' => "Tournament {$tournamentNumber}",
                    'purse_amount' => 10000000,
                    'max_players_per_team' => 18,
                    'base_increment' => 50000,
                    'auction_timer_seconds' => 30,
                    'anti_sniping' => true,
                    'auction_type' => 'live',
                    'status' => 'active',
                    'starts_at' => now()->subDays(2),
                    'trade_window_ends_at' => now()->addDays(30),
                ]);

                $categories = collect([
                    ['name' => 'Batsman', 'max_per_team' => 6],
                    ['name' => 'Bowler', 'max_per_team' => 6],
                    ['name' => 'All-Rounder', 'max_per_team' => 4],
                    ['name' => 'Wicket Keeper', 'max_per_team' => 2],
                ])->map(fn (array $category) => PlayerCategory::query()->create([
                    'tournament_id' => $tournament->id,
                    'name' => $category['name'],
                    'max_per_team' => $category['max_per_team'],
                ]));

                $teams = collect();
                for ($teamNumber = 1; $teamNumber <= $teamsPerTournament; $teamNumber++) {
                    $teamUser = User::factory()->create([
                        'parent_admin_id' => $admin->id,
                        'name' => "Team User T{$tournamentNumber}-{$teamNumber}",
                        'email' => "team.t{$tournamentNumber}.{$teamNumber}@sportsbiz.test",
                    ]);
                    $teamUser->assignRole('Team');

                    [$primaryColor, $secondaryColor] = $colorPairs[($teamNumber - 1) % count($colorPairs)];

                    $teams->push(Team::query()->create([
                        'admin_id' => $admin->id,
                        'tournament_id' => $tournament->id,
                        'user_id' => $teamUser->id,
                        'name' => "Team T{$tournamentNumber}-{$teamNumber}",
                        'primary_color' => $primaryColor,
                        'secondary_color' => $secondaryColor,
                        'wallet_balance' => 2000000,
                        'squad_count' => 0,
                    ]));
                }

                $players = collect();
                for ($playerNumber = 1; $playerNumber <= $playersPerTournament; $playerNumber++) {
                    $category = $categories[($playerNumber - 1) % $categories->count()];

                    $players->push(Player::query()->create([
                        'admin_id' => $admin->id,
                        'tournament_id' => $tournament->id,
                        'category_id' => $category->id,
                        'name' => "Player T{$tournamentNumber}-{$playerNumber}",
                        'base_price' => 100000 + (($playerNumber - 1) * 5000),
                        'age' => 19 + (($playerNumber - 1) % 15),
                        'country' => collect(['India', 'Australia', 'England', 'South Africa', 'New Zealand', 'Pakistan', 'Sri Lanka', 'West Indies'])->random(),
                        'previous_team' => $teams->random()->name,
                        'status' => 'available',
                    ]));
                }

                Auction::query()->create([
                    'tournament_id' => $tournament->id,
                    'current_player_id' => $players->first()?->id,
                    'current_bid' => 0,
                    'started_at' => now(),
                    'ends_at' => now()->addSeconds(30),
                    'is_paused' => false,
                ]);
            }
        }
    }
}
