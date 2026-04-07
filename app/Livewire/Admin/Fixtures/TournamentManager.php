<?php

namespace App\Livewire\Admin\Fixtures;

use App\Models\Fixture;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class TournamentManager extends Component
{
    use WithPagination;

    public Tournament $tournament;
    public ?int $editingId = null;

    public string $homeSourceType = 'team';
    public string $awaySourceType = 'team';
    public int $homeTeamId = 0;
    public int $awayTeamId = 0;
    public int $homeSourceFixtureId = 0;
    public int $awaySourceFixtureId = 0;
    public string $matchAt = '';
    public string $venue = '';
    public string $matchLabel = '';
    public string $status = 'scheduled';
    public string $notes = '';

    public function mount(Tournament $tournament): void
    {
        abort_unless((int) $tournament->admin_id === (int) auth()->id(), 403);
        $this->tournament = $tournament;
    }

    public function updatedHomeSourceType(): void
    {
        $this->homeTeamId = 0;
        $this->homeSourceFixtureId = 0;
    }

    public function updatedAwaySourceType(): void
    {
        $this->awayTeamId = 0;
        $this->awaySourceFixtureId = 0;
    }

    public function save(): void
    {
        $adminId = (int) auth()->id();

        $validated = $this->validate([
            'homeSourceType' => ['required', 'in:team,winner_of,loser_of,tbd'],
            'awaySourceType' => ['required', 'in:team,winner_of,loser_of,tbd'],
            'homeTeamId' => [
                Rule::excludeIf($this->homeSourceType !== 'team'),
                'nullable',
                'integer',
                Rule::exists('teams', 'id')->where(fn ($query) => $query
                    ->where('admin_id', $adminId)
                    ->where('tournament_id', $this->tournament->id)),
            ],
            'awayTeamId' => [
                Rule::excludeIf($this->awaySourceType !== 'team'),
                'nullable',
                'integer',
                Rule::exists('teams', 'id')->where(fn ($query) => $query
                    ->where('admin_id', $adminId)
                    ->where('tournament_id', $this->tournament->id)),
            ],
            'homeSourceFixtureId' => [
                Rule::excludeIf(! in_array($this->homeSourceType, ['winner_of', 'loser_of'], true)),
                'nullable',
                'integer',
                Rule::exists('fixtures', 'id')->where(fn ($query) => $query
                    ->where('admin_id', $adminId)
                    ->where('tournament_id', $this->tournament->id)),
            ],
            'awaySourceFixtureId' => [
                Rule::excludeIf(! in_array($this->awaySourceType, ['winner_of', 'loser_of'], true)),
                'nullable',
                'integer',
                Rule::exists('fixtures', 'id')->where(fn ($query) => $query
                    ->where('admin_id', $adminId)
                    ->where('tournament_id', $this->tournament->id)),
            ],
            'matchAt' => ['required', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'matchLabel' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:scheduled,live,completed,postponed,cancelled'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        [$homeTeamId, $homeSourceFixtureId, $homeLabel, $homeValid] = $this->resolveSlot(
            side: 'home',
            sourceType: $validated['homeSourceType'],
            teamId: (int) ($validated['homeTeamId'] ?? 0),
            sourceFixtureId: (int) ($validated['homeSourceFixtureId'] ?? 0),
        );

        [$awayTeamId, $awaySourceFixtureId, $awayLabel, $awayValid] = $this->resolveSlot(
            side: 'away',
            sourceType: $validated['awaySourceType'],
            teamId: (int) ($validated['awayTeamId'] ?? 0),
            sourceFixtureId: (int) ($validated['awaySourceFixtureId'] ?? 0),
        );

        if (! $homeValid || ! $awayValid) {
            return;
        }

        if ($homeTeamId && $awayTeamId && $homeTeamId === $awayTeamId) {
            $this->addError('awayTeamId', 'Home and away team must be different.');
            return;
        }

        $payload = [
            'admin_id' => $adminId,
            'tournament_id' => (int) $this->tournament->id,
            'home_team_id' => $homeTeamId,
            'away_team_id' => $awayTeamId,
            'home_source_type' => $validated['homeSourceType'],
            'away_source_type' => $validated['awaySourceType'],
            'home_source_fixture_id' => $homeSourceFixtureId,
            'away_source_fixture_id' => $awaySourceFixtureId,
            'home_slot_label' => $homeLabel,
            'away_slot_label' => $awayLabel,
            'match_at' => $validated['matchAt'],
            'venue' => $validated['venue'] ?: null,
            'match_label' => $validated['matchLabel'] ?: null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->editingId) {
            Fixture::query()
                ->where('admin_id', $adminId)
                ->findOrFail($this->editingId)
                ->update($payload);
            $message = 'Fixture updated.';
        } else {
            Fixture::query()->create($payload);
            $message = 'Fixture created.';
        }

        $this->resetForm();
        $this->dispatch('toast', message: $message);
    }

    private function resolveSlot(string $side, string $sourceType, int $teamId, int $sourceFixtureId): array
    {
        $slotLabel = null;

        if ($sourceType === 'team') {
            if ($teamId <= 0) {
                $this->addError($side.'TeamId', 'Please select a team.');
                return [null, null, null, false];
            }

            return [$teamId, null, null, true];
        }

        if ($sourceType === 'tbd') {
            return [null, null, 'TBD', true];
        }

        if ($sourceFixtureId <= 0) {
            $this->addError($side.'SourceFixtureId', 'Please select source fixture.');
            return [null, null, null, false];
        }

        $sourceFixture = Fixture::query()
            ->where('admin_id', (int) auth()->id())
            ->where('tournament_id', $this->tournament->id)
            ->find($sourceFixtureId);

        if (! $sourceFixture) {
            $this->addError($side.'SourceFixtureId', 'Selected source fixture is invalid.');
            return [null, null, null, false];
        }

        $prefix = $sourceType === 'winner_of' ? 'Winner of' : 'Loser of';
        $slotLabel = $prefix.' '.($sourceFixture->display_label);

        return [null, $sourceFixtureId, $slotLabel, true];
    }

    public function edit(int $fixtureId): void
    {
        $fixture = Fixture::query()
            ->where('admin_id', (int) auth()->id())
            ->where('tournament_id', $this->tournament->id)
            ->findOrFail($fixtureId);

        $this->editingId = $fixture->id;
        $this->homeSourceType = (string) ($fixture->home_source_type ?: 'team');
        $this->awaySourceType = (string) ($fixture->away_source_type ?: 'team');
        $this->homeTeamId = (int) ($fixture->home_team_id ?? 0);
        $this->awayTeamId = (int) ($fixture->away_team_id ?? 0);
        $this->homeSourceFixtureId = (int) ($fixture->home_source_fixture_id ?? 0);
        $this->awaySourceFixtureId = (int) ($fixture->away_source_fixture_id ?? 0);
        $this->matchAt = optional($fixture->match_at)->format('Y-m-d\TH:i') ?? '';
        $this->venue = (string) ($fixture->venue ?? '');
        $this->matchLabel = (string) ($fixture->match_label ?? '');
        $this->status = (string) $fixture->status;
        $this->notes = (string) ($fixture->notes ?? '');
    }

    public function delete(int $fixtureId): void
    {
        Fixture::query()
            ->where('admin_id', (int) auth()->id())
            ->where('tournament_id', $this->tournament->id)
            ->findOrFail($fixtureId)
            ->delete();

        if ($this->editingId === $fixtureId) {
            $this->resetForm();
        }

        $this->dispatch('toast', message: 'Fixture deleted.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'homeSourceType',
            'awaySourceType',
            'homeTeamId',
            'awayTeamId',
            'homeSourceFixtureId',
            'awaySourceFixtureId',
            'matchAt',
            'venue',
            'matchLabel',
            'status',
            'notes',
        ]);

        $this->homeSourceType = 'team';
        $this->awaySourceType = 'team';
        $this->status = 'scheduled';
    }

    public function render()
    {
        $adminId = (int) auth()->id();

        $allFixtures = Fixture::query()
            ->where('admin_id', $adminId)
            ->where('tournament_id', $this->tournament->id)
            ->with(['homeSourceFixture:id,match_label', 'awaySourceFixture:id,match_label', 'homeTeam:id,name', 'awayTeam:id,name'])
            ->orderBy('match_at')
            ->get();

        $childrenMap = [];
        foreach ($allFixtures as $fixture) {
            if ($fixture->home_source_fixture_id) {
                $childrenMap[$fixture->home_source_fixture_id][] = $fixture->display_label;
            }
            if ($fixture->away_source_fixture_id) {
                $childrenMap[$fixture->away_source_fixture_id][] = $fixture->display_label;
            }
        }

        return view('livewire.admin.fixtures.tournament-manager', [
            'teamsForForm' => Team::query()
                ->where('admin_id', $adminId)
                ->where('tournament_id', $this->tournament->id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'sourceFixturesForForm' => Fixture::query()
                ->where('admin_id', $adminId)
                ->where('tournament_id', $this->tournament->id)
                ->when($this->editingId, fn ($query) => $query->whereKeyNot($this->editingId))
                ->orderBy('match_at')
                ->get(['id', 'match_label']),
            'hierarchyFixtures' => $allFixtures,
            'childrenMap' => $childrenMap,
            'fixtures' => Fixture::query()
                ->where('admin_id', $adminId)
                ->where('tournament_id', $this->tournament->id)
                ->with([
                    'homeTeam:id,name',
                    'awayTeam:id,name',
                    'winnerTeam:id,name',
                ])
                ->orderBy('match_at')
                ->paginate(15),
        ]);
    }
}
