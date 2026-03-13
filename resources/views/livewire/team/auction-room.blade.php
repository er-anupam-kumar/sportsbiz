<div
    wire:poll.1s="refreshAuctionState"
    class="w-full min-w-0 min-h-[calc(100vh-9rem)] space-y-3 overflow-x-hidden"
    x-on:auction-activity.window="playEventCue($event.detail?.action || 'state_changed')"
    x-on:auction-player-locked.window="lockedPlayer = $event.detail?.player || null; lockedProgress = 100; lockedCountdown = 7; if (lockedTimer) clearInterval(lockedTimer); lockedPopup = true; let remainingMs = 7000; lockedTimer = setInterval(() => { remainingMs = Math.max(remainingMs - 100, 0); lockedProgress = (remainingMs / 7000) * 100; lockedCountdown = Math.ceil(remainingMs / 1000); if (remainingMs <= 0) { clearInterval(lockedTimer); lockedTimer = null; } }, 100); setTimeout(() => { lockedPopup = false; lockedPlayer = null; lockedProgress = 0; lockedCountdown = 0; if (lockedTimer) { clearInterval(lockedTimer); lockedTimer = null; } }, 7000)"
    x-on:auction-round-complete.window="roundSoldCount = Number($event.detail?.soldCount || 0); roundUnsoldCount = Number($event.detail?.unsoldCount || 0); roundProgress = 100; if (roundInterval) clearInterval(roundInterval); roundCompletePopup = true; let remainingMs = 7000; roundInterval = setInterval(() => { remainingMs = Math.max(remainingMs - 100, 0); roundProgress = (remainingMs / 7000) * 100; if (remainingMs <= 0) { clearInterval(roundInterval); roundInterval = null; roundCompletePopup = false; } }, 100)"
    x-on:keydown.escape.window="soldPlayersModal = false"
    x-on:click.window.once="ensureAudio()"
    x-data="{
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
        soundEnabled: true,
        hooterCooldownMs: 500,
        lastHooterAt: 0,
        audioCtx: null,
        ensureAudio() {
            if (!this.audioCtx) {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (this.audioCtx?.state === 'suspended') {
                this.audioCtx.resume();
            }
        },
        playTone(freq = 740, duration = 0.12, type = 'sawtooth', volume = 0.085) {
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
        playActivityCue() {
            const nowTs = Date.now();
            if (nowTs - this.lastHooterAt < this.hooterCooldownMs) return;
            this.lastHooterAt = nowTs;
            this.playTone(740, 0.12, 'sawtooth', 0.095);
            setTimeout(() => this.playTone(660, 0.12, 'sawtooth', 0.095), 130);
            setTimeout(() => this.playTone(740, 0.16, 'sawtooth', 0.105), 260);
        },
        playShuffleCue() {
            this.playTone(520, 0.08, 'triangle', 0.08);
            setTimeout(() => this.playTone(780, 0.1, 'triangle', 0.08), 90);
        },
        playBidCue() {
            this.playTone(860, 0.06, 'square', 0.07);
            setTimeout(() => this.playTone(980, 0.08, 'square', 0.075), 70);
        },
        playStartCue() {
            this.playTone(640, 0.06, 'triangle', 0.075);
            setTimeout(() => this.playTone(780, 0.08, 'triangle', 0.075), 75);
            setTimeout(() => this.playTone(920, 0.1, 'triangle', 0.08), 150);
        },
        playPauseCue() {
            this.playTone(500, 0.08, 'sine', 0.075);
            setTimeout(() => this.playTone(390, 0.1, 'sine', 0.08), 90);
        },
        playSoldCue() {
            this.playTone(720, 0.07, 'sawtooth', 0.08);
            setTimeout(() => this.playTone(900, 0.1, 'sawtooth', 0.085), 85);
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
        }
    }"
>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl md:text-2xl font-extrabold text-amber-900">Auction Room</h1>
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="soundEnabled = !soundEnabled; ensureAudio()"
                class="px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700 bg-white/80"
                x-text="soundEnabled ? 'Sound: ON' : 'Sound: OFF'"
            ></button>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-gradient-to-r from-amber-600 via-red-600 to-emerald-600 text-white shadow">LIVE</span>
        </div>
    </div>

    @if($tournament?->banner_path)
        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white">
            <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-28 md:h-36 object-cover" />
        </div>
    @endif

    @if($error)
        <div class="bg-red-50 text-red-700 p-3 rounded-xl border border-red-200">{{ $error }}</div>
    @endif

    <div class="sb-shiny-box p-3 text-sm text-amber-950">
        <div class="sb-marquee">
            <div class="sb-marquee-track font-semibold">
                <span>⚡ Current Player: {{ $auction?->currentPlayer?->name ?? 'N/A' }}</span>
                <span>#️⃣ Serial: {{ $auction?->currentPlayer?->serial_no ?? '-' }}</span>
                <span>🏷️ Category: {{ $auction?->currentPlayer?->category?->name ?? 'Uncategorized' }}</span>
                <span>💰 Base: {{ number_format($auction?->currentPlayer?->base_price ?? 0, 2) }}</span>
                <span>⬆️ Step Up: {{ number_format($tournament?->base_increment ?? 0, 2) }}</span>
                <span>💸 Highest Bid: {{ number_format($auction?->current_bid ?? 0, 2) }}</span>
                <span>🏁 Leading Team: {{ $auction?->currentHighestTeam?->name ?? '-' }}</span>
                <span>👛 Your Wallet: {{ number_format($team?->wallet_balance ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    @php
        $timerTotal = max((int) ($tournament?->auction_timer_seconds ?? 30), 1);
        $timerPct = max(0, min(100, (int) round(($remainingSeconds / $timerTotal) * 100)));
        $stepUpAmount = (float) ($tournament?->base_increment ?? 0);
        $teamWallet = (float) ($team?->wallet_balance ?? 0);
        $nextMinimumBid = ($auction?->current_bid ?? 0) > 0
            ? (float) (($auction?->current_bid ?? 0) + $stepUpAmount)
            : (float) ($auction?->currentPlayer?->base_price ?? 0);
        $canBidNext = $teamWallet >= $nextMinimumBid;
    @endphp

    <div class="sb-shiny-box p-3 md:p-4 space-y-3 relative overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 text-white shadow">
                <span class="h-2 w-2 rounded-full bg-white"></span>
                LIVE FEED
            </div>
            <div class="text-xs md:text-sm text-slate-600 font-semibold">Auction Arena</div>
        </div>

        <div class="grid lg:grid-cols-3 gap-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white/70 p-3 relative overflow-hidden">
                <span class="sb-sparkle" style="top: 16%; left: 8%;"></span>
                <span class="sb-sparkle" style="bottom: 18%; right: 10%; animation-delay:.7s;"></span>
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Current Player</div>
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ $auction?->currentPlayer?->image_url ?? asset('images/team-placeholder.svg') }}" alt="Current player" class="h-16 w-16 md:h-20 md:w-20 rounded-xl object-cover border border-slate-200" />
                    <div class="min-w-0">
                        <div class="font-black text-xl md:text-2xl leading-tight text-slate-900 truncate">{{ $auction?->currentPlayer?->name ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-500">Serial No: {{ $auction?->currentPlayer?->serial_no ?? '-' }}</div>
                        <div class="text-xs text-slate-500 mt-1">Category: {{ $auction?->currentPlayer?->category?->name ?? 'Uncategorized' }}</div>
                        <div class="text-xs text-slate-500">Base Price: {{ number_format($auction?->currentPlayer?->base_price ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-500">Age: {{ $auction?->currentPlayer?->age ?? '-' }} | Country: {{ $auction?->currentPlayer?->country ?: '-' }}</div>
                        <div class="text-xs text-slate-500 truncate">Previous Team: {{ $auction?->currentPlayer?->previous_team ?: '-' }}</div>
                        <div class="text-xs text-slate-500">Current Bid: {{ number_format($auction?->current_bid ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-500">Step Up Amount: {{ number_format($stepUpAmount, 2) }}</div>
                        <div class="text-xs text-slate-500">Next Minimum Bid: {{ number_format($nextMinimumBid, 2) }}</div>
                        <div class="text-xs text-slate-500">Leading Team: {{ $auction?->currentHighestTeam?->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-3 text-center flex flex-col justify-center">
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Timer</div>
                <div class="mt-1 font-black text-5xl md:text-6xl leading-none {{ $remainingSeconds > 0 && $remainingSeconds <= 5 ? 'text-red-600' : 'text-emerald-800' }}">{{ $remainingSeconds }}s</div>
                <div class="mt-3 h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 via-amber-500 to-red-600" style="width: {{ $timerPct }}%; transition: width 900ms linear"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/70 p-3">
                <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Current Highest Bidder</div>
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ $auction?->currentHighestTeam?->logo_url ?? asset('images/team-placeholder.svg') }}" alt="Leading team logo" class="h-14 w-14 md:h-16 md:w-16 rounded-xl object-cover border border-slate-200" />
                    <div class="min-w-0">
                        <div class="font-black text-xl md:text-2xl leading-tight text-slate-900 truncate">{{ $auction?->currentHighestTeam?->name ?? 'Awaiting bid' }}</div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-200 text-center">
                    <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Current Bid</div>
                    <div class="font-black text-3xl md:text-4xl leading-none text-slate-900">{{ number_format($auction?->current_bid ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="sb-shiny-box p-2.5 md:p-3 flex flex-wrap items-end justify-between gap-2.5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5 flex-1 min-w-0 w-full">
            <div class="rounded-xl border border-slate-200 bg-white/70 px-3 py-2">
                <p class="text-[11px] uppercase tracking-wide text-amber-700/80 font-semibold">Your Team Wallet{{ $team?->name ? ' • '.$team->name : '' }}</p>
                <p class="text-2xl md:text-3xl font-black leading-none {{ $canBidNext ? 'text-emerald-700' : 'text-red-700' }}">{{ number_format($teamWallet, 2) }}</p>
                <p class="text-[11px] mt-0.5 {{ $canBidNext ? 'text-emerald-700' : 'text-red-700' }}">{{ $canBidNext ? 'Ready for next bid' : 'Insufficient for next bid' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/70 px-3 py-2">
                <p class="text-[11px] uppercase tracking-wide text-slate-600 font-semibold">Step Up</p>
                <p class="text-2xl md:text-3xl font-black leading-none text-slate-900">{{ number_format($stepUpAmount, 2) }}</p>
                <p class="text-[11px] mt-0.5 {{ $canBidNext ? 'text-emerald-700' : 'text-red-700' }}">Next Min: {{ number_format($nextMinimumBid, 2) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/70 px-3 py-2">
                <p class="text-[11px] uppercase tracking-wide text-slate-600 font-semibold">Admin Purse</p>
                <p class="text-xs text-slate-600">Total: {{ number_format($adminTotalPurse ?? 0, 2) }}</p>
                <p class="text-xs text-slate-600">Used: {{ number_format($adminUtilizedPurse ?? 0, 2) }}</p>
                <p class="text-xs text-slate-600">Remaining: {{ number_format($adminRemainingPurse ?? 0, 2) }}</p>
            </div>
        </div>
        @if(($tournament?->bidding_type ?? 'admin_only') === 'team_open')
            <button
                type="button"
                wire:click="placeBid"
                wire:loading.attr="disabled"
                wire:target="placeBid"
                class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold disabled:opacity-60"
            >Place Bid</button>
        @else
            <div class="px-3 py-2 rounded-xl border border-indigo-100 bg-indigo-50 text-xs font-semibold text-indigo-700">Bidding is managed by Admin only.</div>
        @endif
    </div>

    <div class="grid md:grid-cols-2 gap-2.5">
        <div class="sb-shiny-box p-3 md:p-4">
            <div class="mb-2 flex items-center justify-between gap-2">
                <h2 class="font-semibold flex items-center gap-2"><i data-lucide="badge-check" class="w-5 h-5 text-amber-700"></i>Sold Players</h2>
                @if($soldPlayers->count() > 4)
                    <button
                        type="button"
                        @click="soldPlayersModal = true"
                        class="h-8 px-3 text-xs rounded-lg border border-slate-300 bg-white text-slate-700 font-semibold"
                    >View All ({{ $soldPlayers->count() }})</button>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @forelse($soldPlayers->take(4) as $player)
                    <div class="rounded-xl border border-slate-200 bg-white/80 p-2.5 flex items-start gap-2">
                        <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-10 w-10 rounded-lg object-cover border border-slate-200" />
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-slate-900 truncate">{{ $player->name }}</div>
                            <div class="text-xs text-slate-500">Team: {{ $player->soldTeam?->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500">Amount: {{ number_format($player->final_price ?? 0, 2) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">No sold players yet.</div>
                @endforelse
            </div>
            @if($soldPlayers->count() > 4)
                <div class="mt-2 text-xs text-slate-500">Showing 4 of {{ $soldPlayers->count() }} sold players.</div>
            @endif
        </div>

        <div class="sb-shiny-box p-3 md:p-4">
            <h2 class="font-semibold mb-2 flex items-center gap-2"><i data-lucide="podium" class="w-5 h-5 text-red-600"></i>Leaderboard</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @forelse($leaderboard as $teamRow)
                    <div class="rounded-xl border border-slate-200 bg-white/80 p-2.5 flex items-center gap-2">
                        <img src="{{ $teamRow->logo_url }}" alt="{{ $teamRow->name }} logo" class="h-10 w-10 rounded-lg object-cover border border-slate-200" />
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-slate-900 truncate">{{ $teamRow->name }}</div>
                            <div class="text-xs text-slate-500">Squad: {{ $teamRow->squad_count }}</div>
                            <div class="text-xs text-slate-500">Wallet: {{ number_format((float) $teamRow->wallet_balance, 2) }}</div>
                            <div class="text-xs text-slate-500">Used: {{ number_format(max((float) ($tournament?->purse_amount ?? 0) - (float) $teamRow->wallet_balance, 0), 2) }}</div>
                            <div class="text-xs text-slate-500">Max Bid: {{ number_format((float) $teamRow->wallet_balance, 2) }}</div>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="h-3.5 w-3.5 rounded-full border border-slate-200" style="background-color: {{ $teamRow->primary_color ?: '#e2e8f0' }}"></span>
                            <span class="h-3.5 w-3.5 rounded-full border border-slate-200" style="background-color: {{ $teamRow->secondary_color ?: '#cbd5e1' }}"></span>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">No teams on leaderboard yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <x-auction.locked-popup />
    <x-auction.round-complete-popup />

    <div
        x-show="soldPlayersModal"
        x-transition.opacity
        class="fixed inset-0 z-50"
        x-cloak
    >
        <div class="absolute inset-0 bg-black/50" @click="soldPlayersModal = false"></div>

        <div class="absolute inset-x-4 top-8 bottom-8 md:inset-x-16 lg:inset-x-24">
            <div class="h-full rounded-2xl bg-white shadow-2xl border border-slate-200 flex flex-col">
                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">All Sold Players</h3>
                        <p class="text-xs text-slate-500">Full player cards</p>
                    </div>
                    <button type="button" @click="soldPlayersModal = false" class="px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700">Close</button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        @forelse($soldPlayers as $player)
                            <div class="relative overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-rose-50 p-4 shadow-lg shadow-amber-100/60">
                                <span class="absolute right-4 top-4 rounded-full bg-gradient-to-r from-amber-500 via-rose-500 to-indigo-600 px-2.5 py-1 text-[10px] font-black tracking-wider text-white">SOLD</span>
                                <span class="absolute left-4 top-4 h-2.5 w-2.5 rounded-full bg-amber-300 animate-pulse"></span>
                                <span class="absolute bottom-5 right-6 h-2 w-2 rounded-full bg-rose-300 animate-pulse" style="animation-delay:.35s"></span>
                                <span class="absolute bottom-8 left-1/2 h-2.5 w-2.5 rounded-full bg-indigo-200 animate-pulse" style="animation-delay:.6s"></span>

                                <div class="flex items-center gap-4">
                                    <div class="shrink-0">
                                        <img src="{{ $player->image_url }}" alt="{{ $player->name }}" class="h-28 w-28 rounded-2xl object-cover border-4 border-white shadow-md" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-lg font-black text-slate-900 truncate pr-16">{{ $player->name }}</div>
                                        <div class="mt-1 flex flex-wrap gap-2 text-[11px] font-semibold">
                                            <span class="rounded-full bg-white/90 px-2 py-1 text-amber-700 border border-amber-200">Serial: {{ $player->serial_no ?? '-' }}</span>
                                            <span class="rounded-full bg-white/90 px-2 py-1 text-rose-700 border border-rose-200">{{ $player->category?->name ?? 'Uncategorized' }}</span>
                                        </div>
                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                            <div class="rounded-xl bg-white/80 border border-slate-200 px-3 py-2">
                                                <div class="text-slate-500 uppercase tracking-wide text-[10px]">Winning Team</div>
                                                <div class="font-semibold text-slate-900 truncate">{{ $player->soldTeam?->name ?? '-' }}</div>
                                            </div>
                                            <div class="rounded-xl bg-white/80 border border-slate-200 px-3 py-2">
                                                <div class="text-slate-500 uppercase tracking-wide text-[10px]">Sold Amount</div>
                                                <div class="font-black text-emerald-700">{{ number_format($player->final_price ?? 0, 2) }}</div>
                                            </div>
                                            <div class="rounded-xl bg-white/80 border border-slate-200 px-3 py-2">
                                                <div class="text-slate-500 uppercase tracking-wide text-[10px]">Age / Country</div>
                                                <div class="font-semibold text-slate-900">{{ $player->age ?? '-' }} / {{ $player->country ?: '-' }}</div>
                                            </div>
                                            <div class="rounded-xl bg-white/80 border border-slate-200 px-3 py-2">
                                                <div class="text-slate-500 uppercase tracking-wide text-[10px]">Previous Team</div>
                                                <div class="font-semibold text-slate-900 truncate">{{ $player->previous_team ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">No sold players yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
