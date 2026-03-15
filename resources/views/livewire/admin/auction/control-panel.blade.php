<div
    wire:poll.1s="refreshAuctionState"
    class="space-y-3 overflow-x-hidden"
    x-on:auction-activity.window="playEventCue($event.detail?.action || 'state_changed')"
    x-on:player-live-set.window="playersModal = false"
    x-on:auction-player-locked.window="lockedPlayer = $event.detail?.player || null; lockedProgress = 100; lockedCountdown = 7; if (lockedTimer) clearInterval(lockedTimer); lockedPopup = true; let remainingMs = 7000; lockedTimer = setInterval(() => { remainingMs = Math.max(remainingMs - 100, 0); lockedProgress = (remainingMs / 7000) * 100; lockedCountdown = Math.ceil(remainingMs / 1000); if (remainingMs <= 0) { clearInterval(lockedTimer); lockedTimer = null; } }, 100); setTimeout(() => { lockedPopup = false; lockedPlayer = null; lockedProgress = 0; lockedCountdown = 0; if (lockedTimer) { clearInterval(lockedTimer); lockedTimer = null; } }, 7000)"
    x-on:auction-round-complete.window="roundSoldCount = Number($event.detail?.soldCount || 0); roundUnsoldCount = Number($event.detail?.unsoldCount || 0); roundProgress = 100; if (roundInterval) clearInterval(roundInterval); roundCompletePopup = true; let remainingMs = 7000; roundInterval = setInterval(() => { remainingMs = Math.max(remainingMs - 100, 0); roundProgress = (remainingMs / 7000) * 100; if (remainingMs <= 0) { clearInterval(roundInterval); roundInterval = null; roundCompletePopup = false; } }, 100)"
    x-on:keydown.escape.window="playersModal = false"
    x-data="{
        soundEnabled: true,
        playersModal: false,
        lockedPopup: false,
        lockedPlayer: null,
        lockedProgress: 0,
        lockedCountdown: 0,
        lockedTimer: null,
        roundCompletePopup: false,
        roundSoldCount: 0,
        roundUnsoldCount: 0,
        roundProgress: 0,
        roundInterval: null,
        playerSearch: '',
        hooterCooldownMs: 500,
        lastHooterAt: 0,
        audioCtx: null,
        ensureAudio() {
            if (!this.audioCtx) {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
        },
        playTone(freq = 880, duration = 0.06, type = 'sine', volume = 0.08) {
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
            this.playTone(620, 0.05, 'square', 0.06);
        },
        playActivityCue() {
            const nowTs = Date.now();
            if (nowTs - this.lastHooterAt < this.hooterCooldownMs) return;
            this.lastHooterAt = nowTs;
            this.playTone(740, 0.12, 'sawtooth', 0.095);
            setTimeout(() => this.playTone(660, 0.12, 'sawtooth', 0.095), 130);
            setTimeout(() => this.playTone(740, 0.16, 'sawtooth', 0.105), 260);
        },
        playShuffleCue() {
            this.playTone(500, 0.08, 'triangle', 0.08);
            setTimeout(() => this.playTone(760, 0.1, 'triangle', 0.08), 85);
        },
        playStartCue() {
            this.playTone(620, 0.06, 'square', 0.07);
            setTimeout(() => this.playTone(760, 0.07, 'square', 0.075), 75);
            setTimeout(() => this.playTone(920, 0.09, 'square', 0.08), 150);
        },
        playPauseCue() {
            this.playTone(520, 0.08, 'sine', 0.075);
            setTimeout(() => this.playTone(420, 0.12, 'sine', 0.08), 90);
        },
        playSoldCue() {
            this.playTone(700, 0.07, 'sawtooth', 0.08);
            setTimeout(() => this.playTone(860, 0.1, 'sawtooth', 0.085), 80);
        },
        playUnsoldCue() {
            this.playTone(460, 0.07, 'sine', 0.08);
            setTimeout(() => this.playTone(360, 0.1, 'sine', 0.085), 85);
        },
        playTimerCue() {
            this.playTone(560, 0.05, 'square', 0.065);
            setTimeout(() => this.playTone(620, 0.05, 'square', 0.065), 60);
        },
        playEventCue(action) {
            switch (action) {
                case 'bid':
                    this.playActivityCue();
                    break;
                case 'player_shuffled':
                    this.playShuffleCue();
                    break;
                case 'auction_started':
                    this.playStartCue();
                    break;
                case 'auction_paused':
                    this.playPauseCue();
                    break;
                case 'player_sold':
                    this.playSoldCue();
                    break;
                case 'timer_extended':
                    this.playTimerCue();
                    break;
                default:
                    this.playActivityCue();
            }
        }
    }"
