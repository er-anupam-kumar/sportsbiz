<div class="space-y-4">
    <h1 class="text-2xl font-bold">Squad View</h1>
    <div class="sb-card p-4 flex items-center gap-3">
        <img src="{{ $team->logo_url }}" alt="{{ $team->name }} logo" class="h-10 w-10 rounded-lg object-cover border border-slate-200">
        <div class="font-semibold text-slate-800">{{ $team->name }}</div>
        <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
        <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
    </div>
    <div class="space-y-3">
        @forelse($playersByCategory as $categoryName => $categoryPlayers)
            <div class="sb-card p-3 space-y-2">
                <div class="text-sm font-semibold text-slate-700">{{ $categoryName }}</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($categoryPlayers as $player)
                        <div class="rounded-xl border border-slate-200 bg-white p-2.5 flex items-center gap-2">
                            <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-10 w-10 rounded-lg object-cover border border-slate-200">
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-slate-900 truncate">{{ $player->name }}</div>
                                <div class="text-xs text-slate-500">{{ number_format($player->final_price, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="sb-card p-3 text-slate-500">No players purchased yet.</div>
        @endforelse
    </div>
</div>
