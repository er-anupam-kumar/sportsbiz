<?php

namespace Tests\Feature;

use App\Exceptions\AuctionException;
use App\Models\Auction;
use App\Models\Player;
use App\Models\PlayerCategory;
use App\Models\Sport;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use App\Services\AuctionEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuctionEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_bid_validation_fails_when_wallet_below_base_price(): void
    {
        [$auction, $teamA] = $this->seedAuctionContext(walletA: 50000, basePrice: 100000);

        $this->expectException(AuctionException::class);
        $this->expectExceptionMessage('Wallet is lower than base price.');

        app(AuctionEngine::class)->placeBid($teamA->id, $auction->current_player_id);
    }

    public function test_wallet_deduction_occurs_only_when_player_marked_sold(): void
    {
        [$auction, $teamA, $teamB] = $this->seedAuctionContext(walletA: 500000, walletB: 500000, basePrice: 100000);

        app(AuctionEngine::class)->placeBid($teamA->id, $auction->current_player_id);
        app(AuctionEngine::class)->placeBid($teamB->id, $auction->current_player_id);

        $beforeSellWallet = (float) $teamB->fresh()->wallet_balance;
        $this->assertEquals(500000.0, $beforeSellWallet);

        app(AuctionEngine::class)->markPlayerSold($auction->id);

        $afterSellWallet = (float) $teamB->fresh()->wallet_balance;
        $this->assertLessThan($beforeSellWallet, $afterSellWallet);
    }

    public function test_category_limit_rule_is_enforced(): void
    {
        [$auction, $teamA] = $this->seedAuctionContext(walletA: 600000, basePrice: 100000, categoryMaxPerTeam: 1);
        $player = Player::query()->findOrFail($auction->current_player_id);

        Player::factory()->create([
            'admin_id' => $player->admin_id,
            'tournament_id' => $player->tournament_id,
            'category_id' => $player->category_id,
            'status' => 'sold',
            'sold_team_id' => $teamA->id,
            'final_price' => 100000,
            'base_price' => 100000,
        ]);

        $this->expectException(AuctionException::class);
        $this->expectExceptionMessage('Category limit exceeded.');

        app(AuctionEngine::class)->placeBid($teamA->id, $player->id);
    }

    public function test_max_players_rule_is_enforced(): void
    {
        [$auction, $teamA] = $this->seedAuctionContext(walletA: 600000, basePrice: 100000, maxPlayersPerTeam: 1);
        $teamA->update(['squad_count' => 1]);

        $this->expectException(AuctionException::class);
        $this->expectExceptionMessage('Team max players reached.');

        app(AuctionEngine::class)->placeBid($teamA->id, $auction->current_player_id);
    }

    public function test_anti_sniping_extends_timer_by_configured_seconds(): void
    {
        config()->set('auction.anti_sniping_window_seconds', 5);
        config()->set('auction.anti_sniping_extension_seconds', 10);

        [$auction, $teamA] = $this->seedAuctionContext(walletA: 600000, basePrice: 100000);

        $auction->update(['ends_at' => now()->addSeconds(4)]);
        $before = $auction->fresh()->ends_at;

        app(AuctionEngine::class)->placeBid($teamA->id, $auction->current_player_id);

        $after = $auction->fresh()->ends_at;
        $this->assertGreaterThan($before->timestamp, $after->timestamp);
    }

    private function seedAuctionContext(
        float $walletA,
        float $basePrice,
        float $walletB = 500000,
        int $categoryMaxPerTeam = 3,
        int $maxPlayersPerTeam = 15,
    ): array {
        Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'Team', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $sport = Sport::query()->create([
            'name' => 'Football'.uniqid(),
            'slug' => 'football-'.uniqid(),
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $tournament = Tournament::query()->create([
            'admin_id' => $admin->id,
            'sport_id' => $sport->id,
            'name' => 'Test Tournament '.uniqid(),
            'purse_amount' => 1000000,
            'max_players_per_team' => $maxPlayersPerTeam,
            'base_increment' => 10000,
            'auction_timer_seconds' => 30,
            'anti_sniping' => true,
            'auction_type' => 'live',
            'status' => 'active',
        ]);

        $category = PlayerCategory::query()->create([
            'tournament_id' => $tournament->id,
            'name' => 'AllRounder',
            'max_per_team' => $categoryMaxPerTeam,
        ]);

        $player = Player::factory()->create([
            'admin_id' => $admin->id,
            'tournament_id' => $tournament->id,
            'category_id' => $category->id,
            'base_price' => $basePrice,
            'status' => 'available',
        ]);

        $teamA = Team::query()->create([
            'admin_id' => $admin->id,
            'tournament_id' => $tournament->id,
            'name' => 'Team A '.uniqid(),
            'wallet_balance' => $walletA,
            'squad_count' => 0,
            'is_locked' => false,
        ]);

        $teamB = Team::query()->create([
            'admin_id' => $admin->id,
            'tournament_id' => $tournament->id,
            'name' => 'Team B '.uniqid(),
            'wallet_balance' => $walletB,
            'squad_count' => 0,
            'is_locked' => false,
        ]);

        $auction = Auction::query()->create([
            'tournament_id' => $tournament->id,
            'current_player_id' => $player->id,
            'current_bid' => 0,
            'is_paused' => false,
            'started_at' => now(),
            'ends_at' => now()->addSeconds(30),
        ]);

        return [$auction, $teamA, $teamB];
    }
}
