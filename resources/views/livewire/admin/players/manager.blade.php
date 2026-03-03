<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="sb-page-title">Players CRUD</h1>
        <a href="{{ route('admin.categories') }}" class="px-3 py-2 border border-amber-200 rounded-lg text-amber-700 text-sm">Manage Categories</a>
    </div>

    <div class="sb-card p-4 space-y-3">
        <h2 class="sb-section-title">{{ $editingId ? 'Edit Player' : 'Create Player' }}</h2>
        <div class="grid md:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Tournament</label>
                <select wire:model.live="formTournamentId" class="sb-input">
                    <option value="0">Select Tournament</option>
                    @foreach($tournaments as $tournament)
                        <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
                    @endforeach
                </select>
                @error('formTournamentId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <select wire:model="categoryId" class="sb-input">
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
                <label class="block text-sm font-medium mb-1">Base Price</label>
                <input type="number" wire:model="basePrice" class="sb-input" placeholder="Base Price">
                @error('basePrice') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Age</label>
                <input type="number" wire:model="age" class="sb-input" placeholder="Age">
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
                    <img src="{{ $image->temporaryUrl() }}" alt="Player image preview" class="mt-2 h-12 w-12 rounded-lg object-cover border border-blue-100">
                @elseif($existingImagePath)
                    <img src="{{ asset('storage/'.$existingImagePath) }}" alt="Player image" class="mt-2 h-12 w-12 rounded-lg object-cover border border-blue-100">
                @endif
            </div>
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
                        <td class="sb-table-cell">
                            <img src="{{ $player->image_path ? asset('storage/'.$player->image_path) : asset('images/team-placeholder.svg') }}" alt="{{ $player->name }}" class="h-8 w-8 rounded-lg object-cover border border-blue-100">
                        </td>
                        <td class="sb-table-cell">{{ $player->tournament?->name ?? '-' }}</td>
                        <td class="sb-table-cell">{{ $player->category?->name ?? '-' }}</td>
                        <td class="sb-table-cell">{{ number_format($player->base_price, 2) }}</td>
                        <td class="sb-table-cell">{{ strtoupper($player->status) }}</td>
                        <td class="sb-table-cell">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $player->id }})" class="sb-action-chip border-amber-200 text-amber-700">Edit</button>
                                <button wire:click="delete({{ $player->id }})" wire:confirm="Delete this player?" class="sb-action-chip border-red-200 text-red-600">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-center text-slate-500">No players found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $players->links() }}
</div>
