<div class="min-h-screen sb-shell-bg p-4 md:p-6 space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900">Tournaments</h1>
            <p class="text-sm text-slate-600">Browse all public tournaments and match schedules.</p>
        </div>
    </div>

    @if($tournaments->isEmpty())
        <div class="sb-card p-6 text-center text-slate-500">No tournaments found.</div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2.5">
            @foreach($tournaments as $tournament)
                <div class="sb-card p-2.5 space-y-2 bg-white border border-slate-200 shadow-sm">
                    <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-24 rounded-md object-cover border border-slate-200">

                    <div class="space-y-0">
                        <h2 class="text-base font-bold text-slate-900 truncate">{{ $tournament->name }}</h2>
                        <p class="text-xs text-slate-600">{{ $tournament->sport?->name ?? '-' }}</p>
                    </div>

                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-800 font-semibold">{{ strtoupper($tournament->status) }}</span>
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 font-semibold">{{ optional($tournament->starts_at)->format('d M Y') ?: 'TBD' }}</span>
                    </div>

                    <div class="space-y-1.5 text-center">
                        <div class="rounded-md border border-slate-300 bg-slate-100 px-2 py-1">
                            <div class="text-[10px] uppercase tracking-wide text-slate-700">Fixtures</div>
                            <div class="text-sm font-bold text-slate-900 leading-4">{{ $tournament->fixtures_count }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div class="rounded-md border border-emerald-300 bg-emerald-200 px-2 py-1">
                                <div class="text-[10px] uppercase tracking-wide text-emerald-900">Live</div>
                                <div class="text-sm font-bold text-emerald-900 leading-4">{{ $tournament->live_fixtures_count }}</div>
                            </div>
                            <div class="rounded-md border border-indigo-300 bg-indigo-200 px-2 py-1">
                                <div class="text-[10px] uppercase tracking-wide text-indigo-900">Completed</div>
                                <div class="text-sm font-bold text-indigo-900 leading-4">{{ $tournament->completed_fixtures_count }}</div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('public.tournaments.show', $tournament->id) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg border border-indigo-300 text-indigo-900 text-sm font-bold bg-indigo-200 hover:bg-indigo-300 shadow-sm transition">View Details</a>
                </div>
            @endforeach
        </div>
    @endif
</div>
