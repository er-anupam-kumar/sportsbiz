<?php

namespace App\Livewire\Public;

use App\Models\Fixture;
use App\Models\Tournament;
use Livewire\Component;

class LiveIndex extends Component
{
    public function render()
    {
        $tournaments = Tournament::query()
            ->with(['sport:id,name'])
            ->withCount([
                'fixtures as fixtures_count',
                'fixtures as upcoming_count' => fn ($query) => $query->whereIn('status', ['scheduled', 'live']),
            ])
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->get(['id', 'name', 'status', 'sport_id', 'starts_at', 'banner_path']);

        $recentFixtures = Fixture::query()
            ->with(['tournament:id,name', 'homeTeam:id,name', 'awayTeam:id,name'])
            ->orderBy('match_at')
            ->limit(25)
            ->get(['id', 'tournament_id', 'home_team_id', 'away_team_id', 'match_label', 'match_at', 'status', 'home_slot_label', 'away_slot_label']);

        return view('livewire.public.live-index', [
            'tournaments' => $tournaments,
            'recentFixtures' => $recentFixtures,
        ]);
    }
}
