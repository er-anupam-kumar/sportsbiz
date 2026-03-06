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

        $players = Player::query()
            ->where('sold_team_id', $team->id)
            ->where('status', 'sold')
            ->with('category:id,name')
            ->orderByDesc('final_price')
            ->orderBy('name')
            ->get();

        $playersByCategory = $players->groupBy(fn (Player $player) => $player->category?->name ?: 'Uncategorized');

        return view('livewire.team.squad-view', [
            'playersByCategory' => $playersByCategory,
            'team' => $team,
        ]);
    }
}
