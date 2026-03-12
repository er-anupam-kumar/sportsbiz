<div class="space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="sb-page-title">Teams</h1>
            <p class="sb-page-subtitle">Manage all teams from one place.</p>
        </div>
        <a href="{{ route('admin.teams.create') }}" class="px-4 py-2 sb-btn-primary">+ Create Team</a>
    </div>

    <div class="max-w-sm">
        <label class="block text-sm font-medium mb-1">Tournament Filter</label>
        <select wire:model.live="tournamentId" class="sb-input">
            <option value="0">All Tournaments</option>
            @foreach($tournaments as $tournament)
                <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="sb-card overflow-x-auto">
        <table class="w-full">
            <thead class="sb-table-head text-left text-sm text-slate-700 border-b">
                <tr>
                    <th class="sb-table-cell">Name</th>
                    <th class="sb-table-cell">Login Email</th>
                    <th class="sb-table-cell">Tournament</th>
                    <th class="sb-table-cell">Logo</th>
                    <th class="sb-table-cell">Colors</th>
                    <th class="sb-table-cell">Wallet</th>
                    <th class="sb-table-cell">Squad</th>
                    <th class="sb-table-cell">Lock</th>
                    <th class="sb-table-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teams as $team)
                    <tr class="border-b last:border-b-0">
                        <td class="sb-table-cell">{{ $team->name }}</td>
                        <td class="sb-table-cell">{{ $team->user?->email ?? '-' }}</td>
                        <td class="sb-table-cell">{{ $team->tournament?->name ?? '-' }}</td>
                        <td class="sb-table-cell">
                            <img src="{{ $team->logo_url }}" alt="{{ $team->name }} logo" class="h-8 w-8 rounded-lg object-cover border border-slate-200" />
                        </td>
                        <td class="sb-table-cell">
                            <div class="flex items-center gap-2">
                                <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
                                <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
                            </div>
                        </td>
                        <td class="sb-table-cell">{{ number_format($team->wallet_balance, 2) }}</td>
                        <td class="sb-table-cell">{{ $team->squad_count }}</td>
                        <td class="sb-table-cell">{{ $team->is_locked ? 'LOCKED' : 'OPEN' }}</td>
                        <td class="sb-table-cell">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="viewSquad({{ $team->id }})" class="sb-action-chip border-indigo-200 text-indigo-700">View Squad</button>
                                <a href="{{ route('admin.teams.edit', $team->id) }}" class="sb-action-chip border-amber-200 text-amber-700">Edit</a>
                                <button wire:click="toggleLock({{ $team->id }})" class="sb-action-chip border-amber-200 text-amber-700">Toggle Lock</button>
                                <button wire:click="delete({{ $team->id }})" wire:confirm="Delete this team?" class="sb-action-chip border-red-200 text-red-600">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="p-4 text-center text-slate-500">No teams found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showSquadModal)
        <div class="fixed inset-0 z-[120] bg-black/50 flex items-center justify-center p-4" wire:click.self="closeSquadModal">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ $squadTeamName }} Squad</h3>
                        <p class="text-xs text-slate-500">Sold players currently assigned to this team.</p>
                    </div>
                    <button class="px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700" wire:click="closeSquadModal">Close</button>
                </div>
                <div class="p-4 overflow-y-auto max-h-[70vh]">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse($squadPlayers as $squadPlayer)
                            <div class="rounded-xl border border-slate-200 bg-white p-3 flex items-start gap-2">
                                <img src="{{ $squadPlayer['image_url'] }}" alt="{{ $squadPlayer['name'] }}" class="h-10 w-10 rounded-lg object-cover border border-slate-200" />
                                <div class="min-w-0 text-xs">
                                    <div class="text-sm font-semibold text-slate-900 truncate">{{ $squadPlayer['name'] }}</div>
                                    <div class="text-slate-500">Serial: {{ $squadPlayer['serial_no'] ?? '-' }}</div>
                                    <div class="text-slate-500">Category: {{ $squadPlayer['category'] ?? 'Uncategorized' }}</div>
                                    <div class="text-slate-600 font-semibold">Amount: {{ number_format((float) ($squadPlayer['final_price'] ?? 0), 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">No players in squad yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{ $teams->links() }}
</div>
