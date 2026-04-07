<?php

namespace App\Livewire\Public;

use App\Models\Fixture;
use App\Models\Tournament;
use Livewire\Component;

class MatchDetails extends Component
{
    public Tournament $tournament;
    public Fixture $fixture;

    public function mount(Tournament $tournament, Fixture $fixture): void
    {
        if ((int) $fixture->tournament_id !== (int) $tournament->id) {
            abort(404);
        }

        $this->tournament = $tournament;
        $this->fixture = $fixture;
    }

    public function render()
    {
        $this->tournament->loadMissing('sport:id,name,slug');

        $fixture = Fixture::query()
            ->whereKey($this->fixture->id)
            ->with([
                'homeTeam:id,name,logo_path,primary_color,secondary_color',
                'awayTeam:id,name,logo_path,primary_color,secondary_color',
                'homeSourceFixture:id,match_label',
                'awaySourceFixture:id,match_label',
                'homeTeam.soldPlayers' => fn ($query) => $query
                    ->where('tournament_id', $this->tournament->id)
                    ->with('category:id,name')
                    ->orderBy('name')
                    ->select(['id', 'tournament_id', 'sold_team_id', 'category_id', 'name', 'final_price', 'image_path']),
                'awayTeam.soldPlayers' => fn ($query) => $query
                    ->where('tournament_id', $this->tournament->id)
                    ->with('category:id,name')
                    ->orderBy('name')
                    ->select(['id', 'tournament_id', 'sold_team_id', 'category_id', 'name', 'final_price', 'image_path']),
            ])
            ->firstOrFail();

            $scoreboard = $this->buildScoreboard($fixture);

        $feedsInto = Fixture::query()
            ->where('tournament_id', $this->tournament->id)
            ->where(function ($query) use ($fixture) {
                $query->where('home_source_fixture_id', $fixture->id)
                    ->orWhere('away_source_fixture_id', $fixture->id);
            })
            ->orderBy('match_at')
            ->get(['id', 'match_label']);

        return view('livewire.public.match-details', [
            'tournament' => $this->tournament,
            'fixture' => $fixture,
            'feedsInto' => $feedsInto,
            'scoreboard' => $scoreboard,
        ]);
    }

    private function buildScoreboard(Fixture $fixture): array
    {
        $sportText = strtolower((string) ($this->tournament->sport?->slug ?: $this->tournament->sport?->name ?: ''));
        $isCricket = str_contains($sportText, 'cricket');

        if ($isCricket) {
            $payload = (array) ($fixture->score_payload ?? []);
            $innings = (array) ($payload['innings'] ?? []);
            $meta = (array) ($payload['meta'] ?? []);

            $formatScore = function (array $in): string {
                $runs = (int) ($in['runs'] ?? 0);
                $wickets = (int) ($in['wickets'] ?? 0);
                $overs = (int) ($in['overs'] ?? 0);
                $balls = (int) ($in['balls'] ?? 0);
                return $runs.'/'.$wickets.' ('.$overs.'.'.$balls.')';
            };

            $teamNameById = [
                (int) ($fixture->homeTeam?->id ?? 0) => $fixture->home_display_name,
                (int) ($fixture->awayTeam?->id ?? 0) => $fixture->away_display_name,
            ];

            $inningsSummary = [];
            foreach ([1, 2] as $i) {
                $in = (array) ($innings[$i] ?? []);
                if (empty($in)) {
                    continue;
                }

                $battingTeamId = (int) ($in['batting_team_id'] ?? 0);
                $inningsSummary[] = [
                    'label' => 'Innings '.$i,
                    'team' => $teamNameById[$battingTeamId] ?? 'TBD',
                    'score' => $formatScore($in),
                ];
            }

            $hasData = ! empty($inningsSummary)
                || ! empty($meta['recent_events'])
                || ! empty($fixture->result_text)
                || ! empty($fixture->notes);

            $currentInnings = (int) ($payload['current_innings'] ?? 1);
            $scorecardStats = $this->buildCricketScorecardStats($fixture, $currentInnings, (array) ($meta['ball_history'] ?? []));

            return [
                'isCricket' => true,
                'hasData' => $hasData,
                'innings' => $inningsSummary,
                'currentInnings' => $currentInnings,
                'toss' => (array) ($meta['toss'] ?? []),
                'lineup' => (array) ($meta['lineup'] ?? []),
                'targetRuns' => $meta['target_runs'] ?? null,
                'striker' => $meta['striker'] ?? null,
                'nonStriker' => $meta['non_striker'] ?? null,
                'bowler' => $meta['bowler'] ?? null,
                'events' => array_slice((array) ($meta['recent_events'] ?? []), 0, 6),
                'overBreakdown' => (array) ($meta['over_breakdown'] ?? []),
                'partnerships' => (array) ($meta['partnerships'] ?? []),
                'battingStats' => $scorecardStats['batters'],
                'bowlingStats' => $scorecardStats['bowlers'],
                'resultText' => $fixture->result_text,
                'progressNote' => $fixture->notes,
            ];
        }

        $hasPoints = $fixture->home_points !== null || $fixture->away_points !== null || $fixture->result_text || $fixture->notes;

        return [
            'isCricket' => false,
            'hasData' => $hasPoints,
            'homePoints' => (int) ($fixture->home_points ?? 0),
            'awayPoints' => (int) ($fixture->away_points ?? 0),
            'resultText' => $fixture->result_text,
            'progressNote' => $fixture->notes,
        ];
    }

