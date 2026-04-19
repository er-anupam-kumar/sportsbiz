<?php

namespace App\Livewire\Public;

use App\Models\Auction;
use App\Models\Fixture;
use App\Models\Team;
use App\Models\TournamentPointTable;
use App\Models\Tournament;
use Livewire\Component;

class TournamentDetails extends Component
{
    public Tournament $tournament;
    public string $activeTab = 'fixtures';

    public function mount(Tournament $tournament): void
    {
        $this->tournament = $tournament;
    }

    public function render()
    {
        $fixturesBase = Fixture::query()
            ->where('tournament_id', $this->tournament->id)
            ->with([
                'homeTeam:id,name,logo_path',
                'awayTeam:id,name,logo_path',
                'winnerTeam:id,name',
                'homeSourceFixture:id,match_label',
                'awaySourceFixture:id,match_label',
            ]);

        $upcomingFixtures = (clone $fixturesBase)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('match_at')
            ->get();

        $completedFixtures = (clone $fixturesBase)
            ->where('status', 'completed')
            ->orderByDesc('match_at')
            ->get();

        $playoffFixtures = (clone $fixturesBase)
            ->orderBy('match_at')
            ->get();

        $customPoints = TournamentPointTable::query()
            ->where('tournament_id', $this->tournament->id)
            ->whereHas('team', fn ($q) => $q->where('tournament_id', $this->tournament->id))
            ->with('team:id,name,logo_path')
            ->orderBy('position')
            ->orderByDesc('points')
            ->orderByDesc('net_run_rate')
            ->get();

        $pointsLastUpdatedAt = $customPoints->max('updated_at');

        if ($customPoints->isNotEmpty()) {
            $pointsTable = $customPoints->map(fn (TournamentPointTable $row) => [
                'id' => (int) $row->team_id,
                'name' => (string) ($row->team?->name ?? 'Team'),
                'logo_url' => $row->team?->logo_url,
                'played' => (int) $row->played,
                'wins' => (int) $row->won,
                'losses' => (int) $row->lost,
                'tied' => (int) $row->tied,
                'no_result' => (int) $row->no_result,
                'points' => (int) $row->points,
                'net_run_rate' => $row->net_run_rate,
            ])->values();
        } else {
            $autoStatsByTeam = $this->buildAutoStatsByTeam();

            $pointsTable = Team::query()
                ->where('tournament_id', $this->tournament->id)
                ->orderBy('name')
                ->get(['id', 'name', 'logo_path'])
                ->map(function (Team $team) use ($autoStatsByTeam) {
                    $auto = (array) ($autoStatsByTeam[(int) $team->id] ?? []);

                    return [
                        'id' => (int) $team->id,
                        'name' => (string) $team->name,
                        'logo_url' => $team->logo_url,
                        'played' => (int) ($auto['played'] ?? 0),
                        'wins' => (int) ($auto['won'] ?? 0),
                        'losses' => (int) ($auto['lost'] ?? 0),
                        'tied' => (int) ($auto['tied'] ?? 0),
                        'no_result' => (int) ($auto['no_result'] ?? 0),
                        'points' => (int) ($auto['points'] ?? 0),
                        'net_run_rate' => $auto['net_run_rate'] ?? null,
                    ];
                })
                ->sort(function (array $a, array $b): int {
                    if ((int) $a['points'] !== (int) $b['points']) {
                        return (int) $b['points'] <=> (int) $a['points'];
                    }

                    $aNrr = $a['net_run_rate'] !== null ? (float) $a['net_run_rate'] : -INF;
                    $bNrr = $b['net_run_rate'] !== null ? (float) $b['net_run_rate'] : -INF;
                    if ($aNrr !== $bNrr) {
                        return $bNrr <=> $aNrr;
                    }

                    return strcmp((string) $a['name'], (string) $b['name']);
                })
                ->values();
        }

        $auctionCompleted = Auction::query()
            ->where('tournament_id', $this->tournament->id)
            ->value('is_completed');

        $showAuctionTab = ! ((bool) $auctionCompleted);

        return view('livewire.public.tournament-details', [
            'tournament' => $this->tournament,
            'activeTab' => $this->activeTab,
            'upcomingFixtures' => $upcomingFixtures,
            'completedFixtures' => $completedFixtures,
            'playoffFixtures' => $playoffFixtures,
            'pointsTable' => $pointsTable,
            'pointsLastUpdatedAt' => $pointsLastUpdatedAt,
            'showAuctionTab' => $showAuctionTab,
            'summary' => [
                'total' => Fixture::query()->where('tournament_id', $this->tournament->id)->count(),
                'scheduled' => Fixture::query()->where('tournament_id', $this->tournament->id)->where('status', 'scheduled')->count(),
                'live' => Fixture::query()->where('tournament_id', $this->tournament->id)->where('status', 'live')->count(),
                'completed' => Fixture::query()->where('tournament_id', $this->tournament->id)->where('status', 'completed')->count(),
            ],
        ]);
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
}
