<div
    wire:poll.1s="refreshAuctionState"
    class="{{ $darkMode ? 'bg-slate-950 text-slate-100' : 'sb-shell-bg text-slate-900' }} {{ $projectorMode ? 'min-h-screen' : 'min-h-[calc(100vh-2rem)]' }} {{ $compactMode ? 'p-3 lg:p-3.5 space-y-2.5' : 'p-4 lg:p-5 space-y-3' }} rounded-2xl overflow-x-hidden"
    x-on:auction-activity.window="playEventCue($event.detail?.action || 'state_changed')"
    x-on:auction-player-locked.window="lockedPlayer = $event.detail?.player || null; lockedProgress = 100; lockedCountdown = 7; if (lockedTimer) clearInterval(lockedTimer); lockedPopup = true; let remainingMs = 7000; lockedTimer = setInterval(() => { remainingMs = Math.max(remainingMs - 100, 0); lockedProgress = (remainingMs / 7000) * 100; lockedCountdown = Math.ceil(remainingMs / 1000); if (remainingMs <= 0) { clearInterval(lockedTimer); lockedTimer = null; } }, 100); setTimeout(() => { lockedPopup = false; lockedPlayer = null; lockedProgress = 0; lockedCountdown = 0; if (lockedTimer) { clearInterval(lockedTimer); lockedTimer = null; } }, 7000)"
    x-on:auction-round-complete.window="roundSoldCount = Number($event.detail?.soldCount || 0); roundUnsoldCount = Number($event.detail?.unsoldCount || 0); roundProgress = 100; if (roundInterval) clearInterval(roundInterval); roundCompletePopup = true; let remainingMs = 7000; roundInterval = setInterval(() => { remainingMs = Math.max(remainingMs - 100, 0); roundProgress = (remainingMs / 7000) * 100; if (remainingMs <= 0) { clearInterval(roundInterval); roundInterval = null; roundCompletePopup = false; } }, 100)"
    x-on:keydown.escape.window="soldPlayersModal = false"
    x-data="{
        lastBid: {{ (float) ($auction?->current_bid ?? 0) }},
        highlight: false,
        soldPlayersModal: false,
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
        leaderboardModal: false,
        soundEnabled: true,
        hooterCooldownMs: 500,
        lastHooterAt: 0,
        audioCtx: null,
        timer: {{ $remainingSeconds }},
        timerInterval: null,
        ensureAudio(){
            if (!this.audioCtx) {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (this.audioCtx?.state === 'suspended') {
                this.audioCtx.resume();
            }
        },
        playTone(freq = 740, duration = 0.12, type = 'sawtooth', volume = 0.085){
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
        playActivityCue(){
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
        playBidCue() {
            this.playTone(860, 0.06, 'square', 0.07);
            setTimeout(() => this.playTone(980, 0.08, 'square', 0.075), 70);
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
        playTimerCue() {
            this.playTone(560, 0.05, 'square', 0.065);
            setTimeout(() => this.playTone(620, 0.05, 'square', 0.065), 60);
        },
        playEventCue(action) {
            switch (action) {
                case 'bid':
                    this.playBidCue();
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
        },
        onRefresh(current){ if(current > this.lastBid){ this.highlight = true; setTimeout(() => this.highlight = false, 1200); } this.lastBid = current; },
        startTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (this.timer > 0) this.timer--;
            }, 1000);
        },
        syncTimer(newVal) {
            if (this.timer !== newVal) this.timer = newVal;
        }
    }"
    x-init="onRefresh({{ (float) ($auction?->current_bid ?? 0) }}); startTimer();"
    x-effect="syncTimer({{ $remainingSeconds }});"
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="{{ $projectorMode ? ($compactMode ? 'text-2xl lg:text-3xl' : 'text-3xl lg:text-4xl') : ($compactMode ? 'text-lg md:text-xl' : 'text-xl md:text-2xl') }} font-extrabold {{ $darkMode ? 'text-amber-200' : 'text-amber-900' }} flex items-center gap-2"><i data-lucide="radio" class="w-6 h-6 text-red-600"></i>Live Auction Viewer</h1>
        <div class="inline-flex items-center gap-2 text-xs sm:text-sm opacity-90">
            <button
                type="button"
                @click="soundEnabled = !soundEnabled; ensureAudio()"
                class="px-2 py-1 rounded-md border border-slate-300 bg-white/80 text-slate-700"
                x-text="soundEnabled ? 'Sound: ON' : 'Sound: OFF'"
            ></button>
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Mode: {{ $projectorMode ? 'Projector' : 'Standard' }}{{ $compactMode ? ' • Compact' : '' }}</span>
        </div>
    </div>

    @if($tournament?->banner_path)
        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white">
            <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-28 md:h-40 object-cover" />
        </div>
    @endif

    <div class="sb-shiny-box p-3 text-sm {{ $darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'text-amber-950' }} overflow-x-hidden">
        <div class="sb-marquee">
            <div class="sb-marquee-track font-semibold">
                <span>⚡ Current Player: {{ $auction?->currentPlayer?->name ?? 'N/A' }}</span>
                <span>#️⃣ Serial: {{ $auction?->currentPlayer?->serial_no ?? '-' }}</span>
                <span>💸 Highest Bid: {{ number_format($auction?->current_bid ?? 0, 2) }}</span>
                <span>🏁 Leading Team: {{ $auction?->currentHighestTeam?->name ?? '-' }}</span>
                <span>📋 Sold Count: {{ $soldPlayers->count() }}</span>
                <span>📈 Leaderboard Teams: {{ $leaderboard->count() }}</span>
            </div>
        </div>
    </div>

    @if(!($auction?->is_completed ?? false))
    <div class="sb-shiny-box {{ $compactMode ? 'p-2.5 lg:p-3' : 'p-3 lg:p-4' }} {{ $darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : '' }} {{ $compactMode ? 'space-y-2.5' : 'space-y-3' }} relative overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 text-white shadow">
                <span class="h-2 w-2 rounded-full bg-white"></span>
                LIVE BROADCAST
            </div>
            <div class="text-xs sm:text-sm opacity-90">Status: {{ $auction?->is_paused ? 'PAUSED' : 'LIVE' }}</div>
        </div>

        <div class="grid lg:grid-cols-3 {{ $compactMode ? 'gap-2.5' : 'gap-3' }}">
            <div class="rounded-2xl border {{ $darkMode ? 'border-slate-700 bg-slate-950/60' : 'border-slate-200 bg-white/70' }} {{ $compactMode ? 'p-2.5' : 'p-3' }} relative overflow-hidden">
                <span class="sb-sparkle" style="top: 16%; left: 8%;"></span>
                <span class="sb-sparkle" style="bottom: 18%; right: 10%; animation-delay:.7s;"></span>
                <div class="text-xs uppercase tracking-wide {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }} font-semibold">Current Player</div>
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ $auction?->currentPlayer?->image_url ?? asset('images/team-placeholder.svg') }}" alt="Current player" class="{{ $compactMode ? 'h-14 w-14 md:h-16 md:w-16' : 'h-16 w-16 md:h-20 md:w-20' }} rounded-xl object-cover border border-slate-200" />
                    <div>
                        <div class="{{ $projectorMode ? ($compactMode ? 'text-2xl lg:text-3xl' : 'text-3xl lg:text-4xl') : ($compactMode ? 'text-lg md:text-xl' : 'text-xl md:text-2xl') }} font-black leading-tight">{{ $auction?->currentPlayer?->name ?? 'N/A' }}</div>
                        <div class="text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Serial No: {{ $auction?->currentPlayer?->serial_no ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border {{ $darkMode ? 'border-slate-700 bg-slate-950/70' : 'border-slate-200 bg-white/80' }} {{ $compactMode ? 'p-2.5' : 'p-3' }} text-center flex flex-col justify-center">
                <div class="text-xs uppercase tracking-wide {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }} font-semibold">Timer</div>
                <div class="{{ $projectorMode ? ($compactMode ? 'text-5xl lg:text-6xl' : 'text-6xl lg:text-7xl') : ($compactMode ? 'text-3xl md:text-4xl' : 'text-4xl md:text-5xl') }} font-black leading-none mt-1 {{ $remainingSeconds > 0 && $remainingSeconds <= 5 ? 'text-red-500' : ($darkMode ? 'text-amber-200' : 'text-emerald-800') }}">
                    <span x-text="timer + 's'"></span>
                </div>
                <div class="mt-3 h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 via-amber-500 to-red-600" style="width: {{ $timerPct }}%; transition: width 900ms linear"></div>
                </div>
            </div>

            <div class="rounded-2xl border {{ $darkMode ? 'border-slate-700 bg-slate-950/60' : 'border-slate-200 bg-white/70' }} {{ $compactMode ? 'p-2.5' : 'p-3' }}">
                <div class="text-xs uppercase tracking-wide {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }} font-semibold">Current Highest Bidder</div>
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ $auction?->currentHighestTeam?->logo_url ?? asset('images/team-placeholder.svg') }}" alt="Leading team logo" class="{{ $compactMode ? 'h-12 w-12 md:h-14 md:w-14' : 'h-14 w-14 md:h-16 md:w-16' }} rounded-xl object-cover border border-slate-200" />
                    <div class="min-w-0">
                        <div class="{{ $projectorMode ? ($compactMode ? 'text-xl lg:text-2xl' : 'text-2xl lg:text-3xl') : ($compactMode ? 'text-lg md:text-xl' : 'text-xl md:text-2xl') }} font-black leading-tight truncate">{{ $auction?->currentHighestTeam?->name ?? 'Awaiting bid' }}</div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t {{ $darkMode ? 'border-slate-700' : 'border-slate-200' }} text-center">
                    <div class="text-xs uppercase tracking-wide {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }} font-semibold">Current Bid</div>
                    <div class="{{ $projectorMode ? ($compactMode ? 'text-3xl lg:text-4xl' : 'text-4xl lg:text-5xl') : ($compactMode ? 'text-2xl md:text-3xl' : 'text-3xl md:text-4xl') }} font-black leading-none" :class="highlight ? 'text-emerald-500' : ''">{{ number_format($auction?->current_bid ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="grid {{ $projectorMode ? 'lg:grid-cols-2 gap-2.5' : 'md:grid-cols-2 gap-2.5' }}">
        <div class="rounded-2xl {{ $compactMode ? 'p-2.5' : 'p-3' }} {{ $darkMode ? 'bg-slate-900 border border-slate-700' : 'sb-card' }}">
            <div class="mb-2 flex items-center justify-between gap-2">
                <h2 class="font-semibold {{ $projectorMode ? ($compactMode ? 'text-xl' : 'text-2xl') : '' }} flex items-center gap-2"><i data-lucide="badge-check" class="w-5 h-5 text-amber-700"></i>Sold Players</h2>
                @if($soldPlayers->count() > 4)
                    <button
                        type="button"
                        @click="soldPlayersModal = true"
                        class="h-8 px-3 text-xs rounded-lg border border-slate-300 {{ $darkMode ? 'bg-slate-800 text-slate-100 border-slate-600' : 'bg-white text-slate-700' }} font-semibold"
                    >View All ({{ $soldPlayers->count() }})</button>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 {{ $compactMode ? 'gap-2' : 'gap-2.5' }}">
                @forelse($soldPlayers->take(4) as $player)
                    <div class="rounded-xl border {{ $darkMode ? 'border-slate-700 bg-slate-950/50' : 'border-slate-200 bg-white/80' }} p-2.5 flex items-start gap-2">
                        <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-12 w-12 rounded-lg object-cover border border-slate-200" />
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold truncate">{{ $player->name }}</div>
                            <div class="text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Team: {{ $player->soldTeam?->name ?? '-' }}</div>
                            <div class="text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Amount: {{ number_format($player->final_price ?? 0, 2) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm {{ $darkMode ? 'text-slate-400' : 'text-slate-500' }}">No sold players yet.</div>
                @endforelse
            </div>
            @if($soldPlayers->count() > 4)
                <div class="mt-2 text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Showing 4 of {{ $soldPlayers->count() }} sold players.</div>
            @endif
        </div>

        <div class="rounded-2xl {{ $compactMode ? 'p-2.5' : 'p-3' }} {{ $darkMode ? 'bg-slate-900 border border-slate-700' : 'sb-card' }}">
            <div class="mb-2 flex items-center gap-2">
                <h2 class="font-semibold {{ $projectorMode ? ($compactMode ? 'text-xl' : 'text-2xl') : '' }} flex items-center gap-2"><i data-lucide="podium" class="w-5 h-5 text-red-600"></i>Leaderboard</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 {{ $compactMode ? 'gap-2' : 'gap-2.5' }}">
                @forelse($leaderboard as $team)
                    <div class="rounded-xl border {{ $darkMode ? 'border-slate-700 bg-slate-950/50' : 'border-slate-200 bg-white/80' }} p-2.5 flex items-center gap-2">
                        <img src="{{ $team->logo_url }}" alt="{{ $team->name }} logo" class="h-10 w-10 rounded-lg object-cover border border-slate-200" />
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold truncate">{{ $team->name }}</div>
                            <div class="text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Squad: {{ $team->squad_count }}</div>
                            <div class="text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Wallet: {{ number_format((float) $team->wallet_balance, 2) }}</div>
                            <div class="text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Used: {{ number_format(max((float) ($tournament?->purse_amount ?? 0) - (float) $team->wallet_balance, 0), 2) }}</div>
                            <div class="text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Max Bid: {{ number_format((float) $team->wallet_balance, 2) }}</div>
                            <button
                                type="button"
                                wire:click="viewSquad({{ $team->id }})"
                                wire:loading.attr="disabled"
                                wire:target="viewSquad({{ $team->id }})"
                                class="mt-1 h-7 px-2 text-[11px] rounded-md border {{ $darkMode ? 'border-indigo-500/40 text-indigo-200 bg-indigo-500/10' : 'border-indigo-200 text-indigo-700 bg-indigo-50' }} font-semibold"
                            >
                                <span class="inline-flex items-center">
                                    <svg wire:loading wire:target="viewSquad({{ $team->id }})" class="animate-spin h-4 w-4 mr-1 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                    <span wire:loading wire:target="viewSquad({{ $team->id }})">Loading...</span>
                                    <span wire:loading.remove wire:target="viewSquad({{ $team->id }})">View Squad</span>
                                </span>
                            </button>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="h-3.5 w-3.5 rounded-full border border-slate-200" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
                            <span class="h-3.5 w-3.5 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
                        </div>
                    </div>
                @empty
                    <div class="text-sm {{ $darkMode ? 'text-slate-400' : 'text-slate-500' }}">No teams on leaderboard yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <x-auction.locked-popup />
    <x-auction.round-complete-popup />

    @if($showSquadModal)
        <div class="fixed inset-0 z-[131] bg-black/55 flex items-center justify-center p-4" wire:click.self="closeSquadModal">
            <div class="w-full max-w-3xl max-h-[85vh] overflow-hidden rounded-2xl border {{ $darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900' }} shadow-2xl">
                <div class="px-4 py-3 border-b {{ $darkMode ? 'border-slate-700' : 'border-slate-200' }} flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold">{{ $squadTeamName }} Squad</h3>
                        <p class="text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Sold players currently assigned to this team.</p>
                    </div>
                    <button type="button" wire:click="closeSquadModal" class="px-2 py-1 text-xs rounded-md border {{ $darkMode ? 'border-slate-600 text-slate-200' : 'border-slate-300 text-slate-700' }}">Close</button>
                </div>
                <div class="p-4 overflow-y-auto max-h-[70vh]">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse($squadPlayers as $squadPlayer)
                            <div class="rounded-xl border {{ $darkMode ? 'border-slate-700 bg-slate-950/50' : 'border-slate-200 bg-white' }} p-3 flex items-start gap-2">
                                <img src="{{ $squadPlayer['image_url'] }}" alt="{{ $squadPlayer['name'] }}" class="h-10 w-10 rounded-lg object-cover border border-slate-200" />
                                <div class="min-w-0 text-xs">
                                    <div class="text-sm font-semibold truncate {{ $darkMode ? 'text-slate-100' : 'text-slate-900' }}">{{ $squadPlayer['name'] }}</div>
                                    <div class="{{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Serial: {{ $squadPlayer['serial_no'] ?? '-' }}</div>
                                    <div class="{{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Category: {{ $squadPlayer['category'] ?? 'Uncategorized' }}</div>
                                    <div class="font-semibold {{ $darkMode ? 'text-emerald-300' : 'text-slate-700' }}">Amount: {{ number_format((float) ($squadPlayer['final_price'] ?? 0), 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm {{ $darkMode ? 'text-slate-400' : 'text-slate-500' }}">No players in squad yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div
        x-show="soldPlayersModal"
        x-transition.opacity
        class="fixed inset-0 z-50"
        x-cloak
    >
        <div class="absolute inset-0 bg-black/50" @click="soldPlayersModal = false"></div>

        <div class="absolute inset-x-4 top-8 bottom-8 md:inset-x-16 lg:inset-x-24">
            <div class="h-full rounded-2xl {{ $darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900' }} shadow-2xl border flex flex-col">
                <div class="px-4 py-3 border-b {{ $darkMode ? 'border-slate-700' : 'border-slate-200' }} flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold">All Sold Players</h3>
                        <p class="text-xs {{ $darkMode ? 'text-slate-300' : 'text-slate-500' }}">Full player cards with complete details</p>
                    </div>
                    <button type="button" @click="soldPlayersModal = false" class="px-2 py-1 text-xs rounded-md border {{ $darkMode ? 'border-slate-600 text-slate-200' : 'border-slate-300 text-slate-700' }}">Close</button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        @forelse($soldPlayers as $player)
                            <div class="relative overflow-hidden rounded-2xl border {{ $darkMode ? 'border-amber-500/30 bg-gradient-to-br from-slate-900 via-slate-900 to-rose-950/40' : 'border-amber-200 bg-gradient-to-br from-amber-50 via-white to-rose-50' }} p-4 shadow-lg {{ $darkMode ? 'shadow-black/30' : 'shadow-amber-100/60' }}">
                                <span class="absolute right-4 top-4 rounded-full bg-gradient-to-r from-amber-500 via-rose-500 to-indigo-600 px-2.5 py-1 text-[10px] font-black tracking-wider text-white">SOLD</span>
                                <span class="absolute left-4 top-4 h-2.5 w-2.5 rounded-full bg-amber-300 animate-pulse"></span>
                                <span class="absolute bottom-5 right-6 h-2 w-2 rounded-full bg-rose-300 animate-pulse" style="animation-delay:.35s"></span>
                                <span class="absolute bottom-8 left-1/2 h-2.5 w-2.5 rounded-full bg-indigo-200 animate-pulse" style="animation-delay:.6s"></span>

                                <div class="flex items-center gap-4">
                                    <div class="shrink-0">
                                        <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-28 w-28 rounded-2xl object-cover {{ $darkMode ? 'border-4 border-white/10' : 'border-4 border-white' }} shadow-md" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-lg font-black truncate pr-16 {{ $darkMode ? 'text-white' : 'text-slate-900' }}">{{ $player->name }}</div>
                                        <div class="mt-1 flex flex-wrap gap-2 text-[11px] font-semibold">
                                            <span class="rounded-full px-2 py-1 border {{ $darkMode ? 'bg-white/10 text-amber-200 border-amber-500/30' : 'bg-white/90 text-amber-700 border-amber-200' }}">Serial: {{ $player->serial_no ?? '-' }}</span>
                                            <span class="rounded-full px-2 py-1 border {{ $darkMode ? 'bg-white/10 text-rose-200 border-rose-500/30' : 'bg-white/90 text-rose-700 border-rose-200' }}">{{ $player->category?->name ?? 'Uncategorized' }}</span>
                                        </div>
                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                            <div class="rounded-xl px-3 py-2 border {{ $darkMode ? 'bg-white/5 border-white/10' : 'bg-white/80 border-slate-200' }}">
                                                <div class="{{ $darkMode ? 'text-slate-400' : 'text-slate-500' }} uppercase tracking-wide text-[10px]">Winning Team</div>
                                                <div class="font-semibold truncate {{ $darkMode ? 'text-white' : 'text-slate-900' }}">{{ $player->soldTeam?->name ?? '-' }}</div>
                                            </div>
                                            <div class="rounded-xl px-3 py-2 border {{ $darkMode ? 'bg-white/5 border-white/10' : 'bg-white/80 border-slate-200' }}">
                                                <div class="{{ $darkMode ? 'text-slate-400' : 'text-slate-500' }} uppercase tracking-wide text-[10px]">Sold Amount</div>
                                                <div class="font-black {{ $darkMode ? 'text-emerald-400' : 'text-emerald-700' }}">{{ number_format($player->final_price ?? 0, 2) }}</div>
                                            </div>
                                            <div class="rounded-xl px-3 py-2 border {{ $darkMode ? 'bg-white/5 border-white/10' : 'bg-white/80 border-slate-200' }}">
                                                <div class="{{ $darkMode ? 'text-slate-400' : 'text-slate-500' }} uppercase tracking-wide text-[10px]">Age / Country</div>
                                                <div class="font-semibold {{ $darkMode ? 'text-white' : 'text-slate-900' }}">{{ $player->age ?? '-' }} / {{ $player->country ?: '-' }}</div>
                                            </div>
                                            <div class="rounded-xl px-3 py-2 border {{ $darkMode ? 'bg-white/5 border-white/10' : 'bg-white/80 border-slate-200' }}">
                                                <div class="{{ $darkMode ? 'text-slate-400' : 'text-slate-500' }} uppercase tracking-wide text-[10px]">Previous Team</div>
                                                <div class="font-semibold truncate {{ $darkMode ? 'text-white' : 'text-slate-900' }}">{{ $player->previous_team ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm {{ $darkMode ? 'text-slate-400' : 'text-slate-500' }}">No sold players yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
