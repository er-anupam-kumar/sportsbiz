<div class="min-h-screen sb-shell-bg p-4 md:p-6 space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900">{{ $tournament->name }}</h1>
            <p class="text-sm text-slate-600">Tournament fixtures and match details.</p>
        </div>
        <a href="{{ route('public.tournaments.index') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-700 text-sm">Back to Tournaments</a>
    </div>

    <div class="sb-card p-3 bg-white border border-slate-200 shadow-sm">
        <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-36 md:h-44 rounded-lg object-cover border border-slate-200">
    </div>

    <div class="grid sm:grid-cols-4 gap-3">
        <div class="sb-card p-4">Total: <span class="font-bold">{{ $summary['total'] }}</span></div>
        <div class="sb-card p-4">Scheduled: <span class="font-bold">{{ $summary['scheduled'] }}</span></div>
        <div class="sb-card p-4">Live: <span class="font-bold">{{ $summary['live'] }}</span></div>
        <div class="sb-card p-4">Completed: <span class="font-bold">{{ $summary['completed'] }}</span></div>
    </div>

    <div class="max-w-sm">
        <label class="block text-sm font-medium mb-1">Status Filter</label>
        <select wire:model.live="statusFilter" class="sb-input">
            <option value="">All</option>
            <option value="scheduled">Scheduled</option>
            <option value="live">Live</option>
            <option value="completed">Completed</option>
            <option value="postponed">Postponed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    @if($fixtures->isEmpty())
        <div class="sb-card p-6 text-center text-slate-500">No fixtures found.</div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach($fixtures as $fixture)
                <a href="{{ route('public.tournaments.matches.show', ['tournament' => $tournament->id, 'fixture' => $fixture->id]) }}" class="block sb-card p-4 space-y-3 bg-white border border-slate-200 shadow-sm hover:border-emerald-300 hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-xs font-semibold text-slate-500">{{ $fixture->display_label }}</div>
                            <div class="flex items-center gap-2 text-sm md:text-base font-bold text-slate-900">
                                <img src="{{ $fixture->homeTeam?->logo_url ?? asset('images/team-placeholder.svg') }}" alt="{{ $fixture->home_display_name }}" class="h-6 w-6 rounded-full object-cover border border-slate-200">
                                <span class="truncate">{{ $fixture->home_display_name }}</span>
                                <span class="text-slate-400 text-xs">vs</span>
                                <img src="{{ $fixture->awayTeam?->logo_url ?? asset('images/team-placeholder.svg') }}" alt="{{ $fixture->away_display_name }}" class="h-6 w-6 rounded-full object-cover border border-slate-200">
                                <span class="truncate">{{ $fixture->away_display_name }}</span>
                            </div>
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

                    @if($fixture->winnerTeam)
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs text-emerald-800">
                            Winner: <span class="font-semibold">{{ $fixture->winnerTeam->name }}</span>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    {{ $fixtures->links() }}
</div>
