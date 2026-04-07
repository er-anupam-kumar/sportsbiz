<?php

namespace App\Livewire\Public;

use App\Models\Fixture;
use App\Models\Tournament;
use Livewire\Component;
use Livewire\WithPagination;

class TournamentDetails extends Component
{
    use WithPagination;

    public Tournament $tournament;
    public string $statusFilter = '';

    public function mount(Tournament $tournament): void
    {
        $this->tournament = $tournament;
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $fixtures = Fixture::query()
            ->where('tournament_id', $this->tournament->id)
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->with(['homeTeam:id,name,logo_path', 'awayTeam:id,name,logo_path', 'winnerTeam:id,name'])
            ->orderBy('match_at')
            ->paginate(20);

        return view('livewire.public.tournament-details', [
            'tournament' => $this->tournament,
            'fixtures' => $fixtures,
            'summary' => [
                'total' => Fixture::query()->where('tournament_id', $this->tournament->id)->count(),
                'scheduled' => Fixture::query()->where('tournament_id', $this->tournament->id)->where('status', 'scheduled')->count(),
                'live' => Fixture::query()->where('tournament_id', $this->tournament->id)->where('status', 'live')->count(),
                'completed' => Fixture::query()->where('tournament_id', $this->tournament->id)->where('status', 'completed')->count(),
            ],
        ]);
    }
}
