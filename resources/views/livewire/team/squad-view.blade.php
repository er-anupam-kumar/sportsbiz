<div class="space-y-4">
    <h1 class="text-2xl font-bold">Squad View</h1>
    <div class="sb-card p-4 flex items-center gap-3">
        <img src="{{ $team->logo_path ? asset('storage/'.$team->logo_path) : asset('images/team-placeholder.svg') }}" alt="{{ $team->name }} logo" class="h-10 w-10 rounded-lg object-cover border border-slate-200">
        <div class="font-semibold text-slate-800">{{ $team->name }}</div>
        <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
        <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
    </div>
    <div class="sb-card divide-y">
        @forelse($players as $player)
            <div class="p-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <img src="{{ $player->image_path ? asset('storage/'.$player->image_path) : asset('images/team-placeholder.svg') }}" alt="{{ $player->name }}" class="h-8 w-8 rounded-md object-cover border border-slate-200">
                    <span>{{ $player->name }}</span>
                </div>
                <span>{{ number_format($player->final_price, 2) }}</span>
            </div>
        @empty
            <div class="p-3 text-slate-500">No players purchased yet.</div>
        @endforelse
    </div>
</div>
