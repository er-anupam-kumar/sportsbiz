<div class="space-y-4">
    <h1 class="text-2xl font-bold">Bid History</h1>
    <div class="bg-white rounded-xl shadow p-4 border border-red-100 flex items-center gap-3">
        <img src="{{ $team->logo_url }}" alt="{{ $team->name }} logo" class="h-10 w-10 rounded-lg object-cover border border-slate-200">
        <div class="font-semibold text-slate-800">{{ $team->name }}</div>
        <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
        <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
    </div>
    <div class="bg-white rounded-xl shadow divide-y border border-red-100">
        @foreach($bids as $bid)
            <div class="p-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <img src="{{ $bid->player?->image_url ?? asset('images/team-placeholder.svg') }}" alt="{{ $bid->player?->name ?? 'Player' }}" class="h-8 w-8 rounded-md object-cover border border-slate-200">
                    <span>{{ $bid->player?->name }}</span>
                </div>
                <span>{{ number_format($bid->amount, 2) }}</span>
            </div>
        @endforeach
    </div>
    {{ $bids->links() }}
</div>
