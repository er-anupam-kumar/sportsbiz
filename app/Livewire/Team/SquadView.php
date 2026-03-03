<?php

namespace App\Livewire\Team;

use App\Models\Player;
use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.team')]
class SquadView extends Component
{
    public int $tournamentId;

    public function mount(int $tournamentId): void
    {
        $this->tournamentId = $tournamentId;
    }

    public function render()
    {
        $team = Team::where('user_id', auth()->id())->where('tournament_id', $this->tournamentId)->firstOrFail();

        return view('livewire.team.squad-view', [
            'players' => Player::where('sold_team_id', $team->id)->where('status', 'sold')->get(),
            'team' => $team,
        ]);
    }
}
