<div class="space-y-3">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h1 class="sb-page-title">Match Scorer</h1>
            <p class="sb-page-subtitle">{{ $fixture->display_label }} | {{ $fixture->home_display_name }} vs {{ $fixture->away_display_name }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="setMatchStatus('scheduled')" class="sb-action-chip border-slate-300 text-slate-700">Mark Scheduled</button>
            <button wire:click="openGoLiveModal" class="sb-action-chip border-emerald-300 text-emerald-700">Go Live</button>
            <button wire:click="completeAndLock" class="sb-action-chip border-indigo-300 text-indigo-700">Complete & Lock</button>
            <a href="{{ route('admin.fixtures.manage', $fixture->tournament_id) }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-700">Back to Fixtures</a>
        </div>
    </div>

    @if($showGoLiveModal)
        <div class="fixed inset-0 z-[220] bg-slate-900/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Pre-Live Match Setup</h3>
                    <button wire:click="$set('showGoLiveModal', false)" class="px-3 py-1.5 text-xs rounded-md border border-slate-300 text-slate-700">Close</button>
                </div>

                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Toss Winner</label>
                        <select wire:model.live="tossWinnerTeamId" class="sb-input">
                            <option value="0">Select Team</option>
                            @foreach($teams->whereIn('id', [(int) $fixture->home_team_id, (int) $fixture->away_team_id]) as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                        @error('tossWinnerTeamId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Toss Decision</label>
                        <select wire:model.live="tossDecision" class="sb-input">
                            <option value="bat">Bat First</option>
                            <option value="bowl">Bowl First</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Batting Team</label>
                        <select wire:model.live="battingTeamId" class="sb-input">
                            <option value="0">Select Team</option>
                            @foreach($teams->whereIn('id', [(int) $fixture->home_team_id, (int) $fixture->away_team_id]) as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                        @error('battingTeamId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Bowling Team</label>
                        <select wire:model.live="bowlingTeamId" class="sb-input">
                            <option value="0">Select Team</option>
                            @foreach($teams->whereIn('id', [(int) $fixture->home_team_id, (int) $fixture->away_team_id]) as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                        @error('bowlingTeamId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Striker</label>
                        <select wire:model="strikerPlayerId" class="sb-input">
                            <option value="0">Select Player</option>
                            @foreach(($playersByTeam[$battingTeamId] ?? collect()) as $player)
                                <option value="{{ $player->id }}">{{ $player->name }}</option>
                            @endforeach
                        </select>
                        @error('strikerPlayerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Non-Striker</label>
                        <select wire:model="nonStrikerPlayerId" class="sb-input">
                            <option value="0">Select Player</option>
                            @foreach(($playersByTeam[$battingTeamId] ?? collect()) as $player)
                                <option value="{{ $player->id }}">{{ $player->name }}</option>
                            @endforeach
                        </select>
                        @error('nonStrikerPlayerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Bowler</label>
                        <select wire:model="bowlerPlayerId" class="sb-input">
                            <option value="0">Select Player</option>
                            @foreach(($playersByTeam[$bowlingTeamId] ?? collect()) as $player)
                                <option value="{{ $player->id }}">{{ $player->name }}</option>
                            @endforeach
                        </select>
                        @error('bowlerPlayerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showGoLiveModal', false)" class="px-3 py-2 border border-slate-300 rounded-lg text-sm">Cancel</button>
                    <button wire:click="confirmGoLiveSetup" class="px-4 py-2 sb-btn-primary">Save & Go Live</button>
                </div>
            </div>
        </div>
    @endif

    @if($showWicketModal)
        <div class="fixed inset-0 z-[230] bg-slate-900/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Wicket Fall</h3>
                    <button wire:click="$set('showWicketModal', false)" class="px-3 py-1.5 text-xs rounded-md border border-slate-300 text-slate-700">Close</button>
                </div>

                @php($battingTeamId = (int) ($innings[$currentInnings]['batting_team_id'] ?? 0))

                @php($dismissedIds = collect($ballHistory)->where('inning', $currentInnings)->pluck('out_player_id')->filter()->map(fn($v) => (int) $v)->unique()->values()->all())
                @php($availableBatters = ($playersByTeam[$battingTeamId] ?? collect())->reject(fn($p) => in_array((int) $p->id, $dismissedIds, true)))

                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Who Got Out?</label>
                        <select wire:model="wicketOutPlayerId" class="sb-input">
                            <option value="0">Select Batter</option>
                            @foreach($availableBatters as $player)
                                <option value="{{ $player->id }}">{{ $player->name }}</option>
                            @endforeach
                        </select>
                        @error('wicketOutPlayerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Dismissal Type</label>
                        <select wire:model="wicketType" class="sb-input">
                            <option value="bowled">Bowled</option>
                            <option value="caught">Caught</option>
                            <option value="lbw">LBW</option>
                            <option value="run_out">Run Out</option>
                            <option value="stumped">Stumped</option>
                            <option value="hit_wicket">Hit Wicket</option>
                            <option value="retired_out">Retired Out</option>
                            <option value="other">Other</option>
                        </select>
                        @error('wicketType') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if(in_array($wicketType, ['caught','run_out','stumped']))
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Fielder / Keeper (Bowling Team)</label>
                            <select wire:model="wicketFielderPlayerId" class="sb-input">
                                <option value="0">Select Player</option>
                                @foreach(($playersByTeam[$bowlingTeamId] ?? collect()) as $player)
                                    <option value="{{ $player->id }}">{{ $player->name }}</option>
                                @endforeach
                            </select>
                            @error('wicketFielderPlayerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($awaitingNextBatter)
                        <div class="md:col-span-2 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                            <label class="block text-sm font-medium mb-1">Next Batter</label>
                            <select wire:model="nextBatterPlayerId" class="sb-input">
                                <option value="0">Select Next Batter</option>
                                @foreach($availableBatters->reject(fn($p) => (int) $p->id === (int) $wicketOutPlayerId) as $player)
                                    <option value="{{ $player->id }}">{{ $player->name }}</option>
                                @endforeach
                            </select>
                            @error('nextBatterPlayerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showWicketModal', false)" class="px-3 py-2 border border-slate-300 rounded-lg text-sm">Cancel</button>
                    <button wire:click="confirmWicketEvent" class="px-4 py-2 sb-btn-primary">{{ $awaitingNextBatter ? 'Confirm Wicket + Next Batter' : 'Continue' }}</button>
                </div>
            </div>
        </div>
    @endif

    @if($showBowlerModal)
        <div class="fixed inset-0 z-[235] bg-slate-900/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">New Over - Select Bowler</h3>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Bowler</label>
                    <select wire:model="bowlerPlayerId" class="sb-input">
                        <option value="0">Select Bowler</option>
                        @foreach(($playersByTeam[$bowlingTeamId] ?? collect()) as $player)
                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                        @endforeach
                    </select>
                    @error('bowlerPlayerId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <button wire:click="confirmNextBowler" class="px-4 py-2 sb-btn-primary">Confirm Bowler</button>
                </div>
            </div>
        </div>
    @endif

    @if($fixture->status === 'completed')
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-3 text-sm text-indigo-900">
            Scorer is locked because this match is completed. Use <span class="font-semibold">Go Live</span> or <span class="font-semibold">Mark Scheduled</span> to unlock editing.
        </div>
    @endif

    <div class="sb-card p-3 grid md:grid-cols-2 gap-2 text-xs">
        <div>
            <div class="text-xs uppercase text-slate-500">Tournament</div>
            <div class="font-semibold text-slate-900">{{ $fixture->tournament?->name }}</div>
        </div>
        <div>
            <div class="text-xs uppercase text-slate-500">Sport</div>
            <div class="font-semibold text-slate-900">{{ $fixture->tournament?->sport?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase text-slate-500">Match Time</div>
            <div class="font-semibold text-slate-900">{{ optional($fixture->match_at)->format('d M Y, h:i A') ?: 'TBD' }}</div>
        </div>
        <div>
            <div class="text-xs uppercase text-slate-500">Status</div>
            <div class="font-semibold text-slate-900">{{ strtoupper($fixture->status) }}</div>
        </div>
    </div>

    @if($isCricket)
        @if($fixture->status !== 'live')
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                Scoring controls are disabled until you click <span class="font-semibold">Go Live</span> and save lineup setup.
            </div>
        @endif
        <fieldset {{ $fixture->status !== 'live' ? 'disabled' : '' }} class="sb-card p-3 space-y-3 disabled:opacity-70">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="sb-section-title">Cricket Scorer Panel</h2>
                <div class="flex items-center gap-2">
                    <button wire:click="switchInnings(1)" class="sb-action-chip {{ $currentInnings === 1 ? 'border-indigo-300 text-indigo-700 bg-indigo-50' : 'border-slate-300 text-slate-700' }}">Innings 1</button>
                    <button wire:click="switchInnings(2)" class="sb-action-chip {{ $currentInnings === 2 ? 'border-indigo-300 text-indigo-700 bg-indigo-50' : 'border-slate-300 text-slate-700' }}">Innings 2</button>
                </div>
            </div>

            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-2.5 space-y-2 order-first">
                @php($ci = (array) ($innings[$currentInnings] ?? ['runs' => 0, 'wickets' => 0, 'overs' => 0, 'balls' => 0]))
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="text-sm font-semibold text-indigo-900">Live Ball Controls</div>
                    <div class="flex items-center gap-2 text-[11px]">
                        <span class="rounded-full border border-indigo-300 bg-white px-2 py-0.5 font-semibold text-indigo-900">Innings {{ $currentInnings }}</span>
                        <span class="rounded-full border border-emerald-300 bg-white px-2 py-0.5 font-semibold text-emerald-800">Score {{ (int) ($ci['runs'] ?? 0) }}/{{ (int) ($ci['wickets'] ?? 0) }}</span>
                        <span class="rounded-full border border-slate-300 bg-white px-2 py-0.5 font-semibold text-slate-700">Over {{ (int) ($ci['overs'] ?? 0) }}.{{ (int) ($ci['balls'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <button wire:click="addCricketEvent(0, false, 'none')" class="px-2 py-1 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700">0</button>
                    <button wire:click="addCricketEvent(1, false, 'none')" class="px-2 py-1 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700">1</button>
                    <button wire:click="addCricketEvent(2, false, 'none')" class="px-2 py-1 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700">2</button>
                    <button wire:click="addCricketEvent(3, false, 'none')" class="px-2 py-1 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700">3</button>
                    <button wire:click="addCricketEvent(4, false, 'none')" class="px-2 py-1 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700">4</button>
                    <button wire:click="addCricketEvent(6, false, 'none')" class="px-2 py-1 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700">6</button>
                    <button wire:click="openWicketModal" class="px-2 py-1 rounded-md border border-red-300 bg-white text-xs font-semibold text-red-700">Wicket</button>
                    <button wire:click="addCricketEvent(0, false, 'wide')" class="px-2 py-1 rounded-md border border-amber-300 bg-white text-xs font-semibold text-amber-700">Wide</button>
                    <button wire:click="addCricketEvent(0, false, 'no_ball')" class="px-2 py-1 rounded-md border border-amber-300 bg-white text-xs font-semibold text-amber-700">No Ball</button>
                    <button wire:click="undoLastBall" class="px-2 py-1 rounded-md border border-rose-300 bg-white text-xs font-semibold text-rose-700">Undo</button>
                </div>

                <div class="grid md:grid-cols-5 gap-1.5 items-end pt-2 border-t border-indigo-200">
                    <div>
                        <label class="block text-xs font-medium mb-1">Extra Type</label>
                        <select wire:model="deliveryExtraType" class="sb-input">
                            <option value="none">None</option>
                            <option value="wide">Wide</option>
                            <option value="no_ball">No Ball</option>
                            <option value="bye">Bye</option>
                            <option value="leg_bye">Leg Bye</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Extra Runs</label>
                        <input type="number" min="0" wire:model="deliveryExtraRuns" class="sb-input" placeholder="e.g. 3">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Runs Off Bat</label>
                        <input type="number" min="0" wire:model="deliveryRunsOffBat" class="sb-input" placeholder="e.g. 2">
                    </div>
                    <div class="pb-2">
                        <label class="inline-flex items-center gap-2 text-xs font-medium">
                            <input type="checkbox" wire:model="deliveryWicket" class="rounded border-slate-300">
                            Wicket
                        </label>
                    </div>
                    <div>
                        <button wire:click="addCustomDelivery" class="w-full px-2.5 py-2 sb-btn-primary text-xs">Add Delivery</button>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-3">
                @foreach([1,2] as $inning)
                    <div class="rounded-xl border border-slate-200 p-2.5 space-y-1.5">
                        <div class="text-sm font-semibold text-slate-800">Innings {{ $inning }}</div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Batting Team</label>
                            <select wire:model="innings.{{ $inning }}.batting_team_id" class="sb-input">
                                <option value="0">Select Team</option>
                                @foreach($teams->whereIn('id', [(int) $fixture->home_team_id, (int) $fixture->away_team_id]) as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium mb-1">Runs</label>
                                <input type="number" min="0" wire:model="innings.{{ $inning }}.runs" class="sb-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Wickets</label>
                                <input type="number" min="0" max="10" wire:model="innings.{{ $inning }}.wickets" class="sb-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Overs</label>
                                <input type="number" min="0" wire:model="innings.{{ $inning }}.overs" class="sb-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Balls</label>
                                <input type="number" min="0" max="5" wire:model="innings.{{ $inning }}.balls" class="sb-input">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium mb-1">Extras</label>
                                <input type="number" min="0" wire:model="innings.{{ $inning }}.extras" class="sb-input">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid md:grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Striker</label>
                    <select wire:model="strikerPlayerId" class="sb-input">
                        <option value="0">Select Striker</option>
                        @foreach(($playersByTeam[$battingTeamId] ?? collect()) as $player)
                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Non-Striker</label>
                    <select wire:model="nonStrikerPlayerId" class="sb-input">
                        <option value="0">Select Non-Striker</option>
                        @foreach(($playersByTeam[$battingTeamId] ?? collect()) as $player)
                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Current Bowler</label>
                    <select wire:model="bowlerPlayerId" class="sb-input">
                        <option value="0">Select Bowler</option>
                        @foreach(($playersByTeam[$bowlingTeamId] ?? collect()) as $player)
                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Target Runs (optional)</label>
                    <input type="number" min="0" wire:model="targetRuns" class="sb-input" placeholder="e.g. 186">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Manual Progress Event</label>
                <div class="flex gap-2">
                    <input wire:model="manualEvent" class="sb-input" placeholder="e.g. Strategic timeout taken">
                    <button wire:click="pushManualEvent" class="px-2.5 py-2 sb-btn-primary text-xs">Add Event</button>
                </div>
            </div>

            <div class="grid md:grid-cols-4 gap-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Result Mode</label>
                    <select wire:model="resultMode" class="sb-input">
                        <option value="auto">Auto</option>
                        <option value="manual_runs">Manual - By Runs</option>
                        <option value="manual_wickets">Manual - By Wickets</option>
                        <option value="tie_no_result">Tie / No Result</option>
                    </select>
                    @error('resultMode') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                @if(in_array($resultMode, ['manual_runs','manual_wickets']))
                    <div>
                        <label class="block text-sm font-medium mb-1">Winner</label>
                        <select wire:model="resultWinnerTeamId" class="sb-input">
                            <option value="0">Select Winner</option>
                            <option value="{{ (int) $fixture->home_team_id }}">{{ $fixture->home_display_name }}</option>
                            <option value="{{ (int) $fixture->away_team_id }}">{{ $fixture->away_display_name }}</option>
                        </select>
                        @error('resultWinnerTeamId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Margin ({{ $resultMode === 'manual_runs' ? 'Runs' : 'Wickets' }})</label>
                        <input type="number" min="1" wire:model="resultMargin" class="sb-input" placeholder="e.g. 12">
                        @error('resultMargin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @elseif($resultMode === 'tie_no_result')
                    <div>
                        <label class="block text-sm font-medium mb-1">Result Type</label>
                        <select wire:model="resultSpecial" class="sb-input">
                            <option value="tie">Tie</option>
                            <option value="no_result">No Result</option>
                        </select>
                        @error('resultSpecial') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>

            <div class="grid md:grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Result Text</label>
                    <input wire:model="resultText" class="sb-input" placeholder="e.g. Team A won by 5 wickets">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Progress Note</label>
                    <input wire:model="progressNote" class="sb-input" placeholder="e.g. Chasing strongly in 17th over">
                </div>
            </div>

            <div class="flex justify-end">
                <button wire:click="saveCricket" class="px-3 py-2 sb-btn-primary text-xs">Save Scorecard</button>
            </div>

            <div class="rounded-xl border border-slate-200 p-2.5">
                <div class="text-sm font-semibold text-slate-800 mb-2">Recent Events</div>
                <div class="space-y-1 text-xs text-slate-600">
                    @forelse($recentEvents as $event)
                        <div class="rounded-md bg-slate-50 border border-slate-200 px-2 py-1">{{ $event }}</div>
                    @empty
                        <div>No events recorded yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-3">
                <div class="rounded-xl border border-slate-200 p-2.5 space-y-1.5">
                    <div class="text-sm font-semibold text-slate-800">Over-by-Over Breakdown</div>
                    @forelse(($overBreakdown[$currentInnings] ?? []) as $overNo => $over)
                        <div class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs text-slate-700 flex items-center justify-between">
                            <span>Over {{ (int) $overNo + 1 }}</span>
                            <span class="font-semibold">{{ $over['runs'] ?? 0 }} runs, {{ $over['wickets'] ?? 0 }} wkts</span>
                        </div>
                    @empty
                        <div class="text-xs text-slate-500">No over data yet for this innings.</div>
                    @endforelse
                </div>

                <div class="rounded-xl border border-slate-200 p-2.5 space-y-1.5">
                    <div class="text-sm font-semibold text-slate-800">Partnerships (Innings {{ $currentInnings }})</div>
                    @php($ps = $partnerships[$currentInnings] ?? ['current_runs' => 0, 'current_balls' => 0, 'stands' => []])
                    <div class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1.5 text-xs text-emerald-900">
                        Current: <span class="font-semibold">{{ $ps['current_runs'] ?? 0 }} ({{ $ps['current_balls'] ?? 0 }} balls)</span>
                    </div>
                    @forelse(($ps['stands'] ?? []) as $stand)
                        <div class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs text-slate-700 flex items-center justify-between">
                            <span>Wicket {{ $stand['at_wicket'] ?? '-' }}</span>
                            <span class="font-semibold">{{ $stand['runs'] ?? 0 }} ({{ $stand['balls'] ?? 0 }})</span>
                        </div>
                    @empty
                        <div class="text-xs text-slate-500">No wicket partnerships recorded yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-3">
                <div class="rounded-xl border border-slate-200 p-2.5 space-y-1.5">
                    <div class="text-sm font-semibold text-slate-800">Batting Scorecard (Innings {{ $currentInnings }})</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="text-left text-slate-500 border-b border-slate-200">
                                    <th class="py-1 pr-2">Batter</th>
                                    <th class="py-1 pr-2">R</th>
                                    <th class="py-1 pr-2">B</th>
                                    <th class="py-1 pr-2">4s</th>
                                    <th class="py-1 pr-2">6s</th>
                                    <th class="py-1 pr-2">SR</th>
                                    <th class="py-1">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($battingScorecard as $row)
                                    <tr class="border-b border-slate-100 text-slate-700">
                                        <td class="py-1 pr-2 font-medium text-slate-900">{{ $row['name'] }}</td>
                                        <td class="py-1 pr-2">{{ $row['runs'] }}</td>
                                        <td class="py-1 pr-2">{{ $row['balls'] }}</td>
                                        <td class="py-1 pr-2">{{ $row['fours'] }}</td>
                                        <td class="py-1 pr-2">{{ $row['sixes'] }}</td>
                                        <td class="py-1 pr-2">{{ number_format((float) $row['strike_rate'], 2) }}</td>
                                        <td class="py-1">{{ $row['out'] ? ($row['dismissal'] ?: 'OUT') : (($row['dnb'] ?? false) ? 'DNB' : 'Not Out') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-2 text-slate-500">No batting stats yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 p-2.5 space-y-1.5">
                    <div class="text-sm font-semibold text-slate-800">Bowling Scorecard (Innings {{ $currentInnings }})</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="text-left text-slate-500 border-b border-slate-200">
                                    <th class="py-1 pr-2">Bowler</th>
                                    <th class="py-1 pr-2">O</th>
                                    <th class="py-1 pr-2">R</th>
                                    <th class="py-1 pr-2">W</th>
                                    <th class="py-1 pr-2">Wd</th>
                                    <th class="py-1 pr-2">Nb</th>
                                    <th class="py-1">Econ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bowlingScorecard as $row)
                                    <tr class="border-b border-slate-100 text-slate-700">
                                        <td class="py-1 pr-2 font-medium text-slate-900">{{ $row['name'] }}</td>
                                        <td class="py-1 pr-2">{{ $row['overs'] }}</td>
                                        <td class="py-1 pr-2">{{ $row['runs'] }}</td>
                                        <td class="py-1 pr-2">{{ $row['wickets'] }}</td>
                                        <td class="py-1 pr-2">{{ $row['wides'] }}</td>
                                        <td class="py-1 pr-2">{{ $row['no_balls'] }}</td>
                                        <td class="py-1">{{ number_format((float) $row['economy'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-2 text-slate-500">No bowling stats yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </fieldset>
    @else
        <fieldset {{ $fixture->status === 'completed' ? 'disabled' : '' }} class="sb-card p-4 space-y-4 disabled:opacity-70">
            <h2 class="sb-section-title">Points Updater</h2>
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $fixture->home_display_name }} Points</label>
                    <div class="flex gap-2">
                        <input type="number" min="0" wire:model="homePoints" class="sb-input">
                        <button wire:click="addPoint('home')" class="sb-action-chip border-indigo-300 text-indigo-700">+1</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $fixture->away_display_name }} Points</label>
                    <div class="flex gap-2">
                        <input type="number" min="0" wire:model="awayPoints" class="sb-input">
                        <button wire:click="addPoint('away')" class="sb-action-chip border-indigo-300 text-indigo-700">+1</button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Result Text</label>
                <input wire:model="resultText" class="sb-input" placeholder="e.g. Team A won 84-76">
            </div>

            <div class="grid md:grid-cols-4 gap-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Result Mode</label>
                    <select wire:model="resultMode" class="sb-input">
                        <option value="auto">Auto</option>
                        <option value="manual_runs">Manual - By Points/Runs</option>
                        <option value="manual_wickets">Manual - By Wickets</option>
                        <option value="tie_no_result">Tie / No Result</option>
                    </select>
                </div>

                @if(in_array($resultMode, ['manual_runs','manual_wickets']))
                    <div>
                        <label class="block text-sm font-medium mb-1">Winner</label>
                        <select wire:model="resultWinnerTeamId" class="sb-input">
                            <option value="0">Select Winner</option>
                            <option value="{{ (int) $fixture->home_team_id }}">{{ $fixture->home_display_name }}</option>
                            <option value="{{ (int) $fixture->away_team_id }}">{{ $fixture->away_display_name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Margin</label>
                        <input type="number" min="1" wire:model="resultMargin" class="sb-input" placeholder="e.g. 8">
                    </div>
                @elseif($resultMode === 'tie_no_result')
                    <div>
                        <label class="block text-sm font-medium mb-1">Result Type</label>
                        <select wire:model="resultSpecial" class="sb-input">
                            <option value="tie">Tie</option>
                            <option value="no_result">No Result</option>
                        </select>
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Progress Note</label>
                <textarea wire:model="progressNote" rows="3" class="sb-input" placeholder="Live note for public page"></textarea>
            </div>

            <div class="flex justify-end">
                <button wire:click="saveNonCricket" class="px-4 py-2 sb-btn-primary">Save Points Update</button>
            </div>
        </fieldset>
    @endif
</div>
