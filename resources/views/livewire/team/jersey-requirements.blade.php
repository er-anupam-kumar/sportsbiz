<div class="space-y-4">
    <h1 class="text-2xl font-bold">Team Jersey Requirements</h1>

    @if(! $team)
        <div class="bg-amber-50 text-amber-700 p-3 rounded">No team profile mapped to this login.</div>
    @elseif(! $tournament)
        <div class="bg-amber-50 text-amber-700 p-3 rounded">No tournament found for this team.</div>
    @else
        <div class="sb-card p-4 border-t-4 border-emerald-700">
            <div class="flex flex-col items-start gap-3 text-left sm:flex-row sm:items-center sm:justify-between">
                <div class="text-left">
                    <div class="font-semibold text-slate-800">{{ $team->name }}</div>
                    <div class="text-sm text-slate-600">Tournament: {{ $tournament->name }}</div>
                    <div class="mt-1 text-sm {{ $tournament->jersey_module_enabled ? 'text-emerald-700' : 'text-red-700' }}">
                        Module Status: {{ $tournament->jersey_module_enabled ? 'Enabled' : 'Disabled' }}
                    </div>
                </div>
                <img
                    src="{{ $team->jersey_image_url }}"
                    alt="{{ $team->name }} jersey"
                    class="h-16 w-16 self-start rounded-lg object-cover border border-slate-200"
                >
            </div>
        </div>

        <div class="sb-card p-4 space-y-3">
            <h2 class="font-semibold">Add Jersey Entry</h2>

            @if(! $tournament->jersey_module_enabled)
                <div class="bg-red-50 text-red-700 p-3 rounded text-sm">Jersey module is disabled by admin. You can view old entries below.</div>
            @endif

            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Booking For</label>
                    <select wire:model.live="requestFor" class="sb-input" @disabled(! $tournament->jersey_module_enabled)>
                        <option value="player">Player</option>
                        <option value="staff">Staff</option>
                    </select>
                    @error('requestFor') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Select Size</label>
                    <select wire:model="size" class="sb-input" @disabled(! $tournament->jersey_module_enabled)>
                        <option value="">Select size</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                        <option value="3XL">3XL</option>
                    </select>
                    @error('size') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                @if($requestFor === 'player')
                    <div>
                        <label class="block text-sm font-medium mb-1">Select Player</label>
                        <select wire:model="playerId" class="sb-input" @disabled(! $tournament->jersey_module_enabled)>
                            <option value="0">Select player</option>
                            @foreach($players as $player)
                                <option value="{{ $player->id }}">{{ $player->name }}{{ $player->serial_no ? ' ('.$player->serial_no.')' : '' }}</option>
                            @endforeach
                        </select>
                        @error('playerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium mb-1">Staff Name</label>
                        <input wire:model="staffName" class="sb-input" placeholder="Enter staff name" @disabled(! $tournament->jersey_module_enabled)>
                        @error('staffName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium mb-1">Enter Nickname</label>
                    <input wire:model="nickname" class="sb-input" placeholder="Nickname" @disabled(! $tournament->jersey_module_enabled)>
                    @error('nickname') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Enter Jersey Number</label>
                    <input wire:model="jerseyNumber" class="sb-input" placeholder="Jersey Number" @disabled(! $tournament->jersey_module_enabled)>
                    @error('jerseyNumber') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <label class="inline-flex items-center gap-2 text-sm font-medium">
                <input type="checkbox" wire:model="additionalJerseyRequired" @disabled(! $tournament->jersey_module_enabled)>
                Additional jersey required
            </label>

            @if($additionalJerseyRequired)
                <div class="max-w-xs">
                    <label class="block text-sm font-medium mb-1">Number of Additional Jerseys Required</label>
                    <input type="number" min="1" wire:model="additionalJerseyQuantity" class="sb-input" placeholder="Enter quantity" @disabled(! $tournament->jersey_module_enabled)>
                    @error('additionalJerseyQuantity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary" @disabled(! $tournament->jersey_module_enabled)>Save Entry</button>
        </div>

        <div class="sb-card p-4 space-y-3">
            <h2 class="font-semibold">Submitted Entries</h2>
            <div class="sb-card overflow-x-auto">
                <table class="w-full">
                    <thead class="sb-table-head text-left text-sm text-slate-700 border-b">
                        <tr>
                            <th class="sb-table-cell">Type</th>
                            <th class="sb-table-cell">Name</th>
                            <th class="sb-table-cell">Size</th>
                            <th class="sb-table-cell">Nickname</th>
                            <th class="sb-table-cell">Jersey No</th>
                            <th class="sb-table-cell">Additional</th>
                            <th class="sb-table-cell">Additional Qty</th>
                            <th class="sb-table-cell">Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td class="sb-table-cell">{{ ucfirst($entry->request_for ?? 'player') }}</td>
                                <td class="sb-table-cell text-slate-800">{{ $entry->request_for === 'staff' ? ($entry->staff_name ?: $entry->player_name) : $entry->player_name }}</td>
                                <td class="sb-table-cell">{{ $entry->size }}</td>
                                <td class="sb-table-cell">{{ $entry->nickname ?: '-' }}</td>
                                <td class="sb-table-cell">{{ $entry->jersey_number }}</td>
                                <td class="sb-table-cell">{{ $entry->additional_jersey_required ? 'Yes' : 'No' }}</td>
                                <td class="sb-table-cell">{{ $entry->additional_jersey_required ? ($entry->additional_jersey_quantity ?: 0) : '-' }}</td>
                                <td class="sb-table-cell">{{ $entry->created_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-4 text-center text-slate-500">No jersey entries submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $entries->links() }}
        </div>
    @endif
</div>
