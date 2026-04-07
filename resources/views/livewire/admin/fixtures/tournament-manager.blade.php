<div class="space-y-4" x-data="{ showHierarchyModal: false }">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="sb-page-title">{{ $tournament->name }} Fixtures</h1>
            <p class="sb-page-subtitle">Create fixtures and manage bracket progression for this tournament.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showHierarchyModal = true" class="px-3 py-2 sb-btn-primary text-sm">View Hierarchy Diagram</button>
            <a href="{{ route('admin.fixtures.index') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-700 text-sm">Back to Tournament Summary</a>
        </div>
    </div>

    <div class="sb-card p-4 space-y-3">
        <h2 class="sb-section-title">{{ $editingId ? 'Edit Fixture' : 'Create Fixture' }}</h2>

        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Match Date & Time</label>
                <input type="datetime-local" wire:model="matchAt" class="sb-input">
                @error('matchAt') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Fixture Label</label>
                <input wire:model="matchLabel" class="sb-input" placeholder="e.g. QF 1 / Match 05">
                @error('matchLabel') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Home Slot Type</label>
                <select wire:model.live="homeSourceType" class="sb-input">
                    <option value="team">Fixed Team</option>
                    <option value="winner_of">Winner Of Match</option>
                    <option value="loser_of">Loser Of Match</option>
                    <option value="tbd">TBD</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Away Slot Type</label>
                <select wire:model.live="awaySourceType" class="sb-input">
                    <option value="team">Fixed Team</option>
                    <option value="winner_of">Winner Of Match</option>
                    <option value="loser_of">Loser Of Match</option>
                    <option value="tbd">TBD</option>
                </select>
            </div>

            <div>
                @if($homeSourceType === 'team')
                    <label class="block text-sm font-medium mb-1">Home Team</label>
                    <select wire:model="homeTeamId" class="sb-input">
                        <option value="0">Select Home Team</option>
                        @foreach($teamsForForm as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('homeTeamId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @elseif(in_array($homeSourceType, ['winner_of', 'loser_of']))
                    <label class="block text-sm font-medium mb-1">Home Source Match</label>
                    <select wire:model="homeSourceFixtureId" class="sb-input">
                        <option value="0">Select Source Match</option>
                        @foreach($sourceFixturesForForm as $sourceFixture)
                            <option value="{{ $sourceFixture->id }}">{{ $sourceFixture->match_label ?: ('Match #'.$sourceFixture->id) }}</option>
                        @endforeach
                    </select>
                    @error('homeSourceFixtureId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @else
                    <label class="block text-sm font-medium mb-1">Home Slot</label>
                    <input class="sb-input bg-slate-100" value="TBD" readonly>
                @endif
            </div>

            <div>
                @if($awaySourceType === 'team')
                    <label class="block text-sm font-medium mb-1">Away Team</label>
                    <select wire:model="awayTeamId" class="sb-input">
                        <option value="0">Select Away Team</option>
                        @foreach($teamsForForm as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('awayTeamId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @elseif(in_array($awaySourceType, ['winner_of', 'loser_of']))
                    <label class="block text-sm font-medium mb-1">Away Source Match</label>
                    <select wire:model="awaySourceFixtureId" class="sb-input">
                        <option value="0">Select Source Match</option>
                        @foreach($sourceFixturesForForm as $sourceFixture)
                            <option value="{{ $sourceFixture->id }}">{{ $sourceFixture->match_label ?: ('Match #'.$sourceFixture->id) }}</option>
                        @endforeach
                    </select>
                    @error('awaySourceFixtureId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @else
                    <label class="block text-sm font-medium mb-1">Away Slot</label>
                    <input class="sb-input bg-slate-100" value="TBD" readonly>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Venue</label>
                <input wire:model="venue" class="sb-input" placeholder="Venue">
                @error('venue') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select wire:model="status" class="sb-input">
                    <option value="scheduled">Scheduled</option>
                    <option value="live">Live</option>
                    <option value="completed">Completed</option>
                    <option value="postponed">Postponed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea wire:model="notes" rows="3" class="sb-input" placeholder="Optional notes"></textarea>
                @error('notes') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-2">
            <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary">{{ $editingId ? 'Update Fixture' : 'Create Fixture' }}</button>
            @if($editingId)
                <button wire:click="resetForm" class="px-4 py-2 border border-slate-300 rounded-lg">Cancel</button>
            @endif
        </div>
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
                    <th class="sb-table-cell">Winner</th>
                    <th class="sb-table-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fixtures as $fixture)
                    <tr class="border-b last:border-b-0">
                        <td class="sb-table-cell">{{ $fixture->display_label }}</td>
                        <td class="sb-table-cell">{{ $fixture->home_display_name }}</td>
                        <td class="sb-table-cell">{{ $fixture->away_display_name }}</td>
                        <td class="sb-table-cell">{{ optional($fixture->match_at)->format('d M Y, h:i A') }}</td>
                        <td class="sb-table-cell">{{ $fixture->venue ?: '-' }}</td>
                        <td class="sb-table-cell">{{ strtoupper($fixture->status) }}</td>
                        <td class="sb-table-cell">{{ $fixture->winnerTeam?->name ?: '-' }}</td>
                        <td class="sb-table-cell">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.fixtures.scorer', $fixture->id) }}" class="sb-action-chip border-emerald-200 text-emerald-700">Score / Go Live</a>
                                <button wire:click="edit({{ $fixture->id }})" class="sb-action-chip border-amber-200 text-amber-700">Edit</button>
                                <button wire:click="delete({{ $fixture->id }})" wire:confirm="Delete this fixture?" class="sb-action-chip border-red-200 text-red-600">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-4 text-center text-slate-500">No fixtures found.</td></tr>
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
                    <p class="text-xs text-slate-500">Clear fixture flow map for {{ $tournament->name }}.</p>
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
</div>
