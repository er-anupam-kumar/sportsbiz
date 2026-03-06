<?php

namespace Tests\Feature;

use App\Livewire\Admin\Players\Manager as PlayersManager;
use App\Livewire\Admin\Teams\Manager as TeamsManager;
use App\Livewire\Admin\Tournament\Create as TournamentCreate;
use App\Models\Player;
use App\Models\Sport;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminQuotaEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_tournament_beyond_subscription_limit(): void
    {
        $admin = $this->seedAdmin();
        $sport = Sport::query()->create([
            'name' => 'Cricket '.uniqid(),
            'slug' => 'cricket-'.uniqid(),
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        Subscription::query()->create([
            'admin_id' => $admin->id,
            'max_tournaments' => 1,
            'max_teams' => 10,
            'max_players' => 200,
            'expires_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        Tournament::query()->create([
            'admin_id' => $admin->id,
            'sport_id' => $sport->id,
            'name' => 'Existing Tournament',
            'purse_amount' => 100000,
            'max_players_per_team' => 15,
            'base_increment' => 1000,
            'auction_timer_seconds' => 30,
            'anti_sniping' => true,
            'auction_type' => 'live',
            'status' => 'draft',
        ]);

        $this->actingAs($admin);

        Livewire::test(TournamentCreate::class)
            ->set('sportId', $sport->id)
            ->set('name', 'Second Tournament')
            ->set('purseAmount', 200000)
            ->set('maxPlayersPerTeam', 15)
            ->set('baseIncrement', 1000)
            ->set('auctionTimerSeconds', 30)
            ->set('auctionType', 'live')
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertEquals(1, Tournament::query()->where('admin_id', $admin->id)->count());
    }

    public function test_admin_cannot_create_team_beyond_subscription_limit(): void
    {
        $admin = $this->seedAdmin();
        $sport = Sport::query()->create([
            'name' => 'Football '.uniqid(),
            'slug' => 'football-'.uniqid(),
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $tournament = Tournament::query()->create([
            'admin_id' => $admin->id,
            'sport_id' => $sport->id,
            'name' => 'Team Limit Tournament',
            'purse_amount' => 100000,
            'max_players_per_team' => 15,
            'base_increment' => 1000,
            'auction_timer_seconds' => 30,
            'anti_sniping' => true,
            'auction_type' => 'live',
            'status' => 'draft',
        ]);

        Subscription::query()->create([
            'admin_id' => $admin->id,
            'max_tournaments' => 3,
            'max_teams' => 1,
            'max_players' => 200,
            'expires_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        Team::query()->create([
            'admin_id' => $admin->id,
            'tournament_id' => $tournament->id,
            'name' => 'Existing Team',
            'wallet_balance' => 100000,
            'is_locked' => false,
        ]);

        $this->actingAs($admin);

        Livewire::test(TeamsManager::class)
            ->set('formTournamentId', $tournament->id)
            ->set('name', 'Second Team')
            ->set('email', 'team-limit@example.com')
            ->set('password', 'password123')
            ->set('walletBalance', 50000)
            ->set('isLocked', false)
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertEquals(1, Team::query()->where('admin_id', $admin->id)->count());
        $this->assertDatabaseMissing('users', ['email' => 'team-limit@example.com']);
    }

    public function test_admin_cannot_create_player_beyond_subscription_limit(): void
    {
        $admin = $this->seedAdmin();
        $sport = Sport::query()->create([
            'name' => 'Kabaddi '.uniqid(),
            'slug' => 'kabaddi-'.uniqid(),
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $tournament = Tournament::query()->create([
            'admin_id' => $admin->id,
            'sport_id' => $sport->id,
            'name' => 'Player Limit Tournament',
            'purse_amount' => 100000,
            'max_players_per_team' => 15,
            'base_increment' => 1000,
            'auction_timer_seconds' => 30,
            'anti_sniping' => true,
            'auction_type' => 'live',
            'status' => 'draft',
        ]);

        Subscription::query()->create([
            'admin_id' => $admin->id,
            'max_tournaments' => 3,
            'max_teams' => 10,
            'max_players' => 1,
            'expires_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        Player::factory()->create([
            'admin_id' => $admin->id,
            'tournament_id' => $tournament->id,
            'name' => 'Existing Player',
            'base_price' => 1000,
            'status' => 'available',
        ]);

        $this->actingAs($admin);

        Livewire::test(PlayersManager::class)
            ->set('formTournamentId', $tournament->id)
            ->set('name', 'Second Player')
            ->set('basePrice', 2000)
            ->set('status', 'available')
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertEquals(1, Player::query()->where('admin_id', $admin->id)->count());
    }

    private function seedAdmin(): User
    {
        Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        return $admin;
    }
}
