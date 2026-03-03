<?php

namespace App\Livewire\Public;

use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use Livewire\Component;

class AuctionViewer extends Component
{
    public int $tournamentId;
    public bool $projectorMode = false;
    public bool $darkMode = false;

    public function mount(int $tournamentId): void
    {
        $this->tournamentId = $tournamentId;
        $this->projectorMode = request()->boolean('projector');
        $this->darkMode = request()->boolean('dark');
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
        return view('livewire.public.auction-viewer', [
            'auction' => Auction::with('currentPlayer', 'currentHighestTeam')->where('tournament_id', $this->tournamentId)->first(),
            'soldPlayers' => Player::where('tournament_id', $this->tournamentId)->where('status', 'sold')->latest()->limit(10)->get(),
            'leaderboard' => Team::where('tournament_id', $this->tournamentId)->orderByDesc('squad_count')->limit(10)->get(),
            'projectorMode' => $this->projectorMode,
            'darkMode' => $this->darkMode,
        ]);
    }
}
