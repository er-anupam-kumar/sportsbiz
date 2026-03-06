<?php

namespace App\Livewire\Team;

use App\Exceptions\AuctionException;
use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use App\Services\AuctionEngine;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.team')]
class AuctionRoom extends Component
{
    public int $tournamentId;
    public ?int $teamId = null;
    public ?string $error = null;

    public function mount(int $tournamentId): void
    {
        $this->tournamentId = $tournamentId;
        $this->teamId = Team::query()
            ->where('user_id', auth()->id())
            ->where('tournament_id', $this->tournamentId)
            ->value('id');
    }

    public function placeBid(AuctionEngine $auctionEngine): void
    {
        $team = Team::query()->where('user_id', auth()->id())->where('tournament_id', $this->tournamentId)->firstOrFail();
        $auction = Auction::query()->where('tournament_id', $this->tournamentId)->firstOrFail();

        try {
            $auctionEngine->placeBid($team->id, (int) $auction->current_player_id);
            $this->error = null;
        } catch (AuctionException $exception) {
            $this->error = $exception->getMessage();
        }
    }

    public function getListeners(): array
    {
        return [
            "echo-presence:tournament.{$this->tournamentId},BidPlaced" => 'handleBidPlaced',
            "echo-presence:tournament.{$this->tournamentId},TimerExtended" => 'handleAuctionActivity',
            "echo-presence:tournament.{$this->tournamentId},PlayerSold" => 'handleAuctionActivity',
            "echo-presence:tournament.{$this->tournamentId},AuctionStarted" => 'handleAuctionActivity',
            "echo-presence:tournament.{$this->tournamentId},AuctionPaused" => 'handleAuctionActivity',
            "echo-presence:tournament.{$this->tournamentId},PlayerShuffled" => 'handlePlayerShuffled',
        ];
    }

    public function handleBidPlaced(array $payload = []): void
    {
        if ($this->teamId && isset($payload['team_id']) && (int) $payload['team_id'] === (int) $this->teamId) {
            return;
        }

        $this->dispatch('auction-activity');
    }

    public function handleAuctionActivity(): void
    {
        $this->dispatch('auction-activity');
    }

    public function handlePlayerShuffled(): void
    {
        $this->dispatch('auction-activity');
        $this->dispatch('auction-player-shuffled');
    }

    public function render()
    {
        $auction = Auction::with('currentPlayer', 'currentHighestTeam')
            ->where('tournament_id', $this->tournamentId)
            ->first();

        $remainingSeconds = 0;
        if ($auction?->ends_at) {
            if ($auction->is_paused && $auction->updated_at) {
                $remainingSeconds = max((int) $auction->updated_at->diffInSeconds($auction->ends_at, false), 0);
            } else {
                $remainingSeconds = max((int) now()->diffInSeconds($auction->ends_at, false), 0);
            }
        }

        return view('livewire.team.auction-room', [
            'auction' => $auction,
            'remainingSeconds' => $remainingSeconds,
            'team' => Team::where('user_id', auth()->id())->where('tournament_id', $this->tournamentId)->first(),
            'soldPlayers' => Player::query()
                ->where('tournament_id', $this->tournamentId)
                ->where('status', 'sold')
                ->latest('updated_at')
                ->limit(8)
                ->get(['id', 'name', 'image_path', 'final_price']),
            'leaderboard' => Team::query()
                ->where('tournament_id', $this->tournamentId)
                ->orderByDesc('squad_count')
                ->limit(6)
                ->get(['id', 'name', 'logo_path', 'primary_color', 'secondary_color', 'squad_count']),
        ]);
    }
}
