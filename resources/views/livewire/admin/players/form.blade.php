<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="sb-page-title">{{ $editingId ? 'Edit Player' : 'Create Player' }}</h1>
        <a href="{{ route('admin.players.index') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-700 text-sm">View Players</a>
    </div>

    <div class="sb-card p-4 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="sb-section-title">Player Form</h2>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="sb-action-chip border-slate-200 text-slate-700">Used: {{ $quota['used'] }}</span>
                <span class="sb-action-chip border-slate-200 text-slate-700">Limit: {{ $quota['limit'] }}</span>
                <span class="sb-action-chip border-amber-200 text-amber-700">Remaining: {{ $quota['remaining'] }}</span>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Tournament</label>
                <select wire:model.live.number="formTournamentId" class="sb-input">
                    <option value="0">Select Tournament</option>
                    @foreach($tournaments as $tournament)
                        <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
                    @endforeach
                </select>
                @error('formTournamentId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <select wire:model.number="categoryId" class="sb-input">
                    <option value="">No Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('categoryId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Player Name</label>
                <input wire:model="name" class="sb-input" placeholder="Player Name">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Player Serial No</label>
                <input type="number" wire:model.number="serialNo" class="sb-input" min="1" placeholder="Serial No">
                @error('serialNo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Base Price</label>
                <input type="number" wire:model.number="basePrice" class="sb-input" placeholder="Base Price">
                @error('basePrice') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Age</label>
                <input type="number" wire:model.number="age" class="sb-input" placeholder="Age">
                @error('age') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Country</label>
                <input wire:model="country" class="sb-input" placeholder="Country">
                @error('country') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Previous Team</label>
                <input wire:model="previousTeam" class="sb-input" placeholder="Previous Team">
                @error('previousTeam') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select wire:model="status" class="sb-input">
                    <option value="available">Available</option>
                    <option value="sold">Sold</option>
                    <option value="unsold">Unsold</option>
                    <option value="retained">Retained</option>
                    <option value="withdrawn">Withdrawn</option>
                </select>
                @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Player Image</label>
                <input type="file" wire:model="image" accept="image/*" class="sb-input">
                @error('image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @if($image)
                    <img src="{{ $image->temporaryUrl() }}" alt="Player image preview" class="mt-2 h-12 w-12 rounded-lg object-cover border border-slate-200">
                @elseif($existingImagePath)
                    <img src="{{ str_starts_with($existingImagePath, 'http') ? $existingImagePath : asset('storage/'.$existingImagePath) }}" alt="Player image" class="mt-2 h-12 w-12 rounded-lg object-cover border border-slate-200">
                @endif
            </div>
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
                <a href="{{ route('admin.players.create') }}" class="px-4 py-2 border border-slate-300 rounded-lg">Cancel</a>
            @else
                <button wire:click="resetForm" class="px-4 py-2 border border-slate-300 rounded-lg">Reset</button>
            @endif
        </div>
    </div>
</div>
