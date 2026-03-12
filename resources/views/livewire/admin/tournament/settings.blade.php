<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Edit Tournament: {{ $tournament->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.tournaments.index') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-700 text-sm">Back to List</a>
            <a href="{{ route('admin.tournaments.create') }}" class="px-3 py-2 border border-red-200 rounded-lg text-red-700 text-sm">Create New</a>
        </div>
    </div>
    <div class="grid md:grid-cols-2 gap-3 sb-card p-4">
        <div>
            <label class="block text-sm font-medium mb-1">Sport</label>
            <select wire:model="sportId" class="sb-input">
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
            <label class="block text-sm font-medium mb-1">Tournament Banner (Update)</label>
            <input type="file" wire:model="banner" accept="image/*" class="sb-input">
            @error('banner') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            @if($banner)
                <img src="{{ $banner->temporaryUrl() }}" alt="Tournament banner preview" class="mt-2 h-20 w-full rounded-lg object-cover border border-slate-200" />
            @elseif($existingBannerPath)
                <img src="{{ str_starts_with($existingBannerPath, 'http') ? $existingBannerPath : asset('storage/'.$existingBannerPath) }}" alt="Current tournament banner" class="mt-2 h-20 w-full rounded-lg object-cover border border-slate-200" />
            @endif
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
        <div>
            <label class="block text-sm font-medium mb-1">Bidding Type</label>
            <select wire:model="biddingType" class="sb-input">
                <option value="admin_only">Admin Only</option>
                <option value="team_open">Team Open</option>
            </select>
            @error('biddingType') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select wire:model="status" class="sb-input">
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="paused">Paused</option>
                <option value="completed">Completed</option>
            </select>
            @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2 text-sm font-medium pt-7"><input type="checkbox" wire:model="antiSniping"> Anti-sniping</label>
    </div>
    <button wire:click="save" class="px-4 py-2 sb-btn-primary">Save</button>
</div>
