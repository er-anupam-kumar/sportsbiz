<div
    wire:poll.1s
    class="space-y-3 overflow-x-hidden"
    x-on:auction-activity.window="playHooter()"
    x-on:player-live-set.window="playersModal = false"
    x-on:keydown.escape.window="playersModal = false"
    x-data="{
        soundEnabled: true,
        playersModal: false,
        playerSearch: '',
        hooterCooldownMs: 500,
        lastHooterAt: 0,
        audioCtx: null,
        ensureAudio() {
            if (!this.audioCtx) {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
        },
        playTone(freq = 880, duration = 0.06, type = 'sine', volume = 0.03) {
            if (!this.soundEnabled) return;
            this.ensureAudio();
            const oscillator = this.audioCtx.createOscillator();
            const gain = this.audioCtx.createGain();
            oscillator.type = type;
            oscillator.frequency.value = freq;
            gain.gain.value = volume;
            oscillator.connect(gain);
            gain.connect(this.audioCtx.destination);
            oscillator.start();
            oscillator.stop(this.audioCtx.currentTime + duration);
        },
        playClick() {
            this.playTone(620, 0.05, 'square', 0.02);
        },
        playHooter() {
            const nowTs = Date.now();
            if (nowTs - this.lastHooterAt < this.hooterCooldownMs) return;
            this.lastHooterAt = nowTs;
            this.playTone(740, 0.12, 'sawtooth', 0.04);
            setTimeout(() => this.playTone(660, 0.12, 'sawtooth', 0.04), 130);
            setTimeout(() => this.playTone(740, 0.16, 'sawtooth', 0.045), 260);
        }
    }"
