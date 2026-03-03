<?php

namespace App\Livewire\Team;

use App\Exceptions\AuctionException;
use App\Models\Auction;
use App\Models\Team;
use App\Services\AuctionEngine;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.team')]
class AuctionRoom extends Component
{
    public int $tournamentId;
    public ?string $error = null;

    public function mount(int $tournamentId): void
    {
        $this->tournamentId = $tournamentId;
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
            "echo-presence:tournament.{$this->tournamentId},BidPlaced" => '$refresh',
            "echo-presence:tournament.{$this->tournamentId},TimerExtended" => '$refresh',
            "echo-presence:tournament.{$this->tournamentId},PlayerSold" => '$refresh',
            "echo-presence:tournament.{$this->tournamentId},AuctionStarted" => '$refresh',
            "echo-presence:tournament.{$this->tournamentId},AuctionPaused" => '$refresh',
            "echo-presence:tournament.{$this->tournamentId},PlayerShuffled" => 'handlePlayerShuffled',
        ];
    }

    public function handlePlayerShuffled(): void
    {
        $this->dispatch('auction-player-shuffled');
    }

    public function render()
    {
        $auction = Auction::with('currentPlayer', 'currentHighestTeam')
            ->where('tournament_id', $this->tournamentId)
            ->first();

        $remainingSeconds = 0;
        if ($auction?->ends_at) {
            $remainingSeconds = max((int) now()->diffInSeconds($auction->ends_at, false), 0);
        }

        return view('livewire.team.auction-room', [
            'auction' => $auction,
            'remainingSeconds' => $remainingSeconds,
            'team' => Team::where('user_id', auth()->id())->where('tournament_id', $this->tournamentId)->first(),
        ]);
    }
}
