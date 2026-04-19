<div class="space-y-4">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="sb-page-title">Fixture Planner</h1>
            <p class="sb-page-subtitle">Tournament-wise fixture setup and management.</p>
        </div>
    </div>

    <div class="sb-card overflow-x-auto">
        <table class="w-full">
            <thead class="sb-table-head text-left text-sm text-slate-700 border-b">
                <tr>
                    <th class="sb-table-cell">Tournament</th>
                    <th class="sb-table-cell">Status</th>
                    <th class="sb-table-cell">Start Date</th>
                    <th class="sb-table-cell">Total Fixtures</th>
                    <th class="sb-table-cell">Scheduled</th>
                    <th class="sb-table-cell">Live</th>
                    <th class="sb-table-cell">Completed</th>
                    <th class="sb-table-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tournaments as $tournament)
                    <tr class="border-b last:border-b-0">
                        <td class="sb-table-cell font-medium text-slate-800">{{ $tournament->name }}</td>
                        <td class="sb-table-cell">{{ strtoupper($tournament->status) }}</td>
                        <td class="sb-table-cell">{{ optional($tournament->starts_at)->format('d M Y') ?: '-' }}</td>
                        <td class="sb-table-cell">{{ $tournament->fixtures_count }}</td>
                        <td class="sb-table-cell">{{ $tournament->scheduled_count }}</td>
                        <td class="sb-table-cell">{{ $tournament->live_count }}</td>
                        <td class="sb-table-cell">{{ $tournament->completed_count }}</td>
                        <td class="sb-table-cell">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.fixtures.manage', $tournament->id) }}" class="sb-action-chip border-indigo-200 text-indigo-700">View / Create Fixture</a>
                                <a href="{{ route('admin.fixtures.points-table', $tournament->id) }}" class="sb-action-chip border-emerald-200 text-emerald-700">Points Table</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-4 text-center text-slate-500">No tournaments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
