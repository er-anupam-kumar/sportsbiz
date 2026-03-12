<div class="space-y-4">
    <h1 class="text-2xl font-bold">Team Dashboard</h1>
    @if($team)
        <div class="sb-card p-4 border-t-4 border-emerald-700 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img src="{{ $team->logo_url }}" alt="{{ $team->name }} logo" class="h-11 w-11 rounded-lg object-cover border border-slate-200">
                <div>
                    <div class="font-semibold text-slate-800">{{ $team->name }}</div>
                    <div class="text-sm text-slate-600">Wallet: {{ number_format($team->wallet_balance, 2) }} | Squad: {{ $team->squad_count }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
                <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
            </div>
        </div>
    @else
        <div class="bg-amber-50 text-amber-700 p-3 rounded">No team profile mapped to this login.</div>
    @endif
</div>
