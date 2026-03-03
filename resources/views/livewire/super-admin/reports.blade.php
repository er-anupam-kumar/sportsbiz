<div class="space-y-4">
    <div class="flex flex-wrap items-end gap-3 justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-9 w-9 object-contain rounded-lg border border-slate-200 p-1 bg-white" />
            <h1 class="text-2xl font-bold">Platform Reports</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <div>
                <label class="block text-sm font-medium mb-1">From Date</label>
                <input type="date" wire:model.live="fromDate" class="sb-input text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">To Date</label>
                <input type="date" wire:model.live="toDate" class="sb-input text-sm">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="sb-card p-4 border-t-4 border-amber-700">
            <p class="text-sm text-slate-500 flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-600"></span>Admin Accounts</p>
            <p class="text-2xl font-bold">{{ $adminCount }}</p>
        </div>
        <div class="sb-card p-4 border-t-4 border-rose-600">
            <p class="text-sm text-slate-500 flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>Tournaments (filtered)</p>
            <p class="text-2xl font-bold">{{ $tournamentCount }}</p>
        </div>
        <div class="sb-card p-4 border-t-4 border-emerald-700">
            <p class="text-sm text-slate-500 flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>Revenue (succeeded)</p>
            <p class="text-2xl font-bold">{{ number_format($revenue, 2) }}</p>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="sb-card p-4">
            <h2 class="font-semibold mb-2">Tournament Status Breakdown</h2>
            @forelse($tournamentSummary as $row)
                <div class="text-sm py-1 flex justify-between border-b last:border-b-0">
                    <span>{{ strtoupper($row->status) }}</span>
                    <span class="font-semibold">{{ $row->total }}</span>
                </div>
            @empty
                <div class="text-sm text-slate-500">No data in selected range.</div>
            @endforelse
        </div>
        <div class="sb-card p-4">
            <h2 class="font-semibold mb-2">Payments Breakdown</h2>
            @forelse($paymentSummary as $row)
                <div class="text-sm py-1 border-b last:border-b-0">
                    <div class="flex justify-between">
                        <span>{{ strtoupper($row->provider) }} / {{ strtoupper($row->status) }}</span>
                        <span class="font-semibold">{{ $row->total }}</span>
                    </div>
                    <div class="text-xs text-slate-500">Amount: {{ number_format((float) $row->amount, 2) }}</div>
                </div>
            @empty
                <div class="text-sm text-slate-500">No payment data in selected range.</div>
            @endforelse
        </div>
    </div>
</div>
