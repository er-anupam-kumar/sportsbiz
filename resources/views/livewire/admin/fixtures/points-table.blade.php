<div class="space-y-4">
    @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h1 class="sb-page-title">Tournament Points Table</h1>
            <p class="sb-page-subtitle">{{ $tournament->name }} - Editable standings control.</p>
            <p class="text-xs text-slate-500 mt-1">
                Last Updated: {{ $lastUpdatedAt ? \Carbon\Carbon::parse($lastUpdatedAt)->format('d M Y, h:i A') : 'Not saved yet' }}
            </p>
        </div>
        <a href="{{ route('admin.fixtures.index') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-700 text-sm">Back to Fixture Planner</a>
    </div>

    <div class="sb-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="sb-table-head text-left border-b">
                <tr>
                    <th class="sb-table-cell">Team</th>
                    <th class="sb-table-cell">P</th>
                    <th class="sb-table-cell">W</th>
                    <th class="sb-table-cell">L</th>
                    <th class="sb-table-cell">T</th>
                    <th class="sb-table-cell">NR</th>
                    <th class="sb-table-cell">PTS</th>
                    <th class="sb-table-cell">NRR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $row)
                    <tr class="border-b last:border-b-0">
                        <td class="sb-table-cell font-medium text-slate-800">{{ $row['team_name'] }}</td>
                        <td class="sb-table-cell"><input type="number" min="0" wire:model="rows.{{ $i }}.played" class="sb-input min-w-[72px]"></td>
                        <td class="sb-table-cell"><input type="number" min="0" wire:model="rows.{{ $i }}.won" class="sb-input min-w-[72px]"></td>
                        <td class="sb-table-cell"><input type="number" min="0" wire:model="rows.{{ $i }}.lost" class="sb-input min-w-[72px]"></td>
                        <td class="sb-table-cell"><input type="number" min="0" wire:model="rows.{{ $i }}.tied" class="sb-input min-w-[72px]"></td>
                        <td class="sb-table-cell"><input type="number" min="0" wire:model="rows.{{ $i }}.no_result" class="sb-input min-w-[72px]"></td>
                        <td class="sb-table-cell"><input type="number" min="0" wire:model="rows.{{ $i }}.points" class="sb-input min-w-[72px]"></td>
                        <td class="sb-table-cell"><input type="number" step="0.001" wire:model="rows.{{ $i }}.net_run_rate" class="sb-input min-w-[96px]" placeholder="0.000"></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-4 text-center text-slate-500">No teams found for this tournament.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-end">
        <button wire:click="save" wire:loading.attr="disabled" wire:target="save" class="px-4 py-2 sb-btn-primary disabled:opacity-60 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="save">Save Points Table</span>
            <span wire:loading wire:target="save">Saving...</span>
        </button>
    </div>
</div>
