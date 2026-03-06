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
                            <img src="{{ $player->image_path ? asset('storage/'.$player->image_path) : asset('images/team-placeholder.svg') }}" alt="{{ $player->name }}" class="h-8 w-8 rounded-lg object-cover border border-slate-200">
                        </td>
                        <td class="sb-table-cell">{{ $player->tournament?->name ?? '-' }}</td>
                        <td class="sb-table-cell">{{ $player->category?->name ?? '-' }}</td>
                        <td class="sb-table-cell">{{ number_format($player->base_price, 2) }}</td>
                        <td class="sb-table-cell">{{ strtoupper($player->status) }}</td>
                        <td class="sb-table-cell">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.players.edit', $player->id) }}" class="sb-action-chip border-amber-200 text-amber-700">Edit</a>
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
