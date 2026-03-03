<?php

namespace App\Livewire\Team;

use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.team')]
class Dashboard extends Component
{
    public function render()
    {
        $team = Team::where('user_id', auth()->id())->first();

        return view('livewire.team.dashboard', [
            'team' => $team,
        ]);
    }
}
