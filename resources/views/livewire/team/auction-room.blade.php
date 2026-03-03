<div
    wire:poll.1s
    class="space-y-4 overflow-x-hidden"
    x-on:auction-player-shuffled.window="playHooter()"
    x-data="{
        soundEnabled: true,
        audioCtx: null,
        ensureAudio() {
            if (!this.audioCtx) {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
        },
        playTone(freq = 740, duration = 0.12, type = 'sawtooth', volume = 0.04) {
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
        playHooter() {
            this.playTone(740, 0.12, 'sawtooth', 0.04);
            setTimeout(() => this.playTone(660, 0.12, 'sawtooth', 0.04), 130);
            setTimeout(() => this.playTone(740, 0.16, 'sawtooth', 0.045), 260);
        }
    }"
>
    <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-extrabold text-amber-900">Auction Room</h1>
        <span class="text-xs font-bold px-3 py-1 rounded-full bg-gradient-to-r from-amber-600 via-red-600 to-emerald-600 text-white shadow">LIVE</span>
    </div>

    @if($error)
        <div class="bg-red-50 text-red-700 p-3 rounded-xl border border-red-200">{{ $error }}</div>
    @endif

    <div class="sb-shiny-box p-3 text-sm text-amber-950">
        <div class="sb-marquee">
            <div class="sb-marquee-track font-semibold">
                <span>⚡ Current Player: {{ $auction?->currentPlayer?->name ?? 'N/A' }}</span>
                <span>💸 Highest Bid: {{ number_format($auction?->current_bid ?? 0, 2) }}</span>
                <span>🏁 Leading Team: {{ $auction?->currentHighestTeam?->name ?? '-' }}</span>
                <span>👛 Your Wallet: {{ number_format($team?->wallet_balance ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="sb-shiny-box p-4 md:p-5 relative">
            <span class="sb-sparkle" style="top: 14%; left: 9%;"></span>
            <span class="sb-sparkle" style="top: 32%; right: 12%; animation-delay: .7s;"></span>
            <span class="sb-sparkle" style="bottom: 16%; left: 22%; animation-delay: 1.1s;"></span>

            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-amber-700/80 font-semibold">Current Player</p>
                    <p class="text-2xl md:text-3xl font-black text-amber-950 mt-1">{{ $auction?->currentPlayer?->name ?? 'N/A' }}</p>
                </div>
                <img src="{{ $auction?->currentPlayer?->image_path ? asset('storage/'.$auction->currentPlayer->image_path) : asset('images/team-placeholder.svg') }}" alt="Current player" class="h-14 w-14 rounded-xl object-cover border border-slate-200" />
            </div>
            <div class="text-sm text-slate-600 mt-2 flex items-center gap-2">
                <span>Leading Team:</span>
                @if($auction?->currentHighestTeam)
                    <img src="{{ $auction->currentHighestTeam->logo_path ? asset('storage/'.$auction->currentHighestTeam->logo_path) : asset('images/team-placeholder.svg') }}" alt="Leading team logo" class="h-6 w-6 rounded-md object-cover border border-slate-200" />
                    <span class="font-semibold text-slate-800">{{ $auction->currentHighestTeam->name }}</span>
                    <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $auction->currentHighestTeam->primary_color ?: '#e2e8f0' }}"></span>
                    <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $auction->currentHighestTeam->secondary_color ?: '#cbd5e1' }}"></span>
                @else
                    <span class="font-semibold text-slate-800">-</span>
                @endif
            </div>
        </div>

        <div class="sb-shiny-box p-4 md:p-5 relative">
            <span class="sb-sparkle" style="top: 18%; right: 10%; animation-delay: .5s;"></span>
            <span class="sb-sparkle" style="bottom: 20%; right: 24%; animation-delay: 1.3s;"></span>

            <p class="text-xs uppercase tracking-wide text-amber-700/80 font-semibold">Highest Bid</p>
            <p class="text-3xl md:text-4xl font-black bg-gradient-to-r from-amber-700 via-red-600 to-emerald-600 bg-clip-text text-transparent mt-1">{{ number_format($auction?->current_bid ?? 0, 2) }}</p>
            <p class="text-sm text-slate-600 mt-2">Wallet: <span class="font-semibold text-slate-800">{{ number_format($team?->wallet_balance ?? 0, 2) }}</span></p>
        </div>
    </div>

    <div class="sb-shiny-box p-4 md:p-5 flex items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wide text-amber-700/80 font-semibold">Countdown</p>
            <p class="text-3xl font-black {{ $remainingSeconds > 0 && $remainingSeconds <= 5 ? 'text-red-600 animate-pulse' : 'text-emerald-800' }}">
                {{ $remainingSeconds }}s
            </p>
        </div>
        <button wire:click="placeBid" wire:loading.attr="disabled" class="sb-btn-primary px-5 py-3 shadow-xl">Place Bid</button>
    </div>
</div>
