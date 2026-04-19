<div class="min-h-screen sb-shell-bg p-4 md:p-6 space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-indigo-800">
                Tournament Hub
            </div>
            <h1 class="text-3xl md:text-4xl font-black leading-tight text-slate-900">All Tournaments</h1>
            <p class="text-sm md:text-base text-slate-600">Browse public tournaments, schedules, and live progress in one place.</p>
        </div>
    </div>

    @if($tournaments->isEmpty())
        <div class="sb-card p-6 text-center text-slate-500">No tournaments found.</div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($tournaments as $tournament)
                <a href="{{ route('public.tournaments.show', $tournament->id) }}" class="block sb-card p-4 space-y-3 bg-white border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition">
                    <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-32 rounded-lg object-cover border border-slate-200">

                    <div class="space-y-1">
                        <h2 class="text-lg font-black text-slate-900 truncate">{{ $tournament->name }}</h2>
                        <p class="text-xs text-slate-600 uppercase tracking-wide">{{ $tournament->sport?->name ?? '-' }}</p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-800 font-semibold">{{ strtoupper($tournament->status) }}</span>
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-semibold">Starts {{ optional($tournament->starts_at)->format('d M Y') ?: 'TBD' }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-md border border-slate-300 bg-slate-100 px-2 py-1.5">
                            <div class="text-[10px] uppercase tracking-wide text-slate-700">Total</div>
                            <div class="text-sm font-bold text-slate-900">{{ $tournament->fixtures_count }}</div>
                        </div>
                        <div class="rounded-md border border-amber-300 bg-amber-100 px-2 py-1.5">
                            <div class="text-[10px] uppercase tracking-wide text-amber-900">Scheduled</div>
                            <div class="text-sm font-bold text-amber-900">{{ $tournament->scheduled_fixtures_count }}</div>
                        </div>
                        <div class="rounded-md border border-emerald-300 bg-emerald-100 px-2 py-1.5">
                            <div class="text-[10px] uppercase tracking-wide text-emerald-900">Live</div>
                            <div class="text-sm font-bold text-emerald-900">{{ $tournament->live_fixtures_count }}</div>
                        </div>
                        <div class="rounded-md border border-indigo-300 bg-indigo-100 px-2 py-1.5">
                            <div class="text-[10px] uppercase tracking-wide text-indigo-900">Completed</div>
                            <div class="text-sm font-bold text-indigo-900">{{ $tournament->completed_fixtures_count }}</div>
                        </div>
                    </div>

                    <div class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg border border-indigo-300 text-indigo-900 text-sm font-bold bg-indigo-100">
                        Open Tournament Center
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
