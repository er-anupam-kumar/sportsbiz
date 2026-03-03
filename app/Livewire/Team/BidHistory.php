<?php

namespace App\Livewire\Team;

use App\Models\Bid;
use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.team')]
class BidHistory extends Component
{
    public int $tournamentId;

    public function mount(int $tournamentId): void
    {
        $this->tournamentId = $tournamentId;
    }

    public function render()
    {
        $team = Team::where('user_id', auth()->id())->where('tournament_id', $this->tournamentId)->firstOrFail();

        return view('livewire.team.bid-history', [
            'team' => $team,
            'bids' => Bid::with('player:id,name,image_path')
                ->where('team_id', $team->id)
                ->where('tournament_id', $this->tournamentId)
                ->latest()
                ->paginate(20),
        ]);
    }
}