    private function buildCricketScorecardStats(Fixture $fixture, int $innings, array $ballHistory): array
    {
        $homeTeamId = (int) ($fixture->homeTeam?->id ?? 0);
        $awayTeamId = (int) ($fixture->awayTeam?->id ?? 0);
        $playerNameById = [];

        foreach (($fixture->homeTeam?->soldPlayers ?? collect()) as $player) {
            $playerNameById[(int) $player->id] = (string) $player->name;
        }
        foreach (($fixture->awayTeam?->soldPlayers ?? collect()) as $player) {
            $playerNameById[(int) $player->id] = (string) $player->name;
        }

        $batters = [];
        $bowlers = [];

        foreach (array_reverse($ballHistory) as $ball) {
            if ((int) ($ball['inning'] ?? 0) !== $innings) {
                continue;
            }

            $strikerId = (int) ($ball['striker_player_id'] ?? 0);
            $bowlerId = (int) ($ball['bowler_player_id'] ?? 0);
            $runsOffBat = (int) ($ball['runs_off_bat'] ?? 0);
            $extraRuns = (int) ($ball['extra_runs'] ?? 0);
            $extraType = (string) ($ball['extra_type'] ?? 'none');
            $isLegal = (bool) ($ball['legal_ball'] ?? false);
            $isWicket = (bool) ($ball['is_wicket'] ?? false);
            $outPlayerId = (int) ($ball['out_player_id'] ?? 0);
            $wicketType = (string) ($ball['wicket_type'] ?? '');

            if ($strikerId > 0) {
                if (! isset($batters[$strikerId])) {
                    $batters[$strikerId] = [
                        'name' => $playerNameById[$strikerId] ?? ('Player #'.$strikerId),
                        'runs' => 0,
                        'balls' => 0,
                        'fours' => 0,
                        'sixes' => 0,
                        'out' => false,
                        'dismissal' => null,
                    ];
                }

                $batters[$strikerId]['runs'] += $runsOffBat;
                if ($isLegal) {
                    $batters[$strikerId]['balls'] += 1;
                }
                if ($runsOffBat === 4) {
                    $batters[$strikerId]['fours'] += 1;
                }
                if ($runsOffBat === 6) {
                    $batters[$strikerId]['sixes'] += 1;
                }
            }

            if ($outPlayerId > 0) {
                $dismissal = strtoupper(str_replace('_', ' ', $wicketType !== '' ? $wicketType : 'out'));
                if (! isset($batters[$outPlayerId])) {
                    $batters[$outPlayerId] = [
                        'name' => $playerNameById[$outPlayerId] ?? ('Player #'.$outPlayerId),
                        'runs' => 0,
                        'balls' => 0,
                        'fours' => 0,
                        'sixes' => 0,
                        'out' => true,
                        'dismissal' => $dismissal,
                    ];
                } else {
                    $batters[$outPlayerId]['out'] = true;
                    $batters[$outPlayerId]['dismissal'] = $dismissal;
                }
            }

            if ($bowlerId > 0) {
                if (! isset($bowlers[$bowlerId])) {
                    $bowlers[$bowlerId] = [
                        'name' => $playerNameById[$bowlerId] ?? ('Player #'.$bowlerId),
                        'balls' => 0,
                        'runs' => 0,
                        'wickets' => 0,
                        'wides' => 0,
                        'no_balls' => 0,
                    ];
                }

                $creditedExtras = in_array($extraType, ['wide', 'no_ball'], true) ? $extraRuns : 0;
                $bowlers[$bowlerId]['runs'] += ($runsOffBat + $creditedExtras);
                if ($isLegal) {
                    $bowlers[$bowlerId]['balls'] += 1;
                }
                if ($extraType === 'wide') {
                    $bowlers[$bowlerId]['wides'] += $extraRuns;
                }
                if ($extraType === 'no_ball') {
                    $bowlers[$bowlerId]['no_balls'] += $extraRuns;
                }
                if ($isWicket && ! in_array($wicketType, ['run_out', 'retired_out', 'other'], true)) {
                    $bowlers[$bowlerId]['wickets'] += 1;
                }
            }
        }

        $batters = array_map(static function (array $row): array {
            $balls = max(1, (int) $row['balls']);
            $row['strike_rate'] = round(((int) $row['runs'] * 100) / $balls, 2);
            return $row;
        }, $batters);
        uasort($batters, static fn (array $a, array $b): int => (int) $b['runs'] <=> (int) $a['runs']);

        $bowlers = array_map(static function (array $row): array {
            $balls = (int) $row['balls'];
            $row['overs'] = intdiv($balls, 6).'.'.($balls % 6);
            $row['economy'] = $balls > 0 ? round(((int) $row['runs'] / $balls) * 6, 2) : 0.0;
            return $row;
        }, $bowlers);
        uasort($bowlers, static function (array $a, array $b): int {
            $w = (int) $b['wickets'] <=> (int) $a['wickets'];
            return $w !== 0 ? $w : ((int) $a['runs'] <=> (int) $b['runs']);
        });

        return [
            'batters' => array_values($batters),
            'bowlers' => array_values($bowlers),
        ];
    }
}
