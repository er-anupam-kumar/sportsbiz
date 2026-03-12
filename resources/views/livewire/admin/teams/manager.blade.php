<div class="space-y-4">
    <h1 class="sb-page-title">Teams CRUD</h1>

    <div class="sb-card p-4 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="sb-section-title">{{ $editingId ? 'Edit Team' : 'Create Team' }}</h2>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="sb-action-chip border-slate-200 text-slate-700">Used: {{ $quota['used'] }}</span>
                <span class="sb-action-chip border-slate-200 text-slate-700">Limit: {{ $quota['limit'] }}</span>
                <span class="sb-action-chip border-amber-200 text-amber-700">Remaining: {{ $quota['remaining'] }}</span>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Tournament</label>
                <select wire:model="formTournamentId" class="sb-input">
                    <option value="0">Select Tournament</option>
                    @foreach($tournaments as $tournament)
                        <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
                    @endforeach
                </select>
                @error('formTournamentId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Team Name</label>
                <input wire:model="name" class="sb-input" placeholder="Team Name">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Team Login Email</label>
                <input wire:model="email" type="email" class="sb-input" placeholder="team@example.com">
                @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Team Login Password {{ $editingId ? '(optional)' : '' }}</label>
                <input wire:model="password" type="password" class="sb-input" placeholder="Minimum 8 characters">
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Wallet Balance</label>
                <input type="number" wire:model="walletBalance" class="sb-input" placeholder="Wallet Balance">
                @error('walletBalance') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Team Logo</label>
                <input type="file" wire:model="logo" accept="image/*" class="sb-input">
                @error('logo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @if($logo)
                    <img src="{{ $logo->temporaryUrl() }}" alt="Team logo preview" class="mt-2 h-12 w-12 rounded-lg object-cover border border-blue-100">
                @elseif($existingLogoPath)
                    <img src="{{ str_starts_with($existingLogoPath, 'http') ? $existingLogoPath : asset('storage/'.$existingLogoPath) }}" alt="Team logo" class="mt-2 h-12 w-12 rounded-lg object-cover border border-blue-100">
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Primary Color</label>
                <input type="color" wire:model="primaryColor" class="h-10 w-full border border-slate-300 rounded-lg px-2 py-1">
                @error('primaryColor') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Secondary Color</label>
                <input type="color" wire:model="secondaryColor" class="h-10 w-full border border-slate-300 rounded-lg px-2 py-1">
                @error('secondaryColor') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm font-medium pt-7"><input type="checkbox" wire:model="isLocked"> Lock team bidding</label>
        </div>
        <div class="flex gap-2">
            <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary">{{ $editingId ? 'Update' : 'Create' }}</button>
            @if($editingId)
                <button wire:click="resetForm" class="px-4 py-2 border border-slate-300 rounded-lg">Cancel</button>
            @endif
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
                            <img src="{{ $team->logo_url }}" alt="{{ $team->name }} logo" class="h-8 w-8 rounded-lg object-cover border border-blue-100" />
                        </td>
                        <td class="sb-table-cell">
                            <div class="flex items-center gap-2">
                                <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
                                <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
                            </div>
                        </td>
                        <td class="sb-table-cell">{{ number_format($team->wallet_balance, 2) }}</td>
                        <td class="sb-table-cell">{{ $team->is_locked ? 'LOCKED' : 'OPEN' }}</td>
                        <td class="sb-table-cell">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $team->id }})" class="sb-action-chip border-amber-200 text-amber-700">Edit</button>
                                <button wire:click="viewSquad({{ $team->id }})" class="sb-action-chip border-indigo-200 text-indigo-700">View Squad</button>
                                <button wire:click="toggleLock({{ $team->id }})" class="sb-action-chip border-amber-200 text-amber-700">Toggle Lock</button>
                                <button wire:click="delete({{ $team->id }})" wire:confirm="Delete this team?" class="sb-action-chip border-red-200 text-red-600">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-4 text-center text-slate-500">No teams found.</td></tr>
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
