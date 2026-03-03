<div class="space-y-4">
    <h1 class="text-2xl font-bold">Admin Reports</h1>
    @if($activeTournament)
        <div class="sb-card p-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Active Tournament</p>
                <p class="font-bold text-slate-800">{{ $activeTournament->name }}</p>
            </div>
            <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-8 w-8 object-contain" />
        </div>
    @endif
    <div class="sb-card p-4 space-y-3">
        <div class="flex flex-wrap items-end gap-2">
            <div class="min-w-56">
                <label class="block text-sm font-medium mb-1">Tournament</label>
                <select wire:model="tournamentId" class="sb-input">
                    <option value="0">Default Tournament</option>
                    @foreach($tournaments as $tournament)
                        <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
                    @endforeach
                </select>
            </div>
            <button wire:click="queueCsvExport" wire:loading.attr="disabled" class="px-3 py-2 sb-btn-primary">Queue CSV Export</button>
            <button wire:click="queueExcelExport" wire:loading.attr="disabled" class="px-3 py-2 sb-btn-primary">Queue Excel Export</button>
            <button wire:click="queuePdfExport" wire:loading.attr="disabled" class="px-3 py-2 sb-btn-primary">Queue PDF Export</button>
        </div>
        <p class="text-sm text-slate-500">Exports are generated in queue and appear below when ready.</p>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        <div class="sb-card p-4 border-t-4 border-amber-700">Total Bids: {{ $bidCount }}</div>
        <div class="sb-card p-4 border-t-4 border-emerald-700">Wallet Debits: {{ number_format($walletDebits, 2) }}</div>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        <div class="sb-card p-4 space-y-2">
            <h2 class="font-semibold">Top Teams</h2>
            @forelse($activeTeams as $team)
                <div class="flex items-center gap-2 py-1 border-b border-slate-200/60 last:border-b-0">
                    <img src="{{ $team->logo_path ? asset('storage/'.$team->logo_path) : asset('images/team-placeholder.svg') }}" alt="{{ $team->name }} logo" class="h-7 w-7 rounded-md object-cover border border-blue-100">
                    <span class="text-sm text-slate-800">{{ $team->name }} ({{ $team->squad_count }})</span>
                    <span class="h-4 w-4 rounded-full border border-slate-200 ml-auto" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
                    <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No teams for selected tournament.</p>
            @endforelse
        </div>
        <div class="sb-card p-4 space-y-2">
            <h2 class="font-semibold">Recent Sold Players</h2>
            @forelse($activeSoldPlayers as $player)
                <div class="flex items-center gap-2 py-1 border-b border-slate-200/60 last:border-b-0">
                    <img src="{{ $player->image_path ? asset('storage/'.$player->image_path) : asset('images/team-placeholder.svg') }}" alt="{{ $player->name }}" class="h-7 w-7 rounded-md object-cover border border-blue-100">
                    <span class="text-sm text-slate-800">{{ $player->name }}</span>
                    <span class="text-sm text-slate-600 ml-auto">{{ number_format($player->final_price ?? 0, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No sold players for selected tournament.</p>
            @endforelse
        </div>
    </div>
    <div class="sb-card p-4 space-y-2">
        <h2 class="font-semibold">Ready Exports</h2>
        @forelse($readyExports as $export)
            <a href="{{ route('admin.exports.download', ['path' => $export->data['path'] ?? '']) }}" class="text-amber-700 hover:underline block">
                {{ $export->title }} - {{ strtoupper($export->data['format'] ?? 'file') }}
            </a>
        @empty
            <p class="text-sm text-slate-500">No exports ready yet.</p>
        @endforelse
    </div>
</div>
