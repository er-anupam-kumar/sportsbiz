<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="sb-page-title">Create Tournament</h1>
        <a href="{{ route('admin.tournaments.index') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-700 text-sm">View Tournaments</a>
    </div>

    <div class="sb-card p-4 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="sb-section-title">New Tournament</h2>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="sb-action-chip border-slate-200 text-slate-700">Used: {{ $quota['used'] }}</span>
                <span class="sb-action-chip border-slate-200 text-slate-700">Limit: {{ $quota['limit'] }}</span>
                <span class="sb-action-chip border-amber-200 text-amber-700">Remaining: {{ $quota['remaining'] }}</span>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Sport</label>
                <select wire:model="sportId" class="sb-input">
                    <option value="0">Select Sport</option>
                    @foreach($sports as $sport)
                        <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                    @endforeach
                </select>
                @error('sportId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tournament Name</label>
                <input wire:model="name" class="sb-input" placeholder="Tournament Name">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Purse Amount</label>
                <input type="number" wire:model="purseAmount" class="sb-input" placeholder="Purse Amount">
                @error('purseAmount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Max Players / Team</label>
                <input type="number" wire:model="maxPlayersPerTeam" class="sb-input" placeholder="Max Players/Team">
                @error('maxPlayersPerTeam') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Base Increment</label>
                <input type="number" wire:model="baseIncrement" class="sb-input" placeholder="Base Increment">
                @error('baseIncrement') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Auction Timer (sec)</label>
                <input type="number" wire:model="auctionTimerSeconds" class="sb-input" placeholder="Timer (sec)">
                @error('auctionTimerSeconds') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Auction Type</label>
                <select wire:model="auctionType" class="sb-input">
                    <option value="live">Live</option>
                    <option value="silent">Silent</option>
                </select>
                @error('auctionType') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm font-medium pt-7"><input type="checkbox" wire:model="antiSniping"> Anti-sniping</label>
        </div>
        <div class="flex gap-2">
            <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary">Create</button>
            <button wire:click="resetForm" class="px-4 py-2 border border-slate-300 rounded-lg">Reset</button>
        </div>
    </div>
</div>
