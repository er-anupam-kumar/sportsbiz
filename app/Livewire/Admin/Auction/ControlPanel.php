<?php

namespace App\Livewire\Admin\Auction;

use App\Events\AuctionPaused;
use App\Events\PlayerShuffled;
use App\Events\AuctionStarted;
use App\Events\TimerExtended;
use App\Exceptions\AuctionException;
use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Services\AuctionEngine;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ControlPanel extends Component
{
    use AuthorizesRequests;

    public Tournament $tournament;

    public function getListeners(): array
    {
        return [
            "echo-presence:tournament.{$this->tournament->id},BidPlaced" => '$refresh',
            "echo-presence:tournament.{$this->tournament->id},TimerExtended" => '$refresh',
            "echo-presence:tournament.{$this->tournament->id},PlayerSold" => '$refresh',
            "echo-presence:tournament.{$this->tournament->id},AuctionStarted" => '$refresh',
            "echo-presence:tournament.{$this->tournament->id},AuctionPaused" => '$refresh',
            "echo-presence:tournament.{$this->tournament->id},PlayerShuffled" => 'handlePlayerShuffled',
        ];
    }

    public function handlePlayerShuffled(): void
    {
        $this->dispatch('auction-player-shuffled');
    }

    public function mount(Tournament $tournament): void
    {
        $this->authorize('update', $tournament);
        $this->tournament = $tournament;
    }

    public function startAuction(): void
    {
        $auction = Auction::query()->firstOrCreate(
            ['tournament_id' => $this->tournament->id],
            ['current_bid' => 0]
        );

        $currentPlayerId = $this->resolveCurrentPlayerId($auction);

        if (! $currentPlayerId) {
            $this->dispatch('toast', message: 'No available players to start auction.');
            return;
        }

        $auction->update([
            'current_player_id' => $currentPlayerId,
            'current_highest_team_id' => null,
            'current_bid' => 0,
            'is_paused' => false,
            'started_at' => now(),
            'ends_at' => now()->addSeconds($this->tournament->auction_timer_seconds),
        ]);

        event(new AuctionStarted($this->tournament->id));
        $this->dispatch('toast', message: 'Auction started.');
    }

    public function pauseAuction(): void
    {
        Auction::query()->where('tournament_id', $this->tournament->id)->update(['is_paused' => true]);
        event(new AuctionPaused($this->tournament->id));
        $this->dispatch('toast', message: 'Auction paused.');
    }

    public function resumeAuction(): void
    {
        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();
        if (! $auction) {
            $this->dispatch('toast', message: 'Auction is not initialized yet.');
            return;
        }

        if (! $auction->current_player_id) {
            $nextPlayerId = $this->resolveCurrentPlayerId($auction);
            if (! $nextPlayerId) {
                $this->dispatch('toast', message: 'No available players to resume auction.');
                return;
            }
            $auction->current_player_id = $nextPlayerId;
        }

        $auction->is_paused = false;
        if (! $auction->ends_at || now()->greaterThan($auction->ends_at)) {
            $auction->ends_at = now()->addSeconds($this->tournament->auction_timer_seconds);
        }
        $auction->save();

        event(new AuctionStarted($this->tournament->id));
        $this->dispatch('toast', message: 'Auction resumed.');
    }

    public function extendTimer(int $seconds = 10): void
    {
        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();
        if (! $auction || ! $auction->ends_at) {
            $this->dispatch('toast', message: 'Cannot extend timer before auction starts.');
            return;
        }

        $auction->update(['ends_at' => $auction->ends_at->addSeconds($seconds)]);
        event(new TimerExtended($this->tournament->id, $seconds));
        $this->dispatch('toast', message: "Timer extended by {$seconds}s.");
    }

    public function markSold(AuctionEngine $auctionEngine): void
    {
        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();
        if (! $auction) {
            $this->dispatch('toast', message: 'Auction is not initialized yet.');
            return;
        }

        try {
            $auctionEngine->markPlayerSold($auction->id);
            $this->advanceToNextPlayer($auction);
            $this->dispatch('toast', message: 'Player marked sold.');
        } catch (AuctionException $exception) {
            $this->dispatch('toast', message: $exception->getMessage());
        }
    }

    public function markUnsold(): void
    {
        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();
        if (! $auction || ! $auction->current_player_id) {
            $this->dispatch('toast', message: 'No active player to mark unsold.');
            return;
        }

        $player = Player::query()->whereKey($auction->current_player_id)->first();
        if (! $player) {
            $this->dispatch('toast', message: 'Current player not found.');
            return;
        }

        $player->update([
            'status' => 'unsold',
            'sold_team_id' => null,
            'final_price' => null,
        ]);

        $this->advanceToNextPlayer($auction);
        $this->dispatch('toast', message: 'Player marked UNSOLD.');
    }

    public function shufflePlayers(): void
    {
        $auction = Auction::query()->firstOrCreate(
            ['tournament_id' => $this->tournament->id],
            ['current_bid' => 0]
        );

        $nextPlayerId = Player::query()
            ->where('tournament_id', $this->tournament->id)
            ->where('status', 'available')
            ->inRandomOrder()
            ->value('id');

        if (! $nextPlayerId) {
            $recycled = Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'unsold')
                ->update(['status' => 'available']);

            if ($recycled > 0) {
                $nextPlayerId = Player::query()
                    ->where('tournament_id', $this->tournament->id)
                    ->where('status', 'available')
                    ->inRandomOrder()
                    ->value('id');
            }
        }

        if (! $nextPlayerId) {
            $this->dispatch('toast', message: 'No players available to shuffle.');
            return;
        }

        $auction->update([
            'current_player_id' => $nextPlayerId,
            'current_highest_team_id' => null,
            'current_bid' => 0,
            'last_bid_at' => null,
            'ends_at' => now()->addSeconds($this->tournament->auction_timer_seconds),
        ]);

        event(new PlayerShuffled($this->tournament->id, (int) $nextPlayerId));

        $this->dispatch('toast', message: 'Players shuffled. New player loaded.');
    }

    private function resolveCurrentPlayerId(Auction $auction): ?int
    {
        $currentIsAvailable = $auction->current_player_id
            ? Player::query()
                ->whereKey($auction->current_player_id)
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'available')
                ->exists()
            : false;

        if ($currentIsAvailable) {
            return (int) $auction->current_player_id;
        }

        return Player::query()
            ->where('tournament_id', $this->tournament->id)
            ->where('status', 'available')
            ->orderBy('id')
            ->value('id');
    }

    private function advanceToNextPlayer(Auction $auction): void
    {
        $nextPlayerId = Player::query()
            ->where('tournament_id', $this->tournament->id)
            ->where('status', 'available')
            ->orderBy('id')
            ->value('id');

        if (! $nextPlayerId) {
            $recycledUnsold = Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'unsold')
                ->update(['status' => 'available']);

            if ($recycledUnsold > 0) {
                $nextPlayerId = Player::query()
                    ->where('tournament_id', $this->tournament->id)
                    ->where('status', 'available')
                    ->orderBy('id')
                    ->value('id');
            }
        }

        if ($nextPlayerId) {
            $auction->update([
                'current_player_id' => $nextPlayerId,
                'current_highest_team_id' => null,
                'current_bid' => 0,
                'last_bid_at' => null,
                'ends_at' => now()->addSeconds($this->tournament->auction_timer_seconds),
                'is_paused' => false,
            ]);
            return;
        }

        $auction->update([
            'current_player_id' => null,
            'current_highest_team_id' => null,
            'current_bid' => 0,
            'last_bid_at' => null,
            'ends_at' => null,
            'is_paused' => true,
        ]);
    }

    public function render()
    {
        $auction = Auction::query()
            ->with('currentPlayer:id,name,image_path', 'currentHighestTeam:id,name,logo_path,primary_color,secondary_color')
            ->where('tournament_id', $this->tournament->id)
            ->first();

        $remainingSeconds = 0;
        if ($auction?->ends_at) {
            $remainingSeconds = max((int) now()->diffInSeconds($auction->ends_at, false), 0);
        }

        return view('livewire.admin.auction.control-panel', [
            'auction' => $auction,
            'remainingSeconds' => $remainingSeconds,
            'availableCount' => Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'available')
                ->count(),
            'unsoldCount' => Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'unsold')
                ->count(),
            'leaderboard' => Team::query()
                ->where('tournament_id', $this->tournament->id)
                ->orderByDesc('squad_count')
                ->limit(5)
                ->get(['id', 'name', 'logo_path', 'primary_color', 'secondary_color', 'squad_count']),
        ]);
    }
}