>
    @if($tournament->banner_path)
        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white">
            <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-28 md:h-36 object-cover" />
        </div>
    @endif

    <div class="sb-shiny-box sb-shiny-box-no-flash p-3 space-y-2">
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
                {{ $startMode === 'manual' ? 'Manual: Choose Player → Bring Live' : 'Auto: system continues player flow'.($randomPickEnabled ? ' with random player picks' : ' in player order') }}
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
        $antiSnipingWindowSeconds = (int) config('auction.anti_sniping_window_seconds', 5);
        $antiSnipingExtensionSeconds = (int) config('auction.anti_sniping_extension_seconds', 10);
        $manualExtendSeconds = 30;
        $lockedModalSeconds = 7;
    @endphp

    <div class="sb-shiny-box sb-shiny-box-no-flash p-3 md:p-4 space-y-3 relative overflow-hidden">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 text-white shadow">
                <span class="h-2 w-2 rounded-full bg-white"></span>
                ON AIR
            </div>
            <div class="text-xs md:text-sm text-slate-600 font-semibold">{{ $tournament->name }}</div>
        </div>

        <div class="grid lg:grid-cols-3 gap-3 items-stretch">
            <div class="rounded-2xl border border-slate-200/80 bg-white/70 p-3 md:p-4 relative overflow-hidden sb-player-flash">
                <span class="absolute top-2 right-2 h-3 w-3 rounded-full bg-rose-500/80 animate-ping"></span>
                <span class="absolute top-2 right-2 h-3 w-3 rounded-full bg-rose-600"></span>
                <span class="sb-sparkle" style="top: 16%; left: 8%;"></span>
                <span class="sb-sparkle" style="bottom: 18%; right: 10%; animation-delay: .7s;"></span>
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Current Player</div>
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ $auction?->currentPlayer?->image_url ?? asset('images/team-placeholder.svg') }}" alt="Current player" class="h-16 w-16 md:h-20 md:w-20 rounded-xl object-cover border border-slate-200" />
                    <div class="min-w-0">
                        <div class="font-black text-xl md:text-2xl text-slate-900 leading-tight truncate">{{ $auction?->currentPlayer?->name ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-500">Serial No: {{ $auction?->currentPlayer?->serial_no ?? '-' }}</div>
                        <div class="text-xs text-slate-500 mt-1">Live on podium</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-3 md:p-4 text-center flex flex-col justify-center">
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Timer</div>
                <div class="mt-1 font-black text-5xl md:text-6xl leading-none {{ $remainingSeconds > 0 && $remainingSeconds <= 5 ? 'text-red-600' : 'text-emerald-800' }}">{{ $remainingSeconds }}s</div>
                <div class="mt-3 h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 via-amber-500 to-red-600" style="width: {{ $timerPct }}%; transition: width 900ms linear"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/70 p-3 md:p-4">
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Current Highest Bidder</div>
                @if($auction?->currentHighestTeam)
                    <div class="mt-2 flex items-center gap-3">
                        <img src="{{ $auction->currentHighestTeam->logo_url }}" alt="Leading team logo" class="h-14 w-14 md:h-16 md:w-16 rounded-xl object-cover border border-slate-200" />
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

    <div class="sb-shiny-box sb-shiny-box-no-flash p-2.5">
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-2">
            <button @click="playStartCue()" wire:click="startAuction" wire:loading.attr="disabled" wire:target="startAuction" class="h-9 w-full px-2 text-sm sb-btn-primary !text-white border border-violet-300/60 font-semibold disabled:opacity-60">
                <span class="inline-flex items-center">
                    <svg wire:loading wire:target="startAuction" class="animate-spin h-4 w-4 mr-1 text-violet-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <span wire:loading wire:target="startAuction">Loading...</span>
                    <span wire:loading.remove wire:target="startAuction">Start</span>
                </span>
            </button>
            <button @click="playPauseCue()" wire:click="pauseAuction" wire:loading.attr="disabled" wire:target="pauseAuction" class="h-9 w-full px-2 text-sm bg-gradient-to-r from-slate-700 to-slate-600 text-white rounded-lg font-semibold disabled:opacity-60">
                <span class="inline-flex items-center">
                    <svg wire:loading wire:target="pauseAuction" class="animate-spin h-4 w-4 mr-1 text-slate-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <span wire:loading wire:target="pauseAuction">Loading...</span>
                    <span wire:loading.remove wire:target="pauseAuction">Pause</span>
                </span>
            </button>
            <button @click="playClick()" wire:click="extendTimer(30)" wire:loading.attr="disabled" wire:target="extendTimer" class="h-9 w-full px-2 text-sm bg-slate-800 text-white rounded-lg font-semibold disabled:opacity-60">
                <span class="inline-flex items-center">
                    <svg wire:loading wire:target="extendTimer" class="animate-spin h-4 w-4 mr-1 text-slate-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <span wire:loading wire:target="extendTimer">Loading...</span>
                    <span wire:loading.remove wire:target="extendTimer">Extend +30s</span>
                </span>
            </button>
            <button @click="playSoldCue()" wire:click="markSold" wire:loading.attr="disabled" wire:target="markSold" class="h-9 w-full px-2 text-sm bg-indigo-600 text-white rounded-lg font-semibold disabled:opacity-60">
                <span class="inline-flex items-center">
                    <svg wire:loading wire:target="markSold" class="animate-spin h-4 w-4 mr-1 text-indigo-200" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <span wire:loading wire:target="markSold">Loading...</span>
                    <span wire:loading.remove wire:target="markSold">Mark SOLD</span>
                </span>
            </button>
            <button @click="playUnsoldCue()" wire:click="markUnsold" wire:loading.attr="disabled" wire:target="markUnsold" class="h-9 w-full px-2 text-sm bg-amber-600 text-white rounded-lg font-semibold disabled:opacity-60">
                <span class="inline-flex items-center">
                    <svg wire:loading wire:target="markUnsold" class="animate-spin h-4 w-4 mr-1 text-amber-200" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <span wire:loading wire:target="markUnsold">Loading...</span>
                    <span wire:loading.remove wire:target="markUnsold">Mark UNSOLD</span>
                </span>
            </button>
            <button @click="playShuffleCue()" wire:click="shufflePlayers" wire:loading.attr="disabled" wire:target="shufflePlayers" class="h-9 w-full px-2 text-sm bg-violet-600 text-white rounded-lg font-semibold disabled:opacity-60">
                <span class="inline-flex items-center">
                    <svg wire:loading wire:target="shufflePlayers" class="animate-spin h-4 w-4 mr-1 text-violet-200" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <span wire:loading wire:target="shufflePlayers">Loading...</span>
                    <span wire:loading.remove wire:target="shufflePlayers">Shuffle Players</span>
                </span>
            </button>
        </div>
        @if($startMode === 'auto')
            <div class="mt-2 flex items-center justify-between gap-3 rounded-xl border border-fuchsia-200 bg-fuchsia-50/80 px-3 py-2.5">
                <div>
                    <div class="text-sm font-semibold text-slate-900">Random Pick Player</div>
                    <div class="text-xs text-slate-600">When enabled, auto mode selects the next live player randomly.</div>
                </div>
                <label class="relative inline-flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="randomPickEnabled" class="peer sr-only">
                    <span class="text-sm font-semibold text-slate-700 min-w-[2.5rem] text-right" x-text="$wire.randomPickEnabled ? 'ON' : 'OFF'"></span>
                    <div class="relative h-10 w-20 rounded-full bg-slate-300 shadow-inner transition peer-checked:bg-fuchsia-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-fuchsia-300/50 after:absolute after:left-1 after:top-1 after:h-8 after:w-8 after:rounded-full after:bg-white after:shadow-md after:transition-all peer-checked:after:translate-x-10"></div>
                </label>
            </div>
        @endif
    </div>

    <div class="sb-shiny-box sb-shiny-box-no-flash p-3 md:p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold text-slate-900">Timer Rules</h2>
            <span class="text-[11px] px-2 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 font-semibold">Live Auction Guide</span>
        </div>
        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-2.5 text-xs text-slate-600">
            <div class="rounded-xl border border-slate-200 bg-white/80 px-3 py-2.5">
                <div class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Start</div>
                <div class="mt-1">When a player goes live, the timer is reset to <span class="font-semibold text-slate-900">{{ (int) $tournament->auction_timer_seconds }}s</span>.</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/80 px-3 py-2.5">
                <div class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Anti-Sniping</div>
                <div class="mt-1">If a valid bid lands in the final <span class="font-semibold text-slate-900">{{ $antiSnipingWindowSeconds }}s</span>, the backend extends the timer by <span class="font-semibold text-slate-900">{{ $antiSnipingExtensionSeconds }}s</span>.</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/80 px-3 py-2.5">
                <div class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Pause / Extend</div>
                <div class="mt-1">Pause freezes the remaining time. Extend adds <span class="font-semibold text-slate-900">{{ $manualExtendSeconds }}s</span> directly to the current timer.</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/80 px-3 py-2.5">
                <div class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">After LOCKED</div>
                <div class="mt-1">The LOCKED modal stays up for <span class="font-semibold text-slate-900">{{ $lockedModalSeconds }}s</span>. In auto mode, only then does the next player get a fresh timer.</div>
            </div>
        </div>
    </div>

    <div class="sb-shiny-box sb-shiny-box-no-flash p-3 md:p-4 space-y-2">
        @if(($tournament->bidding_type ?? 'admin_only') === 'admin_only')
            <div class="text-sm font-semibold">Admin Bidding Panel (Team Names)</div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">
                @forelse($bidTeams as $teamRow)
                    <div class="rounded-xl border border-slate-200 bg-white/80 p-2.5 flex items-center gap-2">
                        <img src="{{ $teamRow->logo_url }}" alt="{{ $teamRow->name }} logo" class="h-9 w-9 rounded-lg object-cover border border-slate-200" />
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-slate-900 truncate">{{ $teamRow->name }}</div>
                            <div class="text-xs text-slate-500">Wallet: {{ number_format((float) $teamRow->wallet_balance, 2) }}</div>
                        </div>
                        <button @click="playActivityCue()" wire:click="placeBidForTeam({{ $teamRow->id }})" wire:loading.attr="disabled" wire:target="placeBidForTeam({{ $teamRow->id }})" class="h-8 px-2 text-xs rounded-md {{ $teamRow->is_locked ? 'bg-slate-400 cursor-not-allowed' : 'bg-indigo-600' }} text-white font-semibold" {{ $teamRow->is_locked ? 'disabled' : '' }}>
                            <span class="inline-flex items-center">
                                <svg wire:loading wire:target="placeBidForTeam({{ $teamRow->id }})" class="animate-spin h-4 w-4 mr-1 text-indigo-200" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                <span wire:loading wire:target="placeBidForTeam({{ $teamRow->id }})">Loading...</span>
                                <span wire:loading.remove wire:target="placeBidForTeam({{ $teamRow->id }})">{{ $teamRow->is_locked ? 'Locked' : 'Place Bid' }}</span>
                            </span>
                        </button>
                        <button wire:click="viewSquad({{ $teamRow->id }})" wire:loading.attr="disabled" wire:target="viewSquad({{ $teamRow->id }})" class="h-8 px-2 text-xs rounded-md border border-indigo-200 text-indigo-700 bg-indigo-50 font-semibold">
                            <span class="inline-flex items-center">
                                <svg wire:loading wire:target="viewSquad({{ $teamRow->id }})" class="animate-spin h-4 w-4 mr-1 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                <span wire:loading wire:target="viewSquad({{ $teamRow->id }})">Loading...</span>
                                <span wire:loading.remove wire:target="viewSquad({{ $teamRow->id }})">View Squad</span>
                            </span>
                        </button>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">No teams found for bidding.</div>
                @endforelse
            </div>
        @else
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800">
                Team Open bidding is enabled for this tournament. Admin Place Bid controls are hidden.
            </div>
        @endif
    </div>
    <div class="sb-shiny-box sb-shiny-box-no-flash p-3 md:p-4 space-y-2">
        <div class="text-sm font-semibold">Leaderboard</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2">
            @forelse($leaderboard as $team)
                <div class="rounded-xl border border-slate-200 bg-white/80 p-2.5 flex items-center gap-2">
                    <img src="{{ $team->logo_url }}" alt="{{ $team->name }} logo" class="h-9 w-9 rounded-lg object-cover border border-slate-200" />
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-slate-900 truncate">{{ $team->name }}</div>
                        <div class="text-xs text-slate-500">Squad: {{ $team->squad_count }}</div>
                        <div class="text-xs text-slate-500">Wallet: {{ number_format((float) $team->wallet_balance, 2) }}</div>
                        <div class="text-xs text-slate-500">Used: {{ number_format(max((float) $tournament->purse_amount - (float) $team->wallet_balance, 0), 2) }}</div>
                        <div class="text-xs text-slate-500">Max Bid: {{ number_format((float) $team->wallet_balance, 2) }}</div>
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
        class="fixed inset-0 z-[120]"
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
                                        <img src="{{ $playerCard->image_url }}" alt="{{ $playerCard->name }}" class="h-10 w-10 rounded-lg object-cover border border-slate-200" />
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-900 truncate">{{ $playerCard->name }}</div>
                                            <div class="text-xs text-slate-500 truncate">Serial: {{ $playerCard->serial_no ?? '-' }}</div>
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
                                @elseif($playerCard->status === 'sold')
                                    <button
                                        type="button"
                                        wire:click="openEditAuctionModal({{ $playerCard->id }})"
                                        class="w-full h-8 px-2 text-xs rounded-md bg-amber-600 text-white font-semibold hover:bg-amber-700 disabled:opacity-60"
                                    >Edit Auction</button>
                                @else
                                    <div class="w-full px-2 py-1.5 text-center text-[11px] rounded-md bg-slate-100 text-slate-500 font-semibold uppercase">{{ $playerCard->status }}</div>
                                @endif
                            </div>
                            @if($editAuctionPlayerId)
                                <div class="fixed inset-0 z-[200] bg-black/40 flex items-center justify-center p-4" wire:ignore.self>
                                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                                        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                                            <div>
                                                <h3 class="text-base font-bold text-slate-900">Edit Auction Details</h3>
                                                <p class="text-xs text-slate-500">Change team and amount for this player.</p>
                                            </div>
                                            <button class="px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700" wire:click="$set('editAuctionPlayerId', null)">Close</button>
                                        </div>
                                        <form wire:submit.prevent="saveEditAuctionDetails" class="p-4 space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Team</label>
                                                <select wire:model="editAuctionTeamId" class="sb-input">
                                                    <option value="">Select Team</option>
                                                    @foreach($editAuctionTeams as $team)
                                                        <option value="{{ $team['id'] }}">{{ $team['name'] }} (Wallet: {{ number_format($team['wallet_balance'], 2) }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Amount</label>
                                                <input type="number" wire:model="editAuctionAmount" :step="editAuctionStepUp" min="0" class="sb-input" />
                                                <div class="text-xs text-slate-500 mt-1">Step up: {{ number_format($editAuctionStepUp, 2) }}</div>
                                            </div>
                                            @if($editAuctionError)
                                                <div class="text-xs text-red-600">{{ $editAuctionError }}</div>
                                            @endif
                                            <div class="flex gap-2 mt-4">
                                                <button type="submit" class="px-4 py-2 sb-btn-primary" wire:loading.attr="disabled" wire:target="saveEditAuctionDetails">
                                                    <span wire:loading wire:target="saveEditAuctionDetails" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                                                    <span wire:loading.remove wire:target="saveEditAuctionDetails">Save</span>
                                                </button>
                                                <button type="button" class="px-4 py-2 border border-slate-300 rounded-lg" wire:click="$set('editAuctionPlayerId', null)">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4 text-center text-slate-500 py-8">No players found.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-auction.locked-popup />

    <x-auction.round-complete-popup />

    @if($showSquadModal)
        <div class="fixed inset-0 z-[130] bg-black/50 flex items-center justify-center p-4" wire:click.self="closeSquadModal">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ $squadTeamName }} Squad</h3>
                        <p class="text-xs text-slate-500">Current sold players in this team.</p>
                    </div>
                    <button class="px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700" wire:click="closeSquadModal">Close</button>
                </div>
                <div class="p-4 overflow-y-auto max-h-[70vh]">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse($squadPlayers as $squadPlayer)
                            <div class="rounded-xl border border-slate-200 bg-white p-3 flex items-start gap-2">
                                <img src="{{ $squadPlayer['image_url'] }}" alt="{{ $squadPlayer['name'] }}" class="h-10 w-10 rounded-lg object-cover border border-slate-200" />
                                <div class="min-w-0 text-xs">
                                    <div class="text-sm font-semibold text-slate-900 truncate">{{ $squadPlayer['name'] }}</div>
                                    <div class="text-slate-500">Serial: {{ $squadPlayer['serial_no'] ?? '-' }}</div>
                                    <div class="text-slate-500">Category: {{ $squadPlayer['category'] ?? 'Uncategorized' }}</div>
                                    <div class="text-slate-600 font-semibold">Amount: {{ number_format((float) ($squadPlayer['final_price'] ?? 0), 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">No players in squad yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
