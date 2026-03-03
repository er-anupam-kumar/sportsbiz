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
            this.playTone(740, 0.12, 'sawtooth', 0.04);
            setTimeout(() => this.playTone(660, 0.12, 'sawtooth', 0.04), 130);
            setTimeout(() => this.playTone(740, 0.16, 'sawtooth', 0.045), 260);
        }
    }"
>
    <div class="sb-shiny-box p-4 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Auction Control Panel: {{ $tournament->name }}</h1>
            <p class="text-sm text-slate-600">Live player/team branding enabled</p>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="soundEnabled = !soundEnabled; playClick()"
                class="px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700 bg-white/80"
                x-text="soundEnabled ? 'Sound: ON' : 'Sound: OFF'"
            ></button>
            <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-8 w-8 object-contain" />
        </div>
    </div>
    <div class="sb-shiny-box p-3">
        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-700">
            <span class="px-2 py-1 rounded bg-emerald-50 border border-emerald-100">Available: <span class="font-semibold">{{ $availableCount }}</span></span>
            <span class="px-2 py-1 rounded bg-amber-50 border border-amber-100">Unsold: <span class="font-semibold">{{ $unsoldCount }}</span></span>
        </div>
    </div>
    <div class="sb-shiny-box p-3">
        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-2">
            <button @click="playClick()" wire:click="startAuction" wire:loading.attr="disabled" wire:target="startAuction" class="h-9 w-full px-2 text-sm bg-gradient-to-r from-emerald-700 to-emerald-600 text-white rounded-lg font-semibold disabled:opacity-60">Start</button>
            <button @click="playClick()" wire:click="pauseAuction" wire:loading.attr="disabled" wire:target="pauseAuction" class="h-9 w-full px-2 text-sm bg-gradient-to-r from-slate-700 to-slate-600 text-white rounded-lg font-semibold disabled:opacity-60">Pause</button>
            <button @click="playClick()" wire:click="resumeAuction" wire:loading.attr="disabled" wire:target="resumeAuction" class="h-9 w-full px-2 text-sm bg-gradient-to-r from-amber-700 to-emerald-700 text-white rounded-lg font-semibold disabled:opacity-60">Resume</button>
            <button @click="playClick()" wire:click="extendTimer(10)" wire:loading.attr="disabled" wire:target="extendTimer" class="h-9 w-full px-2 text-sm bg-slate-800 text-white rounded-lg font-semibold disabled:opacity-60">Extend +10s</button>
            <button @click="playClick()" wire:click="markSold" wire:loading.attr="disabled" wire:target="markSold" class="h-9 w-full px-2 text-sm bg-indigo-600 text-white rounded-lg font-semibold disabled:opacity-60">Mark SOLD</button>
            <button @click="playClick()" wire:click="markUnsold" wire:loading.attr="disabled" wire:target="markUnsold" class="h-9 w-full px-2 text-sm bg-amber-600 text-white rounded-lg font-semibold disabled:opacity-60">Mark UNSOLD</button>
            <button @click="playClick()" wire:click="shufflePlayers" wire:loading.attr="disabled" wire:target="shufflePlayers" class="h-9 w-full px-2 text-sm bg-violet-600 text-white rounded-lg font-semibold disabled:opacity-60">Shuffle Players</button>
        </div>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        <div class="sb-shiny-box p-4 md:p-5 space-y-2 relative">
            <span class="sb-sparkle" style="top: 14%; left: 10%;"></span>
            <span class="sb-sparkle" style="bottom: 16%; right: 12%; animation-delay: .8s;"></span>
            <div class="flex items-center justify-between gap-2">
                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Current Player</div>
                    <div class="font-bold text-slate-900">{{ $auction?->currentPlayer?->name ?? 'N/A' }}</div>
                </div>
                <img src="{{ $auction?->currentPlayer?->image_path ? asset('storage/'.$auction->currentPlayer->image_path) : asset('images/team-placeholder.svg') }}" alt="Current player" class="h-12 w-12 rounded-lg object-cover border border-slate-200" />
            </div>
            <div>Current Bid: {{ number_format($auction?->current_bid ?? 0, 2) }}</div>
            <div>Paused: {{ $auction?->is_paused ? 'Yes' : 'No' }}</div>
            <div>Ends At: {{ $auction?->ends_at }}</div>
            <div class="text-sm text-slate-600">
                Timer:
                <span class="font-bold {{ $remainingSeconds > 0 && $remainingSeconds <= 5 ? 'text-red-600 animate-pulse' : 'text-emerald-800' }}">
                    {{ $remainingSeconds }}s
                </span>
            </div>
        </div>
        <div class="sb-shiny-box p-4 md:p-5 space-y-2 relative">
            <span class="sb-sparkle" style="top: 18%; right: 14%; animation-delay: .5s;"></span>
            <span class="sb-sparkle" style="bottom: 22%; left: 16%; animation-delay: 1.2s;"></span>
            <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Leading Team</div>
            @if($auction?->currentHighestTeam)
                <div class="flex items-center gap-2 min-w-0">
                    <img src="{{ $auction->currentHighestTeam->logo_path ? asset('storage/'.$auction->currentHighestTeam->logo_path) : asset('images/team-placeholder.svg') }}" alt="Leading team logo" class="h-10 w-10 rounded-lg object-cover border border-slate-200" />
                    <span class="font-bold text-slate-900 truncate">{{ $auction->currentHighestTeam->name }}</span>
                    <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $auction->currentHighestTeam->primary_color ?: '#e2e8f0' }}"></span>
                    <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $auction->currentHighestTeam->secondary_color ?: '#cbd5e1' }}"></span>
                </div>
            @else
                <div class="text-sm text-slate-500">No leading team yet.</div>
            @endif
            <div class="pt-1 border-t border-slate-200/70">
                <div class="text-sm font-semibold mb-1">Leaderboard</div>
                @forelse($leaderboard as $team)
                    <div class="flex items-center gap-2 text-sm py-1 border-b border-slate-200/60 last:border-b-0">
                        <img src="{{ $team->logo_path ? asset('storage/'.$team->logo_path) : asset('images/team-placeholder.svg') }}" alt="{{ $team->name }} logo" class="h-6 w-6 rounded-md object-cover border border-slate-200" />
                        <span>{{ $team->name }} ({{ $team->squad_count }})</span>
                        <span class="h-3.5 w-3.5 rounded-full border border-slate-200 ml-auto" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
                        <span class="h-3.5 w-3.5 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">No teams yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
