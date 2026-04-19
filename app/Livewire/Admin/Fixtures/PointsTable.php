<?php

namespace App\Livewire\Admin\Fixtures;

use App\Models\Fixture;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentPointTable;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PointsTable extends Component
{
    public Tournament $tournament;
    public array $rows = [];

    public function mount(Tournament $tournament): void
    {
        abort_unless((int) $tournament->admin_id === (int) auth()->id(), 403);
        $this->tournament = $tournament;
        $this->loadRows();
    }

    private function loadRows(): void
    {
        $teams = Team::query()
            ->where('tournament_id', (int) $this->tournament->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $autoStatsByTeam = $this->buildAutoStatsByTeam();

        $existing = TournamentPointTable::query()
            ->where('tournament_id', (int) $this->tournament->id)
            ->get()
            ->keyBy('team_id');

        $rows = [];
        foreach ($teams as $team) {
            $row = $existing->get($team->id);
            $auto = (array) ($autoStatsByTeam[(int) $team->id] ?? []);

            $rowLooksEmpty = $row
                && (int) ($row->played ?? 0) === 0
                && (int) ($row->won ?? 0) === 0
                && (int) ($row->lost ?? 0) === 0
                && (int) ($row->tied ?? 0) === 0
                && (int) ($row->no_result ?? 0) === 0
                && (int) ($row->points ?? 0) === 0;

            $rows[] = [
                'team_id' => (int) $team->id,
                'team_name' => (string) $team->name,
                'played' => (int) (($row && ! $rowLooksEmpty) ? $row->played : ($auto['played'] ?? 0)),
                'won' => (int) (($row && ! $rowLooksEmpty) ? $row->won : ($auto['won'] ?? 0)),
                'lost' => (int) (($row && ! $rowLooksEmpty) ? $row->lost : ($auto['lost'] ?? 0)),
                'tied' => (int) (($row && ! $rowLooksEmpty) ? $row->tied : ($auto['tied'] ?? 0)),
                'no_result' => (int) (($row && ! $rowLooksEmpty) ? $row->no_result : ($auto['no_result'] ?? 0)),
                'points' => (int) (($row && ! $rowLooksEmpty) ? $row->points : ($auto['points'] ?? 0)),
                'net_run_rate' => ($row && ! $rowLooksEmpty && $row->net_run_rate !== null)
                    ? (string) $row->net_run_rate
                    : (($auto['net_run_rate'] ?? null) !== null ? (string) $auto['net_run_rate'] : ''),
            ];
        }

        $this->rows = $rows;
    }

    private function buildAutoStatsByTeam(): array
    {
        $teamIds = Team::query()
            ->where('tournament_id', (int) $this->tournament->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $stats = [];
        foreach ($teamIds as $teamId) {
            $stats[$teamId] = [
                'played' => 0,
                'won' => 0,
                'lost' => 0,
                'tied' => 0,
                'no_result' => 0,
                'points' => 0,
                'runs_for' => 0,
                'runs_against' => 0,
                'balls_faced' => 0,
                'balls_bowled' => 0,
                'net_run_rate' => null,
            ];
        }

        $fixtures = Fixture::query()
            ->where('tournament_id', (int) $this->tournament->id)
            ->where('status', 'completed')
            ->get(['home_team_id', 'away_team_id', 'winner_team_id', 'score_payload']);

        foreach ($fixtures as $fixture) {
            $homeId = (int) ($fixture->home_team_id ?? 0);
            $awayId = (int) ($fixture->away_team_id ?? 0);

            if (isset($stats[$homeId])) {
                $stats[$homeId]['played']++;
            }
            if (isset($stats[$awayId])) {
                $stats[$awayId]['played']++;
            }

            $winnerId = (int) ($fixture->winner_team_id ?? 0);
            if ($winnerId > 0 && isset($stats[$winnerId])) {
                $stats[$winnerId]['won']++;
            }

            $innings = (array) (($fixture->score_payload['innings'] ?? []) ?: []);
            $in1 = (array) ($innings[1] ?? []);
            $in2 = (array) ($innings[2] ?? []);

            $teamOneId = (int) ($in1['batting_team_id'] ?? 0);
            $teamTwoId = (int) ($in2['batting_team_id'] ?? 0);
            $teamOneRuns = (int) ($in1['runs'] ?? 0);
            $teamTwoRuns = (int) ($in2['runs'] ?? 0);

            if ($winnerId === 0 && $teamOneId > 0 && $teamTwoId > 0) {
                if ($teamOneRuns === $teamTwoRuns) {
                    if (isset($stats[$teamOneId])) {
                        $stats[$teamOneId]['tied']++;
                    }
                    if (isset($stats[$teamTwoId])) {
                        $stats[$teamTwoId]['tied']++;
                    }
                } else {
                    if (isset($stats[$teamOneId])) {
                        $stats[$teamOneId]['no_result']++;
                    }
                    if (isset($stats[$teamTwoId])) {
                        $stats[$teamTwoId]['no_result']++;
                    }
                }
            }

            foreach ([$in1, $in2] as $inning) {
                $battingTeamId = (int) ($inning['batting_team_id'] ?? 0);
                if (! isset($stats[$battingTeamId])) {
                    continue;
                }

                $runs = (int) ($inning['runs'] ?? 0);
                $balls = ((int) ($inning['overs'] ?? 0) * 6) + (int) ($inning['balls'] ?? 0);
                $opponentId = $battingTeamId === $homeId ? $awayId : ($battingTeamId === $awayId ? $homeId : 0);

                $stats[$battingTeamId]['runs_for'] += $runs;
                $stats[$battingTeamId]['balls_faced'] += max(0, $balls);

                if ($opponentId > 0 && isset($stats[$opponentId])) {
                    $stats[$opponentId]['runs_against'] += $runs;
                    $stats[$opponentId]['balls_bowled'] += max(0, $balls);
                }
            }
        }

        foreach ($stats as $teamId => $row) {
            $losses = max(0, (int) $row['played'] - (int) $row['won'] - (int) $row['tied'] - (int) $row['no_result']);
            $points = ((int) $row['won'] * 2) + (int) $row['tied'] + (int) $row['no_result'];

            $forRate = (int) $row['balls_faced'] > 0 ? ((float) $row['runs_for'] * 6) / (int) $row['balls_faced'] : null;
            $againstRate = (int) $row['balls_bowled'] > 0 ? ((float) $row['runs_against'] * 6) / (int) $row['balls_bowled'] : null;
            $nrr = ($forRate !== null && $againstRate !== null) ? round($forRate - $againstRate, 3) : null;

            $stats[$teamId]['lost'] = $losses;
            $stats[$teamId]['points'] = $points;
            $stats[$teamId]['net_run_rate'] = $nrr;
        }

        return $stats;
    }

    public function save(): void
    {
        $validTeamIds = Team::query()
            ->where('tournament_id', (int) $this->tournament->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.team_id' => ['required', 'integer', 'in:'.implode(',', $validTeamIds)],
            'rows.*.played' => ['required', 'integer', 'min:0'],
            'rows.*.won' => ['required', 'integer', 'min:0'],
            'rows.*.lost' => ['required', 'integer', 'min:0'],
            'rows.*.tied' => ['required', 'integer', 'min:0'],
            'rows.*.no_result' => ['required', 'integer', 'min:0'],
            'rows.*.points' => ['required', 'integer', 'min:0'],
            'rows.*.net_run_rate' => ['nullable', 'numeric', 'between:-999.999,999.999'],
        ]);

        DB::transaction(function (): void {
            $rowTeamIds = collect($this->rows)
                ->pluck('team_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            TournamentPointTable::query()
                ->where('tournament_id', (int) $this->tournament->id)
                ->whereNotIn('team_id', $rowTeamIds)
                ->delete();

            foreach ($this->rows as $row) {
                TournamentPointTable::query()->updateOrCreate(
                    [
                        'tournament_id' => (int) $this->tournament->id,
                        'team_id' => (int) $row['team_id'],
                    ],
                    [
                        'played' => (int) $row['played'],
                        'won' => (int) $row['won'],
                        'lost' => (int) $row['lost'],
                        'tied' => (int) $row['tied'],
                        'no_result' => (int) $row['no_result'],
                        'points' => (int) $row['points'],
                        'net_run_rate' => $row['net_run_rate'] === '' ? null : (float) $row['net_run_rate'],
                    ]
                );
            }

            $ordered = TournamentPointTable::query()
                ->where('tournament_id', (int) $this->tournament->id)
                ->orderByDesc('points')
                ->orderByDesc('net_run_rate')
                ->orderBy('team_id')
                ->get(['id']);

            $position = 1;
            foreach ($ordered as $entry) {
                TournamentPointTable::query()->whereKey((int) $entry->id)->update(['position' => $position]);
                $position++;
            }
        });

        $this->loadRows();
        $this->dispatch('toast', message: 'Points table saved.');
    }

    public function render()
    {
        $lastUpdatedAt = TournamentPointTable::query()
            ->where('tournament_id', (int) $this->tournament->id)
            ->max('updated_at');

        return view('livewire.admin.fixtures.points-table', [
            'tournament' => $this->tournament,
            'lastUpdatedAt' => $lastUpdatedAt,
        ]);
    }
}
