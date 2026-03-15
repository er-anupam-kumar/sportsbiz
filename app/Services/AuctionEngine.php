<?php

namespace App\Services;

use App\Events\BidPlaced;
use App\Events\PlayerSold;
use App\Exceptions\AuctionException;
use App\Models\Auction;
use App\Models\AuctionLog;
use App\Models\Bid;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\TeamWalletTransaction;
use Illuminate\Support\Facades\DB;

class AuctionEngine
{
    public function placeBid(int $teamId, int $playerId, bool $isAutoBid = false): Bid
    {
        return DB::transaction(function () use ($teamId, $playerId, $isAutoBid): Bid {
            $team = Team::query()->whereKey($teamId)->lockForUpdate()->firstOrFail();
            $player = Player::query()->whereKey($playerId)->lockForUpdate()->firstOrFail();

            $auction = Auction::query()
                ->where('tournament_id', $player->tournament_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($auction->is_paused) {
                throw new AuctionException('Auction is paused.');
            }

            if (! $auction->ends_at || now()->greaterThan($auction->ends_at)) {
                throw new AuctionException('Auction timer expired.');
            }

            if ($player->status !== 'available' || (int) $auction->current_player_id !== (int) $player->id) {
                throw new AuctionException('Player is not available for active bidding.');
            }

            if ((int) $auction->current_highest_team_id === (int) $team->id) {
                throw new AuctionException('Current highest bidder cannot bid again.');
            }

            if ($team->is_locked) {
                throw new AuctionException('Team is locked for bidding.');
            }

            $tournament = $player->tournament;

            if ((int) $team->squad_count >= (int) $tournament->max_players_per_team) {
                throw new AuctionException('Team max players reached.');
            }

            if ($player->category_id) {
                $category = $player->category;
                $soldInCategory = Player::query()
                    ->where('sold_team_id', $team->id)
                    ->where('category_id', $player->category_id)
                    ->where('status', 'sold')
                    ->count();

                if ($category && $soldInCategory >= $category->max_per_team) {
                    throw new AuctionException('Category limit exceeded.');
                }
            }

            if (bccomp((string) $team->wallet_balance, (string) $player->base_price, 2) < 0) {
                throw new AuctionException('Wallet is lower than base price.');
            }

            $nextBid = $auction->current_bid > 0
                ? $auction->current_bid + $tournament->base_increment
                : $player->base_price;

            if (bccomp((string) $team->wallet_balance, (string) $nextBid, 2) < 0) {
                throw new AuctionException('Wallet is lower than required bid amount.');
            }

            $bid = Bid::query()->create([
                'auction_id' => $auction->id,
                'tournament_id' => $tournament->id,
                'player_id' => $player->id,
                'team_id' => $team->id,
                'amount' => $nextBid,
                'is_auto_bid' => $isAutoBid,
            ]);

            $auction->forceFill([
                'current_bid' => $nextBid,
                'current_highest_team_id' => $team->id,
                'last_bid_at' => now(),
            ]);

            $remainingSeconds = (int) now()->diffInSeconds($auction->ends_at, false);
            if ($tournament->anti_sniping && $remainingSeconds >= 0 && $remainingSeconds <= config('auction.anti_sniping_window_seconds', 5)) {
                $auction->ends_at = $auction->ends_at->addSeconds((int) config('auction.anti_sniping_extension_seconds', 10));
            }

            $auction->save();

            AuctionLog::query()->create([
                'tournament_id' => $tournament->id,
                'auction_id' => $auction->id,
                'actor_id' => auth()->id(),
                'action' => 'bid_placed',
                'payload' => [
                    'team_id' => $team->id,
                    'player_id' => $player->id,
                    'bid_id' => $bid->id,
                    'amount' => $nextBid,
                    'is_auto_bid' => $isAutoBid,
                ],
            ]);

            event(new BidPlaced($bid, auth()->id()));

            return $bid;
        });
    }

    public function markPlayerSold(int $auctionId): Player
    {
        return DB::transaction(function () use ($auctionId): Player {
            $auction = Auction::query()->whereKey($auctionId)->lockForUpdate()->firstOrFail();


            if (! $auction->current_player_id) {
                throw new AuctionException('No sellable state found in active auction.');
            }

            $player = Player::query()->whereKey($auction->current_player_id)->lockForUpdate()->firstOrFail();

            // Explicit logic: For base price > 0, require a bid (current_highest_team_id)
            if ($player->base_price > 0 && ! $auction->current_highest_team_id) {
                throw new AuctionException('A bid must be placed to mark as sold.');
            }
            // For base price 0, allow marking as sold if a team has placed a bid (current_highest_team_id set, even if bid is 0)
            if ($player->base_price == 0 && ! $auction->current_highest_team_id) {
                throw new AuctionException('A team must be selected to mark as sold.');
            }

            $team = Team::query()->whereKey($auction->current_highest_team_id)->lockForUpdate()->firstOrFail();

            $player = Player::query()->whereKey($auction->current_player_id)->lockForUpdate()->firstOrFail();
            $team = Team::query()->whereKey($auction->current_highest_team_id)->lockForUpdate()->firstOrFail();

            if (bccomp((string) $team->wallet_balance, (string) $auction->current_bid, 2) < 0) {
                throw new AuctionException('Team wallet no longer covers final bid.');
            }

            $team->wallet_balance = $team->wallet_balance - $auction->current_bid;
            $team->squad_count = $team->squad_count + 1;
            $team->save();

            $player->forceFill([
                'status' => 'sold',
                'sold_team_id' => $team->id,
                'final_price' => $auction->current_bid,
            ])->save();

            TeamWalletTransaction::query()->create([
                'team_id' => $team->id,
                'tournament_id' => $auction->tournament_id,
                'player_id' => $player->id,
                'type' => 'debit',
                'amount' => $auction->current_bid,
                'description' => 'Player sold in auction',
                'meta' => ['auction_id' => $auction->id],
            ]);

            AuctionLog::query()->create([
                'tournament_id' => $auction->tournament_id,
                'auction_id' => $auction->id,
                'actor_id' => auth()->id(),
                'action' => 'player_sold',
                'payload' => [
                    'player_id' => $player->id,
                    'team_id' => $team->id,
                    'amount' => $auction->current_bid,
                ],
            ]);

            event(new PlayerSold($auction->tournament_id, $auction->id, $player->id, $team->id, (float) $auction->current_bid, auth()->id()));

            return $player;
        });
    }

    public function placeAutoBid(int $teamId, int $playerId): Bid
    {
        return $this->placeBid($teamId, $playerId, true);
    }

    public function cloneTournament(int $tournamentId, string $newName): Tournament
    {
        return DB::transaction(function () use ($tournamentId, $newName): Tournament {
            $source = Tournament::query()->with(['categories', 'players'])->findOrFail($tournamentId);

            $clone = Tournament::query()->create([
                'admin_id' => $source->admin_id,
                'sport_id' => $source->sport_id,
                'name' => $newName,
                'purse_amount' => $source->purse_amount,
                'max_players_per_team' => $source->max_players_per_team,
                'category_limits' => $source->category_limits,
                'base_increment' => $source->base_increment,
                'auction_timer_seconds' => $source->auction_timer_seconds,
                'anti_sniping' => $source->anti_sniping,
                'auction_type' => $source->auction_type,
                'status' => 'draft',
            ]);

            foreach ($source->categories as $category) {
                $clone->categories()->create([
                    'name' => $category->name,
                    'max_per_team' => $category->max_per_team,
                ]);
            }

            foreach ($source->players as $player) {
                $clone->players()->create([
                    'admin_id' => $clone->admin_id,
                    'name' => $player->name,
                    'base_price' => $player->base_price,
                    'image_path' => $player->image_path,
                    'stats' => $player->stats,
                    'age' => $player->age,
                    'country' => $player->country,
                    'previous_team' => $player->previous_team,
                    'status' => 'available',
                ]);
            }

            return $clone;
        });
    }
}
