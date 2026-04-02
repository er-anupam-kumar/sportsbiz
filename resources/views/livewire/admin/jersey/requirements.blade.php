<div class="space-y-4">
    <h1 class="text-2xl font-bold">Jersey Requirements</h1>

    <div class="sb-card p-4 space-y-3">
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-56">
                <label class="block text-sm font-medium mb-1">Tournament</label>
                <select wire:model.live="tournamentId" class="sb-input">
                    <option value="0">Default Tournament</option>
                    @foreach($tournaments as $tournament)
                        <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($activeTournamentId > 0)
                @php $activeTournament = $tournaments->firstWhere('id', $activeTournamentId); @endphp
                <button
                    wire:click="toggleModule"
                    wire:loading.attr="disabled"
                    class="px-3 py-2 {{ $activeTournament?->jersey_module_enabled ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white' }} rounded-lg text-sm font-medium"
                >
                    {{ $activeTournament?->jersey_module_enabled ? 'Disable Jersey Module' : 'Enable Jersey Module' }}
                </button>
                <button wire:click="exportExcel" wire:loading.attr="disabled" class="px-3 py-2 bg-slate-900 text-white rounded-lg text-sm font-medium">
                    Export Excel
                </button>
            @endif
        </div>

        @if($activeTournamentId > 0)
            <div class="text-sm {{ $activeTournament?->jersey_module_enabled ? 'text-emerald-700' : 'text-red-700' }}">
                Module Status: {{ $activeTournament?->jersey_module_enabled ? 'Enabled' : 'Disabled' }}
            </div>
        @endif

        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <input type="checkbox" wire:model.live="onlyAdditionalJersey">
            Show only additional jersey requests
        </label>
        <p class="text-xs text-slate-500">Excel export follows this filter.</p>
    </div>

    <div class="sb-card overflow-x-auto">
        <table class="w-full">
            <thead class="sb-table-head text-left text-sm text-slate-700 border-b">
                <tr>
                    <th class="sb-table-cell">Team</th>
                    <th class="sb-table-cell">Tournament</th>
                    <th class="sb-table-cell">Player</th>
                    <th class="sb-table-cell">Size</th>
                    <th class="sb-table-cell">Nickname</th>
                    <th class="sb-table-cell">Jersey No</th>
                    <th class="sb-table-cell">Additional</th>
                    <th class="sb-table-cell">Additional Qty</th>
                    <th class="sb-table-cell">Submitted At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr class="border-b border-slate-100 last:border-b-0">
                        <td class="sb-table-cell">
                            <div class="flex items-center gap-3">
                                <img src="{{ $entry->team?->jersey_image_url }}" alt="{{ $entry->team?->name }} jersey" class="h-10 w-10 rounded-lg object-cover border border-slate-200">
                                <span class="text-slate-800">{{ $entry->team?->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="sb-table-cell">{{ $entry->tournament?->name ?? '-' }}</td>
                        <td class="sb-table-cell text-slate-800">{{ $entry->player_name }}</td>
                        <td class="sb-table-cell">{{ $entry->size }}</td>
                        <td class="sb-table-cell">{{ $entry->nickname ?: '-' }}</td>
                        <td class="sb-table-cell">{{ $entry->jersey_number }}</td>
                        <td class="sb-table-cell">{{ $entry->additional_jersey_required ? 'Yes' : 'No' }}</td>
                        <td class="sb-table-cell">{{ $entry->additional_jersey_required ? ($entry->additional_jersey_quantity ?: 0) : '-' }}</td>
                        <td class="sb-table-cell">{{ $entry->created_at?->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-4 text-center text-slate-500">No jersey requirements found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $entries->links() }}
</div>
