<div class="min-h-screen sb-shell-bg p-4 md:p-6 space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-indigo-800">
                Tournament Center
            </div>
            <h1 class="text-3xl md:text-4xl font-black leading-tight text-slate-900">{{ $tournament->name }}</h1>
            <p class="text-sm md:text-base text-slate-600">Fixtures, results, points, playoffs, and live updates.</p>
        </div>
        <a href="{{ route('public.tournaments.index') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">Back to Tournaments</a>
    </div>

    <div class="sb-card p-3 bg-white border border-slate-200 shadow-sm">
        <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-36 md:h-44 rounded-lg object-cover border border-slate-200">
    </div>

    <div class="grid sm:grid-cols-4 gap-3">
        <div class="rounded-md border border-slate-300 bg-slate-100 px-3 py-2">
            <div class="text-[10px] uppercase tracking-wide text-slate-700">Total</div>
            <div class="text-lg font-black text-slate-900">{{ $summary['total'] }}</div>
        </div>
        <div class="rounded-md border border-amber-300 bg-amber-100 px-3 py-2">
            <div class="text-[10px] uppercase tracking-wide text-amber-900">Scheduled</div>
            <div class="text-lg font-black text-amber-900">{{ $summary['scheduled'] }}</div>
        </div>
        <div class="rounded-md border border-emerald-300 bg-emerald-100 px-3 py-2">
            <div class="text-[10px] uppercase tracking-wide text-emerald-900">Live</div>
            <div class="text-lg font-black text-emerald-900">{{ $summary['live'] }}</div>
        </div>
        <div class="rounded-md border border-indigo-300 bg-indigo-100 px-3 py-2">
            <div class="text-[10px] uppercase tracking-wide text-indigo-900">Completed</div>
            <div class="text-lg font-black text-indigo-900">{{ $summary['completed'] }}</div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-1.5 inline-flex flex-wrap gap-1 shadow-sm">
        <button wire:click="$set('activeTab','fixtures')" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'fixtures' ? 'bg-indigo-700 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Fixtures</button>
        <button wire:click="$set('activeTab','results')" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'results' ? 'bg-indigo-700 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Results</button>
        <button wire:click="$set('activeTab','points')" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'points' ? 'bg-indigo-700 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Points Table</button>
        <button wire:click="$set('activeTab','playoffs')" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === 'playoffs' ? 'bg-indigo-700 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">Playoffs</button>
        @if($showAuctionTab)
            <a href="{{ route('public.auction-viewer', $tournament->id) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-700">Auction</a>
        @endif
    </div>

    @if($activeTab === 'fixtures')
        @if($upcomingFixtures->isEmpty())
            <div class="sb-card p-6 text-center text-slate-500">No upcoming fixtures.</div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @foreach($upcomingFixtures as $fixture)
                    <a href="{{ route('public.tournaments.matches.show', ['tournament' => $tournament->id, 'fixture' => $fixture->id]) }}" class="block sb-card p-4 space-y-3 bg-white border border-slate-200 shadow-sm hover:border-emerald-300 hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-xs font-semibold text-slate-500">{{ $fixture->display_label }}</div>
                                <div class="text-sm md:text-base font-bold text-slate-900">{{ $fixture->home_display_name }} vs {{ $fixture->away_display_name }}</div>
                            </div>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-800 font-semibold">{{ strtoupper($fixture->status) }}</span>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-1.5 text-xs">
                            <div class="rounded-md border border-slate-300 bg-slate-100 px-2 py-1">
                                <div class="text-[10px] uppercase tracking-wide text-slate-700">Match Time</div>
                                <div class="font-semibold text-slate-900">{{ optional($fixture->match_at)->format('d M Y, h:i A') ?: '-' }}</div>
                            </div>
                            <div class="rounded-md border border-slate-300 bg-slate-100 px-2 py-1">
                                <div class="text-[10px] uppercase tracking-wide text-slate-700">Venue</div>
                                <div class="font-semibold text-slate-900">{{ $fixture->venue ?: '-' }}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    @elseif($activeTab === 'results')
        @if($completedFixtures->isEmpty())
            <div class="sb-card p-6 text-center text-slate-500">No completed results yet.</div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @foreach($completedFixtures as $fixture)
                    <a href="{{ route('public.tournaments.matches.show', ['tournament' => $tournament->id, 'fixture' => $fixture->id]) }}" class="block sb-card p-4 space-y-2 bg-white border border-slate-200 shadow-sm hover:border-indigo-300">
                        @php
                            $inningsPayload = (array) (($fixture->score_payload['innings'] ?? []) ?: []);
                            $in1 = (array) ($inningsPayload[1] ?? []);
                            $in2 = (array) ($inningsPayload[2] ?? []);
                        @endphp
                        <div class="text-xs font-semibold text-slate-500">{{ $fixture->display_label }}</div>
                        <div class="text-sm font-bold text-slate-900">{{ $fixture->home_display_name }} vs {{ $fixture->away_display_name }}</div>
                        @if(!empty($in1) || !empty($in2))
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1">
                                    Inn 1: {{ (int) ($in1['runs'] ?? 0) }}/{{ (int) ($in1['wickets'] ?? 0) }} ({{ (int) ($in1['overs'] ?? 0) }}.{{ (int) ($in1['balls'] ?? 0) }})
                                </div>
                                <div class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1">
                                    Inn 2: {{ (int) ($in2['runs'] ?? 0) }}/{{ (int) ($in2['wickets'] ?? 0) }} ({{ (int) ($in2['overs'] ?? 0) }}.{{ (int) ($in2['balls'] ?? 0) }})
                                </div>
                            </div>
                        @endif
                        <div class="text-xs text-slate-600">{{ $fixture->result_text ?: 'Result pending' }}</div>
                        @if($fixture->winnerTeam)
                            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs text-emerald-800">Winner: <span class="font-semibold">{{ $fixture->winnerTeam->name }}</span></div>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    @elseif($activeTab === 'points')
        <div class="sb-card overflow-x-auto">
            <div class="px-3 pt-3 text-xs text-slate-500">
                Last Updated: {{ $pointsLastUpdatedAt ? \Carbon\Carbon::parse($pointsLastUpdatedAt)->format('d M Y, h:i A') : 'Auto calculated from results' }}
            </div>
            <table class="w-full text-sm">
                <thead class="text-left border-b bg-slate-50">
                    <tr>
                        <th class="p-3">Team</th>
                        <th class="p-3">P</th>
                        <th class="p-3">W</th>
                        <th class="p-3">L</th>
                        <th class="p-3">T</th>
                        <th class="p-3">NR</th>
                        <th class="p-3">NRR</th>
                        <th class="p-3">Pts</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pointsTable as $row)
                        <tr class="border-b last:border-b-0">
                            <td class="p-3 font-semibold">{{ $row['name'] }}</td>
                            <td class="p-3">{{ $row['played'] }}</td>
                            <td class="p-3">{{ $row['wins'] }}</td>
                            <td class="p-3">{{ $row['losses'] }}</td>
                            <td class="p-3">{{ $row['tied'] ?? 0 }}</td>
                            <td class="p-3">{{ $row['no_result'] ?? 0 }}</td>
                            <td class="p-3">{{ $row['net_run_rate'] !== null ? number_format((float) $row['net_run_rate'], 3) : '-' }}</td>
                            <td class="p-3 font-bold">{{ $row['points'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="p-4 text-center text-slate-500">No team stats yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($activeTab === 'playoffs')
        <div class="space-y-3">
            @forelse($playoffFixtures as $fixture)
                <a href="{{ route('public.tournaments.matches.show', ['tournament' => $tournament->id, 'fixture' => $fixture->id]) }}" class="block sb-card p-4 space-y-2 bg-white border border-slate-200 shadow-sm hover:border-indigo-300">
                    <div class="text-xs font-semibold text-slate-500">{{ $fixture->display_label }}</div>
                    <div class="text-sm font-bold text-slate-900">{{ $fixture->home_display_name }} vs {{ $fixture->away_display_name }}</div>
                    <div class="text-xs text-slate-600">
                        Home Source: {{ strtoupper(str_replace('_',' ', (string) $fixture->home_source_type)) }}
                        @if($fixture->homeSourceFixture)
                            ({{ $fixture->homeSourceFixture->match_label ?: ('Match #'.$fixture->homeSourceFixture->id) }})
                        @endif
                    </div>
                    <div class="text-xs text-slate-600">
                        Away Source: {{ strtoupper(str_replace('_',' ', (string) $fixture->away_source_type)) }}
                        @if($fixture->awaySourceFixture)
                            ({{ $fixture->awaySourceFixture->match_label ?: ('Match #'.$fixture->awaySourceFixture->id) }})
                        @endif
                    </div>
                </a>
            @empty
                <div class="sb-card p-6 text-center text-slate-500">No playoff mapping data.</div>
            @endforelse
        </div>
    @endif
</div>
