<style>
    @keyframes sbTickerSlide {
        0% { transform: translateX(0); }
        50% { transform: translateX(8px); }
        100% { transform: translateX(0); }
    }
    @keyframes sbCardFadeUp {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .sb-ticker-text {
        display: inline-block;
        animation: sbTickerSlide 6s ease-in-out infinite;
        white-space: nowrap;
    }
    .sb-over-scroll {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .sb-over-scroll::-webkit-scrollbar {
        display: none;
    }
    .sb-card-enter {
        opacity: 0;
        animation: sbCardFadeUp 0.5s ease-out forwards;
    }
    .sb-card-enter:nth-child(1) { animation-delay: 0.04s; }
    .sb-card-enter:nth-child(2) { animation-delay: 0.08s; }
    .sb-card-enter:nth-child(3) { animation-delay: 0.12s; }
    .sb-card-enter:nth-child(4) { animation-delay: 0.16s; }
    .sb-card-enter:nth-child(5) { animation-delay: 0.20s; }
    .sb-card-enter:nth-child(6) { animation-delay: 0.24s; }
    .sb-card-enter:nth-child(7) { animation-delay: 0.28s; }
    .sb-card-enter:nth-child(8) { animation-delay: 0.32s; }
    .sb-card-enter:nth-child(9) { animation-delay: 0.36s; }
    .sb-card-enter:nth-child(10) { animation-delay: 0.40s; }
    @media (prefers-reduced-motion: reduce) {
        .sb-ticker-text,
        .sb-card-enter {
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
        }
    }
</style>

<div class="min-h-screen sb-shell-bg p-4 md:p-6 space-y-4" x-data="{ tab: 'score' }">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-indigo-800">
                {{ $tournament->name }}
            </div>
            <h1 class="text-3xl md:text-4xl font-black leading-tight text-slate-900">{{ $fixture->display_label }}</h1>
            <p class="text-sm md:text-base text-slate-600">Live score, progression mapping, and squad details.</p>
        </div>
        <a href="{{ route('public.tournaments.show', $tournament->id) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Back to Fixtures</a>
    </div>

    <div class="sb-card p-2 bg-white border border-slate-200 shadow-sm">
        <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-32 md:h-40 rounded-lg object-cover border border-slate-200">
    </div>

    <div class="relative rounded-2xl border border-indigo-900/40 bg-gradient-to-br from-indigo-950 via-blue-900 to-indigo-900 shadow-xl overflow-hidden">
        <div class="absolute left-0 top-0 h-full w-28 md:w-40 pointer-events-none opacity-50">
            <div class="absolute left-2 top-8 h-20 w-20 rounded-full border-8 border-amber-300/60"></div>
            <div class="absolute left-12 top-24 h-16 w-16 rounded-full border-8 border-amber-200/50"></div>
            <div class="absolute left-1 bottom-10 h-14 w-14 rounded-full border-8 border-emerald-300/45"></div>
        </div>
        <div class="absolute right-0 top-0 h-full w-28 md:w-40 pointer-events-none opacity-50">
            <div class="absolute right-3 top-10 h-20 w-20 rounded-full border-8 border-orange-300/60"></div>
            <div class="absolute right-12 top-24 h-14 w-14 rounded-full border-8 border-orange-200/50"></div>
            <div class="absolute right-1 bottom-12 h-16 w-16 rounded-full border-8 border-pink-300/45"></div>
        </div>

        @if($scoreboard['isCricket'] && !empty($scoreboard['heroTeams']))
            <div class="relative z-10 px-4 py-5 md:px-6 md:py-7 text-white space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] items-center gap-3 md:gap-5">
                    <div class="flex items-center justify-between md:justify-start gap-3 min-w-0">
                        <img src="{{ $scoreboard['heroTeams'][0]['logo'] }}" alt="{{ $scoreboard['heroTeams'][0]['name'] }}" class="h-12 w-12 md:h-16 md:w-16 rounded-full border-2 border-white/50 object-cover bg-white/90 p-1">
                        <div class="min-w-0">
                            <div class="text-lg sm:text-2xl md:text-5xl font-black leading-none">{{ $scoreboard['heroTeams'][0]['score'] }}</div>
                            <div class="text-xs sm:text-sm md:text-base text-blue-100">{{ $scoreboard['heroTeams'][0]['overs'] }}</div>
                            <div class="text-[11px] md:text-xs uppercase tracking-wide text-blue-200 truncate">{{ $scoreboard['heroTeams'][0]['name'] }}</div>
                        </div>
                    </div>

                    <div class="justify-self-center px-3 py-1 rounded-md bg-white text-indigo-900 text-[11px] md:text-xs font-extrabold uppercase tracking-wide border border-indigo-200">
                        {{ $fixture->display_label }}
                    </div>

                    <div class="flex items-center justify-between md:justify-end gap-3 min-w-0 text-right">
                        <div class="min-w-0">
                            <div class="text-lg sm:text-2xl md:text-5xl font-black leading-none">{{ $scoreboard['heroTeams'][1]['score'] }}</div>
                            <div class="text-xs sm:text-sm md:text-base text-blue-100">{{ $scoreboard['heroTeams'][1]['overs'] }}</div>
                            <div class="text-[11px] md:text-xs uppercase tracking-wide text-blue-200 truncate">{{ $scoreboard['heroTeams'][1]['name'] }}</div>
                        </div>
                        <img src="{{ $scoreboard['heroTeams'][1]['logo'] }}" alt="{{ $scoreboard['heroTeams'][1]['name'] }}" class="h-12 w-12 md:h-16 md:w-16 rounded-full border-2 border-white/50 object-cover bg-white/90 p-1">
                    </div>
                </div>

                <div class="text-center text-[11px] md:text-sm text-blue-100">
                    {{ $fixture->venue ?: 'Venue TBD' }}
                    <span class="mx-1">•</span>
                    {{ optional($fixture->match_at)->format('d M Y') ?: '-' }}
                    <span class="mx-1">•</span>
                    {{ optional($fixture->match_at)->format('h:i A') ?: '-' }}
                </div>

                @if(!empty($scoreboard['recentOvers']))
                    <div class="space-y-2">
                        @foreach($scoreboard['recentOvers'] as $overRow)
                            <div class="sb-over-scroll overflow-x-auto">
                                <div class="inline-flex items-center gap-2 text-xs min-w-max px-0.5">
                                    <span class="text-blue-200 font-semibold">{{ $overRow['label'] }}</span>
                                    @foreach($overRow['balls'] as $ball)
                                        <span class="h-7 min-w-7 px-1 rounded-full inline-flex items-center justify-center border text-[11px] font-bold
                                            {{ $ball['type'] === 'wicket' ? 'bg-rose-600 border-rose-300 text-white' : '' }}
                                            {{ $ball['type'] === 'boundary' ? 'bg-emerald-600 border-emerald-300 text-white' : '' }}
                                            {{ $ball['type'] === 'extra' ? 'bg-amber-500 border-amber-300 text-slate-900' : '' }}
                                            {{ in_array($ball['type'], ['run','dot'], true) ? 'bg-transparent border-blue-300 text-blue-50' : '' }}">
                                            {{ $ball['text'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(!empty($scoreboard['resultText']))
                <div class="bg-blue-700/80 text-white text-center py-2.5 px-3 text-sm md:text-base font-semibold border-t border-white/20 overflow-hidden">
                    <span class="sb-ticker-text">{{ $scoreboard['resultText'] }}</span>
                </div>
            @endif
        @else
            <div class="px-4 py-5 md:px-6 md:py-7 text-white space-y-2">
                <div class="text-xs uppercase tracking-wide text-blue-200">Match Overview</div>
                <div class="text-xl md:text-2xl font-black">{{ $fixture->home_display_name }} vs {{ $fixture->away_display_name }}</div>
                <div class="text-sm text-blue-100">{{ $fixture->venue ?: 'Venue TBD' }} • {{ optional($fixture->match_at)->format('d M Y, h:i A') ?: '-' }}</div>
                <div class="text-sm text-blue-100">Status: {{ strtoupper($fixture->status) }}</div>
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-1.5 inline-flex flex-wrap gap-1 shadow-sm">
        <button @click="tab='score'" :class="tab==='score' ? 'bg-indigo-700 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">Scorecard</button>
        <button @click="tab='progression'" :class="tab==='progression' ? 'bg-indigo-700 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">Progression</button>
        <button @click="tab='squads'" :class="tab==='squads' ? 'bg-indigo-700 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">Teams</button>
    </div>

    <div class="sb-card p-3 bg-emerald-50 border border-emerald-200 shadow-sm space-y-2" x-show="tab === 'score'" x-cloak>
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
                        <div class="md:hidden space-y-2">
                            @forelse(($scoreboard['battingStats'] ?? []) as $row)
                                <div class="sb-card-enter rounded-xl border border-slate-300 bg-white shadow-sm px-2.5 py-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="text-xs font-bold text-slate-900 truncate">{{ $row['name'] }}</div>
                                        <div class="text-[10px] px-2 py-0.5 rounded-full {{ $row['out'] ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' }} font-semibold">{{ $row['out'] ? ($row['dismissal'] ?: 'OUT') : 'Not Out' }}</div>
                                    </div>
                                    <div class="mt-1.5 grid grid-cols-3 gap-1 text-[10px]">
                                        <div class="rounded-md bg-slate-100 border border-slate-200 px-1.5 py-1 font-medium text-slate-700">R <span class="font-bold text-slate-900">{{ $row['runs'] }}</span></div>
                                        <div class="rounded-md bg-slate-100 border border-slate-200 px-1.5 py-1 font-medium text-slate-700">B <span class="font-bold text-slate-900">{{ $row['balls'] }}</span></div>
                                        <div class="rounded-md bg-indigo-50 border border-indigo-200 px-1.5 py-1 font-medium text-indigo-700">SR <span class="font-bold text-indigo-900">{{ number_format((float) $row['strike_rate'], 2) }}</span></div>
                                        <div class="rounded-md bg-slate-100 border border-slate-200 px-1.5 py-1 font-medium text-slate-700">4s <span class="font-bold text-slate-900">{{ $row['fours'] }}</span></div>
                                        <div class="rounded-md bg-slate-100 border border-slate-200 px-1.5 py-1 font-medium text-slate-700">6s <span class="font-bold text-slate-900">{{ $row['sixes'] }}</span></div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-2 text-xs text-slate-500">No batting stats yet.</div>
                            @endforelse
                        </div>
                        <div class="hidden md:block overflow-x-auto">
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
                        <div class="md:hidden space-y-2">
                            @forelse(($scoreboard['bowlingStats'] ?? []) as $row)
                                <div class="sb-card-enter rounded-xl border border-slate-300 bg-white shadow-sm px-2.5 py-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="text-xs font-bold text-slate-900 truncate">{{ $row['name'] }}</div>
                                        <div class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200 font-semibold">Econ {{ number_format((float) $row['economy'], 2) }}</div>
                                    </div>
                                    <div class="mt-1.5 grid grid-cols-3 gap-1 text-[10px]">
                                        <div class="rounded-md bg-slate-100 border border-slate-200 px-1.5 py-1 font-medium text-slate-700">O <span class="font-bold text-slate-900">{{ $row['overs'] }}</span></div>
                                        <div class="rounded-md bg-slate-100 border border-slate-200 px-1.5 py-1 font-medium text-slate-700">R <span class="font-bold text-slate-900">{{ $row['runs'] }}</span></div>
                                        <div class="rounded-md bg-emerald-50 border border-emerald-200 px-1.5 py-1 font-medium text-emerald-700">W <span class="font-bold text-emerald-900">{{ $row['wickets'] }}</span></div>
                                        <div class="rounded-md bg-slate-100 border border-slate-200 px-1.5 py-1 font-medium text-slate-700">Wd <span class="font-bold text-slate-900">{{ $row['wides'] }}</span></div>
                                        <div class="rounded-md bg-slate-100 border border-slate-200 px-1.5 py-1 font-medium text-slate-700">Nb <span class="font-bold text-slate-900">{{ $row['no_balls'] }}</span></div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-2 text-xs text-slate-500">No bowling stats yet.</div>
                            @endforelse
                        </div>
                        <div class="hidden md:block overflow-x-auto">
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

    <div class="sb-card p-3 bg-white border border-slate-200 shadow-sm space-y-3" x-show="tab === 'progression'" x-cloak>
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

    <div class="space-y-3" x-show="tab === 'squads'" x-cloak>
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
