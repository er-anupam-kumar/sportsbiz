<div class="space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="sb-page-title">Players</h1>
            <p class="sb-page-subtitle">Manage all players from one place.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories') }}" class="px-3 py-2 border border-amber-200 rounded-lg text-amber-700 text-sm">Categories</a>
            <a href="{{ route('admin.players.create') }}" class="px-4 py-2 sb-btn-primary">+ Create Player</a>
        </div>
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

    <div class="sb-card p-4 space-y-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="sb-section-title">Bulk Import Players</h2>
                <p class="text-sm text-slate-600 mt-1">Upload CSV to import players for the selected tournament filter.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    wire:click="downloadImportTemplate"
                    wire:loading.attr="disabled"
                    wire:target="downloadImportTemplate"
                    class="px-3 py-2 border border-slate-300 rounded-lg text-slate-700 text-sm bg-white"
                >Download Sample Template</button>
                <button
                    wire:click="importPlayers"
                    wire:loading.attr="disabled"
                    wire:target="importPlayers,importFile"
                    class="px-4 py-2 sb-btn-primary"
                >Import CSV</button>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">CSV File</label>
                <input type="file" wire:model="importFile" accept=".csv,.txt" class="sb-input">
                @error('importFile') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-500 mt-2">Required headers: <code>name,serial_no,base_price</code>. Optional: <code>category,status,age,country,previous_team</code>.</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                <div class="font-semibold text-slate-700 mb-1">CSV Example</div>
                <pre class="whitespace-pre-wrap">name,serial_no,base_price,category,status,age,country,previous_team
Virat Kohli,18,2000,Batsman,available,35,India,RCB
Jasprit Bumrah,93,1800,Bowler,available,30,India,MI</pre>
            </div>
        </div>

        @if(!empty($importSummary))
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-3 text-sm text-indigo-900 space-y-1">
                <div class="font-semibold">Last Import Summary</div>
                <div>Created: {{ $importSummary['created'] ?? 0 }} | Skipped: {{ $importSummary['skipped'] ?? 0 }}</div>
                @if(!empty($importSummary['errors']))
                    <div class="text-xs text-indigo-800">Issues:</div>
                    <ul class="text-xs text-indigo-800 list-disc list-inside space-y-0.5">
                        @foreach($importSummary['errors'] as $importError)
                            <li>{{ $importError }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>

    <div class="sb-card overflow-x-auto">
        <table class="w-full">
            <thead class="sb-table-head text-left text-sm text-slate-700 border-b">
                <tr>
                    <th class="sb-table-cell">Name</th>
                    <th class="sb-table-cell">Serial</th>
                    <th class="sb-table-cell">Image</th>
                    <th class="sb-table-cell">Tournament</th>
                    <th class="sb-table-cell">Category</th>
                    <th class="sb-table-cell">Base Price</th>
                    <th class="sb-table-cell">Status</th>
                    <th class="sb-table-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($players as $player)
                    <tr class="border-b last:border-b-0">
                        <td class="sb-table-cell">{{ $player->name }}</td>
                        <td class="sb-table-cell">{{ $player->serial_no ?? '-' }}</td>
                        <td class="sb-table-cell">
                            <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-8 w-8 rounded-lg object-cover border border-slate-200">
                        </td>
                        <td class="sb-table-cell">{{ $player->tournament?->name ?? '-' }}</td>
                        <td class="sb-table-cell">{{ $player->category?->name ?? '-' }}</td>
                        <td class="sb-table-cell">{{ number_format($player->base_price, 2) }}</td>
                        <td class="sb-table-cell">{{ strtoupper($player->status) }}</td>
                        <td class="sb-table-cell">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.players.edit', $player->id) }}" class="sb-action-chip border-amber-200 text-amber-700">Edit</a>
                                <button
                                    wire:click="delete({{ $player->id }})"
                                    wire:confirm="Delete this player?"
                                    wire:loading.attr="disabled"
                                    wire:target="delete({{ $player->id }})"
                                    class="sb-action-chip {{ $player->status === 'sold' ? 'border-slate-200 text-slate-400' : 'border-red-200 text-red-600' }}"
                                    {{ $player->status === 'sold' ? 'disabled title=Sold\ players\ cannot\ be\ deleted' : '' }}
                                >
                                    <span wire:loading wire:target="delete({{ $player->id }})" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-slate-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                                    <span wire:loading.remove wire:target="delete({{ $player->id }})">{{ $player->status === 'sold' ? 'Locked' : 'Delete' }}</span>
                                </button>
                                @if($player->status === 'sold')
                                    <button
                                        type="button"
                                        wire:click="openEditAuctionModal({{ $player->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="openEditAuctionModal({{ $player->id }})"
                                        class="sb-action-chip border-amber-400 text-amber-700 bg-amber-50 hover:bg-amber-100"
                                    >
                                        <span wire:loading wire:target="openEditAuctionModal({{ $player->id }})" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-amber-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                                        <span wire:loading.remove wire:target="openEditAuctionModal({{ $player->id }})">Edit Auction</span>
                                    </button>
                                @endif
                            </div>
                        @if($editAuctionPlayerId)
                            <div class="fixed inset-0 z-[200] bg-black/40 flex items-center justify-center p-4" wire:ignore.self>
                                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                                    <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                                        <div>
                                            <h3 class="text-base font-bold text-slate-900">Edit Auction Details</h3>
                                            <p class="text-xs text-slate-500">Change team and amount for this player.</p>
                                        </div>
                                        <button class="px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700" wire:click="$set('editAuctionPlayerId', null)">Close</button>
                                    </div>
                                    <form wire:submit.prevent="saveEditAuctionDetails" class="p-4 space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Team</label>
                                            <select wire:model="editAuctionTeamId" class="sb-input">
                                                <option value="">Select Team</option>
                                                @foreach($editAuctionTeams as $team)
                                                    <option value="{{ is_array($team) ? $team['id'] : $team->id }}">{{ is_array($team) ? $team['name'] : $team->name }} (Wallet: {{ number_format(is_array($team) ? $team['wallet_balance'] : $team->wallet_balance, 2) }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Amount</label>
                                            <input type="number" wire:model="editAuctionAmount" :step="editAuctionStepUp" min="{{ $editAuctionStepUp }}" class="sb-input" />
                                            <div class="text-xs text-slate-500 mt-1">Step up: {{ number_format($editAuctionStepUp, 2) }}</div>
                                        </div>
                                        @if($editAuctionError)
                                            <div class="text-xs text-red-600">{{ $editAuctionError }}</div>
                                        @endif
                                        <div class="flex gap-2 mt-4">
                                            <button type="submit" class="px-4 py-2 sb-btn-primary" wire:loading.attr="disabled" wire:target="saveEditAuctionDetails">
                                                <span wire:loading wire:target="saveEditAuctionDetails" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                                                <span wire:loading.remove wire:target="saveEditAuctionDetails">Save</span>
                                            </button>
                                            <button type="button" class="px-4 py-2 border border-slate-300 rounded-lg" wire:click="$set('editAuctionPlayerId', null)">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-4 text-center text-slate-500">No players found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $players->links() }}
</div>
