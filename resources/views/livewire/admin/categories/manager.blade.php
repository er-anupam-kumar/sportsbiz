<div class="space-y-4">
    <h1 class="sb-page-title">Player Categories</h1>

    <div class="sb-card p-4 space-y-3">
        <h2 class="sb-section-title">{{ $editingId ? 'Edit Category' : 'Create Category' }}</h2>
        <div class="grid md:grid-cols-3 gap-3">
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
                <label class="block text-sm font-medium mb-1">Category Name</label>
                <input wire:model="name" class="sb-input" placeholder="e.g. Batsman">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Max Per Team</label>
                <input type="number" wire:model="maxPerTeam" class="sb-input" placeholder="Max per team">
                @error('maxPerTeam') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary" wire:target="save">
                <span wire:loading wire:target="save" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update' : 'Create' }}</span>
            </button>
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
                    <th class="sb-table-cell">Category</th>
                    <th class="sb-table-cell">Tournament</th>
                    <th class="sb-table-cell">Max/Team</th>
                    <th class="sb-table-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="border-b last:border-b-0">
                        <td class="sb-table-cell">{{ $category->name }}</td>
                        <td class="sb-table-cell">{{ $category->tournament?->name ?? '-' }}</td>
                        <td class="sb-table-cell">{{ $category->max_per_team }}</td>
                        <td class="sb-table-cell">
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="edit({{ $category->id }})" wire:loading.attr="disabled" wire:target="edit({{ $category->id }})" class="sb-action-chip border-amber-200 text-amber-700">
                                    <span wire:loading wire:target="edit({{ $category->id }})" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-amber-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                                    <span wire:loading.remove wire:target="edit({{ $category->id }})">Edit</span>
                                </button>
                                <button wire:click="delete({{ $category->id }})" wire:confirm="Delete this category?" wire:loading.attr="disabled" wire:target="delete({{ $category->id }})" class="sb-action-chip border-red-200 text-red-600">
                                    <span wire:loading wire:target="delete({{ $category->id }})" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-red-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                                    <span wire:loading.remove wire:target="delete({{ $category->id }})">Delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-4 text-center text-slate-500">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $categories->links() }}</div>
</div>
