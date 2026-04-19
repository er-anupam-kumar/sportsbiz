<?php

namespace App\Livewire\Public;

use App\Models\Tournament;
use Livewire\Component;

class TournamentsIndex extends Component
{
    public function render()
    {
        $tournaments = Tournament::query()
            ->whereHas('fixtures')
            ->with(['sport:id,name'])
            ->withCount([
                'fixtures as fixtures_count',
                'fixtures as scheduled_fixtures_count' => fn ($query) => $query->where('status', 'scheduled'),
                'fixtures as live_fixtures_count' => fn ($query) => $query->where('status', 'live'),
                'fixtures as completed_fixtures_count' => fn ($query) => $query->where('status', 'completed'),
            ])
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->get(['id', 'sport_id', 'name', 'banner_path', 'status', 'starts_at']);

        return view('livewire.public.tournaments-index', [
            'tournaments' => $tournaments,
        ]);
    }
}
