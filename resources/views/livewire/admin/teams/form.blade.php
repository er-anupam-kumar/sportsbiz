<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="sb-page-title">{{ $editingId ? 'Edit Team' : 'Create Team' }}</h1>
        <a href="{{ route('admin.teams.index') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-700 text-sm">View Teams</a>
    </div>

    <div class="sb-card p-4 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="sb-section-title">Team Form</h2>
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
                    <img src="{{ $logo->temporaryUrl() }}" alt="Team logo preview" class="mt-2 h-12 w-12 rounded-lg object-cover border border-slate-200">
                @elseif($existingLogoPath)
                    <img src="{{ str_starts_with($existingLogoPath, 'http') ? $existingLogoPath : asset('storage/'.$existingLogoPath) }}" alt="Team logo" class="mt-2 h-12 w-12 rounded-lg object-cover border border-slate-200">
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Team Jersey Image</label>
                <input type="file" wire:model="jerseyImage" accept="image/*" class="sb-input">
                @error('jerseyImage') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @if($jerseyImage)
                    <img src="{{ $jerseyImage->temporaryUrl() }}" alt="Team jersey preview" class="mt-2 h-12 w-12 rounded-lg object-cover border border-slate-200">
                @elseif($existingJerseyImagePath)
                    <img src="{{ str_starts_with($existingJerseyImagePath, 'http') ? $existingJerseyImagePath : asset('storage/'.$existingJerseyImagePath) }}" alt="Team jersey" class="mt-2 h-12 w-12 rounded-lg object-cover border border-slate-200">
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
            <div>
                <label class="block text-sm font-medium mb-1">Captain</label>
                <select wire:model="captainPlayerId" class="sb-input" @disabled(!$editingId)>
                    <option value="0">{{ $editingId ? 'Select Captain' : 'Save team first' }}</option>
                    @foreach($squadPlayersForRoleSelection as $player)
                        <option value="{{ $player->id }}">{{ $player->name }}</option>
                    @endforeach
                </select>
                @error('captainPlayerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Wicketkeeper</label>
                <select wire:model="wicketkeeperPlayerId" class="sb-input" @disabled(!$editingId)>
                    <option value="0">{{ $editingId ? 'Select Wicketkeeper' : 'Save team first' }}</option>
                    @foreach($squadPlayersForRoleSelection as $player)
                        <option value="{{ $player->id }}">{{ $player->name }}</option>
                    @endforeach
                </select>
                @error('wicketkeeperPlayerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm font-medium pt-7"><input type="checkbox" wire:model="isLocked"> Lock team bidding</label>
        </div>

        <div class="flex gap-2">
            <button wire:click="save" wire:loading.attr="disabled" wire:target="save" class="px-4 py-2 sb-btn-primary">
                <span class="inline-flex items-center">
                    <svg wire:loading wire:target="save" class="animate-spin h-4 w-4 mr-1 text-violet-200" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <span wire:loading wire:target="save">Loading...</span>
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update' : 'Create' }}</span>
                </span>
            </button>
            @if($editingId)
                <a href="{{ route('admin.teams.create') }}" class="px-4 py-2 border border-slate-300 rounded-lg">Cancel</a>
            @else
                <button wire:click="resetForm" class="px-4 py-2 border border-slate-300 rounded-lg">Reset</button>
            @endif
        </div>
    </div>
</div>
