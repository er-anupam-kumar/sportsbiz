<div class="space-y-4" x-data="{ showHierarchyModal: false }">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-bold">Fixtures</h1>
        <button type="button" @click="showHierarchyModal = true" class="px-3 py-2 sb-btn-primary text-sm">View Hierarchy Diagram</button>
    </div>

    @if(! $team)
        <div class="bg-amber-50 text-amber-700 p-3 rounded">No team profile mapped to this login.</div>
    @else
        <div class="max-w-sm">
            <label class="block text-sm font-medium mb-1">Status Filter</label>
            <select wire:model.live="statusFilter" class="sb-input">
                <option value="">All Statuses</option>
                <option value="scheduled">Scheduled</option>
                <option value="live">Live</option>
                <option value="completed">Completed</option>
                <option value="postponed">Postponed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="sb-card overflow-x-auto">
            <table class="w-full">
                <thead class="sb-table-head text-left text-sm text-slate-700 border-b">
                    <tr>
                        <th class="sb-table-cell">Fixture</th>
                        <th class="sb-table-cell">Home</th>
                        <th class="sb-table-cell">Away</th>
                        <th class="sb-table-cell">Match Time</th>
                        <th class="sb-table-cell">Venue</th>
                        <th class="sb-table-cell">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fixtures as $fixture)
                        <tr class="border-b last:border-b-0">
                            <td class="sb-table-cell">{{ $fixture->display_label }}</td>
                            <td class="sb-table-cell">{{ $fixture->home_display_name }}</td>
                            <td class="sb-table-cell">{{ $fixture->away_display_name }}</td>
                            <td class="sb-table-cell">{{ optional($fixture->match_at)->format('d M Y, h:i A') ?: '-' }}</td>
                            <td class="sb-table-cell">{{ $fixture->venue ?: '-' }}</td>
                            <td class="sb-table-cell">{{ strtoupper($fixture->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-4 text-center text-slate-500">No fixtures found for this tournament.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $fixtures->links() }}

        <div x-show="showHierarchyModal" x-cloak class="fixed inset-0 z-[220] bg-slate-900/60 backdrop-blur-[1px] flex items-center justify-center p-4" @click.self="showHierarchyModal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl max-h-[92vh] overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Hierarchy Diagram</h3>
                        <p class="text-xs text-slate-500">Fixture flow map for your tournament.</p>
                    </div>
                    <button type="button" class="px-3 py-1.5 text-xs rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50" @click="showHierarchyModal = false">Close</button>
                </div>

                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/70">
                    <div class="grid md:grid-cols-3 gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <div>Input Slots</div>
                        <div class="text-center">Match Node</div>
                        <div>Output Node(s)</div>
                    </div>
                </div>

                <div class="p-5 pb-12 overflow-auto max-h-[78vh] space-y-4 bg-slate-50">
                    @forelse($hierarchyFixtures as $fixture)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="grid lg:grid-cols-[1.4fr_auto_1fr_auto_1.4fr] gap-3 items-center">
                                <div class="space-y-2">
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 block">Home</span>
                                        {{ $fixture->home_display_name }}
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 block">Away</span>
                                        {{ $fixture->away_display_name }}
                                    </div>
                                </div>

                                <div class="text-slate-300 text-xl font-bold text-center">→</div>

                                <div class="rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-3 text-center">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-500">Match</div>
                                    <div class="text-sm font-semibold text-indigo-900">{{ $fixture->display_label }}</div>
                                    <div class="text-xs text-indigo-700 mt-1">{{ optional($fixture->match_at)->format('d M Y, h:i A') ?: 'Schedule TBD' }}</div>
                                </div>

                                <div class="text-slate-300 text-xl font-bold text-center">→</div>

                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1">Feeds Into</div>
                                    @if(!empty($childrenMap[$fixture->id] ?? []))
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($childrenMap[$fixture->id] as $childLabel)
                                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">{{ $childLabel }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">Final Node</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-500 bg-white rounded-xl border border-slate-200">No fixtures created yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
