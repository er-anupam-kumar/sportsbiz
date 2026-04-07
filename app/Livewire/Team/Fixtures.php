<?php

namespace App\Livewire\Team;

use App\Models\Fixture;
use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.team')]
class Fixtures extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $team = Team::query()
            ->where('user_id', (int) auth()->id())
            ->first();

        $fixtures = collect();
        $hierarchyFixtures = collect();
        $childrenMap = [];

        if ($team) {
            $fixtures = Fixture::query()
                ->where('tournament_id', $team->tournament_id)
                ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
                ->with(['homeTeam:id,name', 'awayTeam:id,name'])
                ->orderBy('match_at')
                ->paginate(15);

            $hierarchyFixtures = Fixture::query()
                ->where('tournament_id', $team->tournament_id)
                ->with(['homeTeam:id,name', 'awayTeam:id,name'])
                ->orderBy('match_at')
                ->get();

            foreach ($hierarchyFixtures as $fixture) {
                if ($fixture->home_source_fixture_id) {
                    $childrenMap[$fixture->home_source_fixture_id][] = $fixture->display_label;
                }
                if ($fixture->away_source_fixture_id) {
                    $childrenMap[$fixture->away_source_fixture_id][] = $fixture->display_label;
                }
            }
        }

        return view('livewire.team.fixtures', [
            'team' => $team,
            'fixtures' => $fixtures,
            'hierarchyFixtures' => $hierarchyFixtures,
            'childrenMap' => $childrenMap,
        ]);
    }
}
