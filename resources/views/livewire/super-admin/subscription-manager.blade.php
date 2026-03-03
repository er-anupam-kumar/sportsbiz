<div class="space-y-4">
    <div class="flex items-center gap-3">
        <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-9 w-9 object-contain rounded-lg border border-slate-200 p-1 bg-white" />
        <h1 class="text-2xl font-bold">Subscription Manager</h1>
    </div>

    <div class="sb-card p-4 space-y-3">
        <h2 class="font-semibold">{{ $editingId ? 'Edit Subscription' : 'Create Subscription' }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="text-sm">Admin</label>
                <select wire:model="adminId" class="sb-input">
                    <option value="0">Select Admin</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                    @endforeach
                </select>
                @error('adminId') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm">Expiry Date</label>
                <input type="date" wire:model="expiresAt" class="sb-input">
                @error('expiresAt') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm">Status</label>
                <select wire:model="isActive" class="sb-input">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div>
                <label class="text-sm">Max Tournaments</label>
                <input type="number" wire:model="maxTournaments" class="sb-input">
                @error('maxTournaments') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm">Max Teams</label>
                <input type="number" wire:model="maxTeams" class="sb-input">
                @error('maxTeams') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm">Max Players</label>
                <input type="number" wire:model="maxPlayers" class="sb-input">
                @error('maxPlayers') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex gap-2">
            <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary">Save</button>
            @if($editingId)
                <button wire:click="resetForm" class="px-4 py-2 border border-slate-300 rounded-lg">Cancel</button>
            @endif
        </div>
    </div>

    <div class="sb-card p-4 max-w-xl">
        <label class="block text-sm font-medium mb-1">Search Subscription</label>
        <input wire:model.live.debounce.400ms="search" class="sb-input" placeholder="Search by admin name/email">
    </div>

    <div class="sb-card overflow-x-auto">
        <table class="w-full">
            <thead class="text-left text-sm text-slate-700 border-b bg-gradient-to-r from-amber-50 via-rose-50 to-emerald-50">
                <tr>
                    <th class="p-3">Admin</th>
                    <th class="p-3">Limits</th>
                    <th class="p-3">Expiry</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                    <tr class="border-b last:border-b-0">
                        <td class="p-3">
                            <div class="font-medium">{{ $subscription->admin?->name }}</div>
                            <div class="text-xs text-slate-500">{{ $subscription->admin?->email }}</div>
                        </td>
                        <td class="p-3 text-sm">
                            T: {{ $subscription->max_tournaments }}
                            | Team: {{ $subscription->max_teams }}
                            | P: {{ $subscription->max_players }}
                        </td>
                        <td class="p-3">{{ $subscription->expires_at?->format('Y-m-d') }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs {{ $subscription->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-700' }}">
                                <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full {{ $subscription->is_active ? 'bg-green-600' : 'bg-slate-500' }}"></span>{{ $subscription->is_active ? 'ACTIVE' : 'INACTIVE' }}</span>
                            </span>
                        </td>
                        <td class="p-3" x-data="{open:false}">
                            <button @click="open=!open" class="px-2 py-1 border border-amber-200 rounded text-sm text-amber-700">Actions</button>
                            <div x-show="open" @click.outside="open=false" class="absolute mt-1 bg-white border rounded shadow z-10" x-cloak>
                                <button wire:click="edit({{ $subscription->id }})" class="block w-full text-left px-3 py-2 text-sm hover:bg-slate-50">Edit</button>
                                <button wire:click="toggle({{ $subscription->id }})" class="block w-full text-left px-3 py-2 text-sm hover:bg-slate-50">Toggle Status</button>
                                <button wire:click="delete({{ $subscription->id }})" wire:confirm="Delete this subscription?" class="block w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-slate-500">No subscriptions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $subscriptions->links() }}</div>
</div>
