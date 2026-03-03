<div class="space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="sb-page-title">Tournaments</h1>
            <p class="sb-page-subtitle">Manage all tournaments from one place.</p>
        </div>
        <a href="{{ route('admin.tournaments.create') }}" class="px-4 py-2 sb-btn-primary">+ Create Tournament</a>
    </div>

    <div class="sb-card p-4 max-w-xl">
        <label class="block text-sm font-medium mb-1">Search Tournament</label>
        <input wire:model.live.debounce.400ms="search" class="sb-input" placeholder="Search by name">
    </div>

    <div class="sb-card overflow-x-auto">
        <table class="w-full">
            <thead class="sb-table-head text-left text-sm text-slate-700 border-b">
                <tr>
                    <th class="sb-table-cell">Name</th>
                    <th class="sb-table-cell">Sport</th>
                    <th class="sb-table-cell">Status</th>
                    <th class="sb-table-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tournaments as $tournament)
                    <tr class="border-b last:border-b-0">
                        <td class="sb-table-cell">{{ $tournament->name }}</td>
                        <td class="sb-table-cell">{{ $tournament->sport?->name ?? '-' }}</td>
                        <td class="sb-table-cell">{{ strtoupper($tournament->status) }}</td>
                        <td class="sb-table-cell">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.tournaments.edit', $tournament->id) }}" class="sb-action-chip border-amber-200 text-amber-700">Edit</a>
                                <a href="{{ route('admin.auction.control', $tournament->id) }}" class="sb-action-chip border-amber-200 text-amber-700">Auction</a>
                                <button wire:click="delete({{ $tournament->id }})" wire:confirm="Delete this tournament?" class="sb-action-chip border-red-200 text-red-600">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-4 text-center text-slate-500">No tournaments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $tournaments->links() }}</div>
</div>