>
    <div class="sb-shiny-box p-3 space-y-2">
        <div class="flex items-center gap-2 flex-nowrap overflow-x-auto pb-1">
            <select wire:model="selectedTournamentId" wire:change="loadTournament" class="h-9 w-56 lg:w-64 shrink-0 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                @foreach($tournaments as $optionTournament)
                    <option value="{{ $optionTournament->id }}">{{ $optionTournament->name }}</option>
                @endforeach
            </select>
            <select wire:model="startMode" class="h-9 w-36 shrink-0 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                <option value="auto">Auto</option>
                <option value="manual">Manual</option>
            </select>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                @click="playersModal = true"
                class="px-3 h-8 text-xs rounded-lg bg-indigo-600 text-white font-semibold"
            >Choose Player</button>

            <span class="text-xs px-2 py-1 rounded border border-slate-200 bg-white text-slate-600">
                {{ $startMode === 'manual' ? 'Manual: Choose Player → Bring Live' : 'Auto: system continues player flow' }}
            </span>

            @if($selectedPlayerId)
                <span class="text-xs px-2 py-1 rounded bg-slate-100 border border-slate-200 text-slate-700">Selected: {{ $selectedPlayerId }}</span>
            @endif

            <span class="text-xs px-2 py-1 rounded bg-emerald-50 border border-emerald-100 text-emerald-700">Available: <span class="font-semibold">{{ $availableCount }}</span></span>
            <span class="text-xs px-2 py-1 rounded bg-amber-50 border border-amber-100 text-amber-700">Unsold: <span class="font-semibold">{{ $unsoldCount }}</span></span>
            <span class="text-xs px-2 py-1 rounded bg-slate-100 border border-slate-200 text-slate-700">{{ $auction?->is_paused ? 'PAUSED' : 'LIVE' }}</span>

            <button
                type="button"
                @click="soundEnabled = !soundEnabled; playClick()"
                class="ml-auto px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700 bg-white/80"
                x-text="soundEnabled ? 'Sound: ON' : 'Sound: OFF'"
            ></button>
            <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-6 w-6 object-contain" />
        </div>
    </div>

    @php
        $timerTotal = max((int) $tournament->auction_timer_seconds, 1);
        $timerPct = max(0, min(100, (int) round(($remainingSeconds / $timerTotal) * 100)));
    @endphp

    <div class="sb-shiny-box p-3 md:p-4 space-y-3 relative overflow-hidden">
        <span class="sb-sparkle" style="top: 10%; left: 6%;"></span>
        <span class="sb-sparkle" style="top: 24%; right: 12%; animation-delay: .6s;"></span>
        <span class="sb-sparkle" style="bottom: 16%; left: 38%; animation-delay: 1.1s;"></span>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 text-white shadow">
                <span class="h-2 w-2 rounded-full bg-white animate-pulse"></span>
                ON AIR
            </div>
            <div class="text-xs md:text-sm text-slate-600 font-semibold">{{ $tournament->name }}</div>
        </div>

        <div class="grid lg:grid-cols-3 gap-3 items-stretch">
            <div class="rounded-2xl border border-slate-200/80 bg-white/70 p-3 md:p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Current Player</div>
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ $auction?->currentPlayer?->image_path ? asset('storage/'.$auction->currentPlayer->image_path) : asset('images/team-placeholder.svg') }}" alt="Current player" class="h-16 w-16 md:h-20 md:w-20 rounded-xl object-cover border border-slate-200" />
                    <div class="min-w-0">
                        <div class="font-black text-xl md:text-2xl text-slate-900 leading-tight truncate">{{ $auction?->currentPlayer?->name ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-500 mt-1">Live on podium</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-3 md:p-4 text-center flex flex-col justify-center">
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Timer</div>
                <div class="mt-1 font-black text-5xl md:text-6xl leading-none {{ $remainingSeconds > 0 && $remainingSeconds <= 5 ? 'text-red-600 animate-pulse' : 'text-emerald-800' }}">{{ $remainingSeconds }}s</div>
                <div class="mt-3 h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 via-amber-500 to-red-600" style="width: {{ $timerPct }}%"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/70 p-3 md:p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Current Highest Bidder</div>
                @if($auction?->currentHighestTeam)
                    <div class="mt-2 flex items-center gap-3">
                        <img src="{{ $auction->currentHighestTeam->logo_path ? asset('storage/'.$auction->currentHighestTeam->logo_path) : asset('images/team-placeholder.svg') }}" alt="Leading team logo" class="h-14 w-14 md:h-16 md:w-16 rounded-xl object-cover border border-slate-200" />
                        <div class="min-w-0">
                            <div class="font-black text-xl md:text-2xl text-slate-900 leading-tight truncate">{{ $auction->currentHighestTeam->name }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $auction->currentHighestTeam->primary_color ?: '#e2e8f0' }}"></span>
                                <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $auction->currentHighestTeam->secondary_color ?: '#cbd5e1' }}"></span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-2 text-lg text-slate-500">Awaiting first bid</div>
                @endif
                <div class="mt-3 pt-3 border-t border-slate-200 text-center">
                    <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Current Bid</div>
                    <div class="font-black text-3xl md:text-4xl leading-none text-slate-900">{{ number_format($auction?->current_bid ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="sb-shiny-box p-2.5">
        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-2">
            <button @click="playClick()" wire:click="startAuction" wire:loading.attr="disabled" wire:target="startAuction" class="h-9 w-full px-2 text-sm sb-btn-primary !text-white border border-violet-300/60 font-semibold disabled:opacity-60">Start</button>
            <button @click="playClick()" wire:click="pauseAuction" wire:loading.attr="disabled" wire:target="pauseAuction" class="h-9 w-full px-2 text-sm bg-gradient-to-r from-slate-700 to-slate-600 text-white rounded-lg font-semibold disabled:opacity-60">Pause</button>
            <button @click="playClick()" wire:click="resumeAuction" wire:loading.attr="disabled" wire:target="resumeAuction" class="h-9 w-full px-2 text-sm bg-gradient-to-r from-amber-700 to-emerald-700 text-white rounded-lg font-semibold disabled:opacity-60">Resume</button>
            <button @click="playClick()" wire:click="extendTimer(10)" wire:loading.attr="disabled" wire:target="extendTimer" class="h-9 w-full px-2 text-sm bg-slate-800 text-white rounded-lg font-semibold disabled:opacity-60">Extend +10s</button>
            <button @click="playClick()" wire:click="markSold" wire:loading.attr="disabled" wire:target="markSold" class="h-9 w-full px-2 text-sm bg-indigo-600 text-white rounded-lg font-semibold disabled:opacity-60">Mark SOLD</button>
            <button @click="playClick()" wire:click="markUnsold" wire:loading.attr="disabled" wire:target="markUnsold" class="h-9 w-full px-2 text-sm bg-amber-600 text-white rounded-lg font-semibold disabled:opacity-60">Mark UNSOLD</button>
            <button @click="playClick()" wire:click="shufflePlayers" wire:loading.attr="disabled" wire:target="shufflePlayers" class="h-9 w-full px-2 text-sm bg-violet-600 text-white rounded-lg font-semibold disabled:opacity-60">Shuffle Players</button>
        </div>
    </div>
    <div class="sb-shiny-box p-3 md:p-4 space-y-2 relative">
        <span class="sb-sparkle" style="top: 18%; right: 14%; animation-delay: .5s;"></span>
        <span class="sb-sparkle" style="bottom: 22%; left: 16%; animation-delay: 1.2s;"></span>
        <div class="text-sm font-semibold">Leaderboard</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2">
            @forelse($leaderboard as $team)
                <div class="rounded-xl border border-slate-200 bg-white/80 p-2.5 flex items-center gap-2">
                    <img src="{{ $team->logo_path ? asset('storage/'.$team->logo_path) : asset('images/team-placeholder.svg') }}" alt="{{ $team->name }} logo" class="h-9 w-9 rounded-lg object-cover border border-slate-200" />
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-slate-900 truncate">{{ $team->name }}</div>
                        <div class="text-xs text-slate-500">Squad: {{ $team->squad_count }}</div>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="h-3.5 w-3.5 rounded-full border border-slate-200" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
                        <span class="h-3.5 w-3.5 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
                    </div>
                </div>
            @empty
                <div class="text-sm text-slate-500">No teams yet.</div>
            @endforelse
        </div>
    </div>

    <div
        x-show="playersModal"
        x-transition.opacity
        class="fixed inset-0 z-50"
        x-cloak
    >
        <div class="absolute inset-0 bg-black/40" @click="playersModal = false"></div>

        <div class="absolute inset-x-4 top-8 bottom-8 md:inset-x-16 lg:inset-x-24">
            <div class="h-full rounded-2xl bg-white shadow-2xl border border-slate-200 flex flex-col">
                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Players</h3>
                        <p class="text-xs text-slate-500">Small cards with live status, team, price, and action</p>
                    </div>
                    <button type="button" @click="playersModal = false" class="px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700">Close</button>
                </div>

                <div class="px-4 py-3 border-b border-slate-200 bg-white">
                    <input
                        type="text"
                        x-model="playerSearch"
                        class="sb-input"
                        placeholder="Search player by name or category"
                    />
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div class="p-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @forelse($modalPlayers as $playerCard)
                            <div
                                class="rounded-xl border border-slate-200 bg-white p-3 space-y-2"
                                x-show="'{{ strtolower($playerCard->name.' '.($playerCard->category?->name ?? '').' '.($playerCard->soldTeam?->name ?? '').' '.$playerCard->status) }}'.includes(playerSearch.toLowerCase())"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex items-center gap-2">
                                        <img src="{{ $playerCard->image_path ? asset('storage/'.$playerCard->image_path) : asset('images/team-placeholder.svg') }}" alt="{{ $playerCard->name }}" class="h-10 w-10 rounded-lg object-cover border border-slate-200" />
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-900 truncate">{{ $playerCard->name }}</div>
                                            <div class="text-xs text-slate-500 truncate">{{ $playerCard->category?->name ?? 'No category' }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold uppercase
                                        {{ $playerCard->status === 'available' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $playerCard->status === 'sold' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                        {{ $playerCard->status === 'unsold' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ !in_array($playerCard->status, ['available','sold','unsold']) ? 'bg-slate-100 text-slate-700' : '' }}
                                    ">{{ $playerCard->status }}</span>
                                </div>

                                <div class="text-xs text-slate-600 space-y-1">
                                    <div>Base: <span class="font-semibold text-slate-800">{{ number_format($playerCard->base_price, 2) }}</span></div>
                                    <div>Team: <span class="font-semibold text-slate-800">{{ $playerCard->soldTeam?->name ?? '-' }}</span></div>
                                    <div>Sold On: <span class="font-semibold text-slate-800">{{ $playerCard->final_price ? number_format($playerCard->final_price, 2) : '-' }}</span></div>
                                </div>

                                @if($playerCard->status === 'available')
                                    <button
                                        type="button"
                                        wire:click="bringPlayerLive({{ $playerCard->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="bringPlayerLive({{ $playerCard->id }})"
                                        class="w-full h-8 px-2 text-xs rounded-md bg-indigo-600 text-white font-semibold hover:bg-indigo-700 disabled:opacity-60"
                                    >
                                        <span wire:loading.remove wire:target="bringPlayerLive({{ $playerCard->id }})">Bring Live Now</span>
                                        <span wire:loading wire:target="bringPlayerLive({{ $playerCard->id }})">Bringing...</span>
                                    </button>
                                @else
                                    <div class="w-full px-2 py-1.5 text-center text-[11px] rounded-md bg-slate-100 text-slate-500 font-semibold uppercase">{{ $playerCard->status }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4 text-center text-slate-500 py-8">No players found.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
