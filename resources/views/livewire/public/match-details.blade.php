<div class="min-h-screen sb-shell-bg p-4 md:p-6 space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500">{{ $tournament->name }}</p>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900">{{ $fixture->display_label }}</h1>
            <p class="text-sm text-slate-600">Public match details</p>
        </div>
        <a href="{{ route('public.tournaments.show', $tournament->id) }}" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-700 text-sm">Back to Fixtures</a>
    </div>

    <div class="sb-card p-2 bg-white border border-slate-200 shadow-sm">
        <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-32 md:h-40 rounded-lg object-cover border border-slate-200">
    </div>

    <div class="sb-card p-3 bg-gradient-to-r from-sky-100 to-indigo-100 border border-sky-200 shadow-sm space-y-2">
        <div class="text-xs font-semibold uppercase tracking-wide text-sky-900">Match Overview</div>
        <div class="grid md:grid-cols-2 gap-2 text-xs">
            <div class="rounded-md border border-white/80 bg-white px-2 py-1.5 flex items-center gap-2">
                <img src="{{ $fixture->homeTeam?->logo_url ?? asset('images/team-placeholder.svg') }}" alt="{{ $fixture->home_display_name }}" class="h-6 w-6 rounded-full object-cover border border-slate-200">
                <div><span class="text-slate-600">Home:</span> <span class="font-semibold text-slate-900">{{ $fixture->home_display_name }}</span></div>
            </div>
            <div class="rounded-md border border-white/80 bg-white px-2 py-1.5 flex items-center gap-2">
                <img src="{{ $fixture->awayTeam?->logo_url ?? asset('images/team-placeholder.svg') }}" alt="{{ $fixture->away_display_name }}" class="h-6 w-6 rounded-full object-cover border border-slate-200">
                <div><span class="text-slate-600">Away:</span> <span class="font-semibold text-slate-900">{{ $fixture->away_display_name }}</span></div>
            </div>
            <div class="rounded-md border border-white/80 bg-white px-2 py-1.5"><span class="text-slate-600">Date/Time:</span> <span class="font-semibold text-slate-900">{{ optional($fixture->match_at)->format('d M Y, h:i A') ?: '-' }}</span></div>
            <div class="rounded-md border border-white/80 bg-white px-2 py-1.5"><span class="text-slate-600">Venue:</span> <span class="font-semibold text-slate-900">{{ $fixture->venue ?: '-' }}</span></div>
            <div class="rounded-md border border-white/80 bg-white px-2 py-1.5 md:col-span-2"><span class="text-slate-600">Status:</span> <span class="font-semibold text-slate-900">{{ strtoupper($fixture->status) }}</span></div>
        </div>
    </div>

    <div class="sb-card p-3 bg-emerald-50 border border-emerald-200 shadow-sm space-y-2">
        <div class="text-xs font-semibold uppercase tracking-wide text-emerald-900">Current Score & Progress</div>

        @if($scoreboard['hasData'])
            @if($scoreboard['isCricket'])
                @php
                    $toss = (array) ($scoreboard['toss'] ?? []);
                    $lineup = (array) ($scoreboard['lineup'] ?? []);
                    $teamNames = [
                        (int) ($fixture->homeTeam?->id ?? 0) => $fixture->home_display_name,
                        (int) ($fixture->awayTeam?->id ?? 0) => $fixture->away_display_name,
                    ];
                    $tossWinnerName = $teamNames[(int) ($toss['winner_team_id'] ?? 0)] ?? null;
                    $battingTeamName = $teamNames[(int) ($lineup['batting_team_id'] ?? 0)] ?? null;
                    $bowlingTeamName = $teamNames[(int) ($lineup['bowling_team_id'] ?? 0)] ?? null;
                    $tossDecision = (string) ($toss['decision'] ?? '');
                    $in1 = $scoreboard['innings'][0] ?? null;
                    $in2 = $scoreboard['innings'][1] ?? null;
                @endphp

                <div class="rounded-lg border border-emerald-300 bg-gradient-to-r from-emerald-100 to-teal-100 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <div class="font-bold text-emerald-900">{{ $in1['team'] ?? $fixture->home_display_name }}: {{ $in1['score'] ?? '0/0 (0.0)' }}</div>
                        <div class="text-emerald-700 font-semibold">vs</div>
                        <div class="font-bold text-emerald-900">{{ $in2['team'] ?? $fixture->away_display_name }}: {{ $in2['score'] ?? '0/0 (0.0)' }}</div>
                    </div>
                </div>

                @if($tossWinnerName)
                    <div class="rounded-md border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-700">
                        Toss: <span class="font-semibold text-slate-900">{{ $tossWinnerName }}</span>
                        @if($tossDecision !== '')
                            won and chose to <span class="font-semibold text-slate-900">{{ strtoupper($tossDecision) }}</span> first.
                        @endif
                        @if($battingTeamName || $bowlingTeamName)
                            <span class="block mt-1">Batting: <span class="font-semibold text-slate-900">{{ $battingTeamName ?: '-' }}</span> | Bowling: <span class="font-semibold text-slate-900">{{ $bowlingTeamName ?: '-' }}</span></span>
                        @endif
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-2">
                    @foreach($scoreboard['innings'] as $inning)
                        <div class="rounded-md border border-emerald-200 bg-white px-2.5 py-2">
                            <div class="text-[10px] uppercase tracking-wide text-slate-500">{{ $inning['label'] }}</div>
                            <div class="text-sm font-bold text-slate-900">{{ $inning['team'] }}</div>
                            <div class="text-sm font-semibold text-emerald-800">{{ $inning['score'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2 text-xs">
                    <div class="rounded-md border border-slate-200 bg-white px-2 py-1.5">Target: <span class="font-semibold text-slate-900">{{ $scoreboard['targetRuns'] ?: '-' }}</span></div>
                    <div class="rounded-md border border-slate-200 bg-white px-2 py-1.5">Striker: <span class="font-semibold text-slate-900">{{ $scoreboard['striker'] ?: '-' }}</span></div>
                    <div class="rounded-md border border-slate-200 bg-white px-2 py-1.5">Non-Striker: <span class="font-semibold text-slate-900">{{ $scoreboard['nonStriker'] ?: '-' }}</span></div>
                    <div class="rounded-md border border-slate-200 bg-white px-2 py-1.5">Bowler: <span class="font-semibold text-slate-900">{{ $scoreboard['bowler'] ?: '-' }}</span></div>
                </div>

                @if(!empty($scoreboard['events']))
                    <div class="space-y-1">
                        @foreach($scoreboard['events'] as $event)
                            <div class="rounded-md border border-emerald-200 bg-white px-2 py-1.5 text-xs text-slate-700">{{ $event }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-2">
                    <div class="rounded-md border border-slate-200 bg-white px-2.5 py-2 space-y-1">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-600">Over-by-Over</div>
                        @php
                            $currentInningsForOver = max(1, (int) ($scoreboard['currentInnings'] ?? 1));
                        @endphp
                        @forelse(($scoreboard['overBreakdown'][$currentInningsForOver] ?? []) as $overNo => $over)
                            <div class="text-xs text-slate-700 flex items-center justify-between">
                                <span>Over {{ (int) $overNo + 1 }}</span>
                                <span class="font-semibold">{{ $over['runs'] ?? 0 }} runs, {{ $over['wickets'] ?? 0 }} wkts</span>
                            </div>
                        @empty
                            <div class="text-xs text-slate-500">No over breakdown yet.</div>
                        @endforelse
                    </div>

                    <div class="rounded-md border border-slate-200 bg-white px-2.5 py-2 space-y-1">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-600">Partnership</div>
                        @php
                            $p = $scoreboard['partnerships'][$currentInningsForOver] ?? ['current_runs' => 0, 'current_balls' => 0, 'stands' => []];
                        @endphp
                        <div class="text-xs text-emerald-800 font-semibold">Current: {{ $p['current_runs'] ?? 0 }} ({{ $p['current_balls'] ?? 0 }} balls)</div>
                        @if(!empty($p['stands']))
                            <div class="space-y-1">
                                @foreach(array_slice(array_reverse($p['stands']), 0, 3) as $stand)
                                    <div class="text-xs text-slate-700 flex items-center justify-between">
                                        <span>Wicket {{ $stand['at_wicket'] ?? '-' }}</span>
                                        <span class="font-semibold">{{ $stand['runs'] ?? 0 }} ({{ $stand['balls'] ?? 0 }})</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-2">
                    <div class="rounded-md border border-slate-200 bg-white px-2.5 py-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-600 mb-1">Batting Scorecard</div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr class="text-left text-slate-500 border-b border-slate-200">
                                        <th class="py-1 pr-2">Batter</th>
                                        <th class="py-1 pr-2">R</th>
                                        <th class="py-1 pr-2">B</th>
                                        <th class="py-1 pr-2">4s</th>
                                        <th class="py-1 pr-2">6s</th>
                                        <th class="py-1 pr-2">SR</th>
                                        <th class="py-1">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($scoreboard['battingStats'] ?? []) as $row)
                                        <tr class="border-b border-slate-100 text-slate-700">
                                            <td class="py-1 pr-2 font-medium text-slate-900">{{ $row['name'] }}</td>
                                            <td class="py-1 pr-2">{{ $row['runs'] }}</td>
                                            <td class="py-1 pr-2">{{ $row['balls'] }}</td>
                                            <td class="py-1 pr-2">{{ $row['fours'] }}</td>
                                            <td class="py-1 pr-2">{{ $row['sixes'] }}</td>
                                            <td class="py-1 pr-2">{{ number_format((float) $row['strike_rate'], 2) }}</td>
                                            <td class="py-1">{{ $row['out'] ? ($row['dismissal'] ?: 'OUT') : 'Not Out' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-2 text-slate-500">No batting stats yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-white px-2.5 py-2">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-600 mb-1">Bowling Scorecard</div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr class="text-left text-slate-500 border-b border-slate-200">
                                        <th class="py-1 pr-2">Bowler</th>
                                        <th class="py-1 pr-2">O</th>
                                        <th class="py-1 pr-2">R</th>
                                        <th class="py-1 pr-2">W</th>
                                        <th class="py-1 pr-2">Wd</th>
                                        <th class="py-1 pr-2">Nb</th>
                                        <th class="py-1">Econ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($scoreboard['bowlingStats'] ?? []) as $row)
                                        <tr class="border-b border-slate-100 text-slate-700">
                                            <td class="py-1 pr-2 font-medium text-slate-900">{{ $row['name'] }}</td>
                                            <td class="py-1 pr-2">{{ $row['overs'] }}</td>
                                            <td class="py-1 pr-2">{{ $row['runs'] }}</td>
                                            <td class="py-1 pr-2">{{ $row['wickets'] }}</td>
                                            <td class="py-1 pr-2">{{ $row['wides'] }}</td>
                                            <td class="py-1 pr-2">{{ $row['no_balls'] }}</td>
                                            <td class="py-1">{{ number_format((float) $row['economy'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-2 text-slate-500">No bowling stats yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-md border border-emerald-200 bg-white px-3 py-2 text-sm">
                    <span class="font-semibold text-slate-900">{{ $fixture->home_display_name }}</span>
                    <span class="font-bold text-emerald-800">{{ $scoreboard['homePoints'] }}</span>
                    <span class="text-slate-400 mx-1">-</span>
                    <span class="font-bold text-emerald-800">{{ $scoreboard['awayPoints'] }}</span>
                    <span class="font-semibold text-slate-900">{{ $fixture->away_display_name }}</span>
                </div>
            @endif

            @if(!empty($scoreboard['resultText']))
                <div class="rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-2 text-sm text-indigo-900">
                    <span class="font-semibold">Result:</span> {{ $scoreboard['resultText'] }}
                </div>
            @endif

            @if(!empty($scoreboard['progressNote']))
                <div class="rounded-md border border-amber-200 bg-amber-50 px-2.5 py-2 text-sm text-amber-900">
                    <span class="font-semibold">Progress:</span> {{ $scoreboard['progressNote'] }}
                </div>
            @endif
        @else
            <p class="text-sm text-emerald-900">Scoring has not started yet. Live score and progress will appear here once scorer updates begin.</p>
        @endif
    </div>

    <div class="sb-card p-3 bg-white border border-slate-200 shadow-sm space-y-3">
        @php
            $homeSourceType = strtoupper(str_replace('_', ' ', $fixture->home_source_type ?? 'team'));
            $awaySourceType = strtoupper(str_replace('_', ' ', $fixture->away_source_type ?? 'team'));
            $homeSourceMatch = $fixture->homeSourceFixture?->match_label;
            $awaySourceMatch = $fixture->awaySourceFixture?->match_label;
            $winnerTargets = $feedsInto->map(fn ($child) => $child->match_label ?: ('Match #'.$child->id))->values();
            $hasPreviousMatch = (bool) ($homeSourceMatch || $awaySourceMatch);
        @endphp

        <h2 class="text-sm font-semibold text-slate-900">{{ $hasPreviousMatch ? 'Progression Mapping' : 'Winner Progression' }}</h2>

        <div class="rounded-md border border-slate-300 bg-slate-50 px-3 py-2 space-y-2 text-sm leading-relaxed text-slate-700">
            @if($hasPreviousMatch)
                <p>
                    In this fixture, the home slot comes from <span class="font-semibold text-slate-900">{{ $homeSourceType }}</span>
                    @if($homeSourceMatch)
                        (source match: <span class="font-semibold text-slate-900">{{ $homeSourceMatch }}</span>)
                    @endif
                    and the away slot comes from <span class="font-semibold text-slate-900">{{ $awaySourceType }}</span>
                    @if($awaySourceMatch)
                        (source match: <span class="font-semibold text-slate-900">{{ $awaySourceMatch }}</span>)
                    @endif
                    .
                </p>
            @endif

            @if($winnerTargets->isNotEmpty())
                <p>
                    The winner of <span class="font-semibold text-slate-900">{{ $fixture->display_label }}</span> will enter to
                    <span class="font-semibold text-indigo-900">{{ $winnerTargets->join(', ') }}</span>.
                </p>
            @else
                <p>
                    The winner of <span class="font-semibold text-slate-900">{{ $fixture->display_label }}</span> does not have a next mapped fixture yet.
                </p>
            @endif
        </div>
    </div>

    <div class="space-y-3">
        @php($compactLimit = 6)
        <h2 class="text-lg md:text-xl font-black text-slate-900">Squads</h2>
        <div class="grid md:grid-cols-2 gap-5">
            <div class="sb-card p-2.5 space-y-2 bg-white border border-slate-200 shadow-sm">
                <div class="flex items-center gap-3">
                    @if($fixture->homeTeam)
                        <img src="{{ $fixture->homeTeam->logo_url }}" alt="{{ $fixture->homeTeam->name }}" class="h-10 w-10 rounded-lg object-cover border border-slate-200">
                        <div>
                            <div class="text-xs text-slate-500">Home Team</div>
                            <div class="text-sm font-bold text-slate-900">{{ $fixture->homeTeam->name }}</div>
                        </div>
                    @else
                        <div>
                            <div class="text-xs text-slate-500">Home Team</div>
                            <div class="text-sm font-bold text-slate-900">{{ $fixture->home_display_name }}</div>
                        </div>
                    @endif
                </div>

                @if($fixture->homeTeam && $fixture->homeTeam->soldPlayers->isNotEmpty())
                    <div class="text-xs text-slate-500">
                        Showing {{ min($compactLimit, $fixture->homeTeam->soldPlayers->count()) }} of {{ $fixture->homeTeam->soldPlayers->count() }} players
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach($fixture->homeTeam->soldPlayers->take($compactLimit) as $player)
                            <div class="rounded-md border border-slate-300 bg-slate-100 px-2 py-1.5 flex items-center gap-2">
                                <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-8 w-8 rounded-md object-cover border border-slate-200">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-semibold text-slate-900 truncate">{{ $player->name }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $player->category?->name ?? 'Uncategorized' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($fixture->homeTeam->soldPlayers->count() > $compactLimit)
                        <details class="rounded-md border border-slate-300 bg-slate-100 p-2">
                            <summary class="cursor-pointer text-xs font-semibold text-indigo-700">Show full home squad</summary>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach($fixture->homeTeam->soldPlayers->skip($compactLimit) as $player)
                                    <div class="rounded-md border border-slate-300 bg-white px-2 py-1.5 flex items-center gap-2">
                                        <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-8 w-8 rounded-md object-cover border border-slate-200">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-semibold text-slate-900 truncate">{{ $player->name }}</div>
                                            <div class="text-xs text-slate-500 truncate">{{ $player->category?->name ?? 'Uncategorized' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                @else
                    <div class="text-sm text-slate-500">Squad not available yet.</div>
                @endif
            </div>

            <div class="sb-card p-2.5 space-y-2 bg-white border border-slate-200 shadow-sm">
                <div class="flex items-center gap-3">
                    @if($fixture->awayTeam)
                        <img src="{{ $fixture->awayTeam->logo_url }}" alt="{{ $fixture->awayTeam->name }}" class="h-10 w-10 rounded-lg object-cover border border-slate-200">
                        <div>
                            <div class="text-xs text-slate-500">Away Team</div>
                            <div class="text-sm font-bold text-slate-900">{{ $fixture->awayTeam->name }}</div>
                        </div>
                    @else
                        <div>
                            <div class="text-xs text-slate-500">Away Team</div>
                            <div class="text-sm font-bold text-slate-900">{{ $fixture->away_display_name }}</div>
                        </div>
                    @endif
                </div>

                @if($fixture->awayTeam && $fixture->awayTeam->soldPlayers->isNotEmpty())
                    <div class="text-xs text-slate-500">
                        Showing {{ min($compactLimit, $fixture->awayTeam->soldPlayers->count()) }} of {{ $fixture->awayTeam->soldPlayers->count() }} players
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach($fixture->awayTeam->soldPlayers->take($compactLimit) as $player)
                            <div class="rounded-md border border-slate-300 bg-slate-100 px-2 py-1.5 flex items-center gap-2">
                                <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-8 w-8 rounded-md object-cover border border-slate-200">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-semibold text-slate-900 truncate">{{ $player->name }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $player->category?->name ?? 'Uncategorized' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($fixture->awayTeam->soldPlayers->count() > $compactLimit)
                        <details class="rounded-md border border-slate-300 bg-slate-100 p-2">
                            <summary class="cursor-pointer text-xs font-semibold text-indigo-700">Show full away squad</summary>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach($fixture->awayTeam->soldPlayers->skip($compactLimit) as $player)
                                    <div class="rounded-md border border-slate-300 bg-white px-2 py-1.5 flex items-center gap-2">
                                        <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-8 w-8 rounded-md object-cover border border-slate-200">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-semibold text-slate-900 truncate">{{ $player->name }}</div>
                                            <div class="text-xs text-slate-500 truncate">{{ $player->category?->name ?? 'Uncategorized' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                @else
                    <div class="text-sm text-slate-500">Squad not available yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>
