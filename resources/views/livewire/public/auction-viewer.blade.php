<div
    wire:poll.3s
    class="{{ $darkMode ? 'bg-slate-950 text-slate-100' : 'sb-shell-bg text-slate-900' }} {{ $projectorMode ? 'min-h-screen p-8 lg:p-10' : 'p-6' }} rounded-2xl space-y-4"
    x-on:auction-player-shuffled.window="playHooter()"
    x-data="{
        now: Date.now(),
        lastBid: {{ (float) ($auction?->current_bid ?? 0) }},
        highlight: false,
        soundEnabled: true,
        audioCtx: null,
        ensureAudio(){
            if (!this.audioCtx) {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
        },
        playTone(freq = 740, duration = 0.12, type = 'sawtooth', volume = 0.04){
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
        playHooter(){
            this.playTone(740, 0.12, 'sawtooth', 0.04);
            setTimeout(() => this.playTone(660, 0.12, 'sawtooth', 0.04), 130);
            setTimeout(() => this.playTone(740, 0.16, 'sawtooth', 0.045), 260);
        },
        tick(){ this.now = Date.now() },
        onRefresh(current){ if(current > this.lastBid){ this.highlight = true; setTimeout(() => this.highlight = false, 1200); } this.lastBid = current; }
    }"
    x-init="setInterval(() => tick(), 1000); onRefresh({{ (float) ($auction?->current_bid ?? 0) }})"
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="{{ $projectorMode ? 'text-4xl lg:text-5xl' : 'text-2xl' }} font-extrabold {{ $darkMode ? 'text-amber-200' : 'text-amber-900' }} flex items-center gap-2"><i data-lucide="radio" class="w-6 h-6 text-red-600"></i>Live Auction Viewer</h1>
        <div class="inline-flex items-center gap-2 text-xs sm:text-sm opacity-90">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Mode: {{ $projectorMode ? 'Projector' : 'Standard' }}</span>
        </div>
    </div>

    <div class="sb-shiny-box p-3 text-sm {{ $darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'text-amber-950' }} overflow-x-hidden">
        <div class="sb-marquee">
            <div class="sb-marquee-track font-semibold">
                <span>⚡ Current Player: {{ $auction?->currentPlayer?->name ?? 'N/A' }}</span>
                <span>💸 Highest Bid: {{ number_format($auction?->current_bid ?? 0, 2) }}</span>
                <span>🏁 Leading Team: {{ $auction?->currentHighestTeam?->name ?? '-' }}</span>
                <span>📋 Sold Count: {{ $soldPlayers->count() }}</span>
                <span>📈 Leaderboard Teams: {{ $leaderboard->count() }}</span>
            </div>
        </div>
    </div>

    <div class="grid {{ $projectorMode ? 'lg:grid-cols-2 gap-6' : 'md:grid-cols-2 gap-4' }}">
        <div class="sb-shiny-box p-4 lg:p-6 {{ $darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : '' }} relative">
            <span class="sb-sparkle" style="top: 16%; left: 10%;"></span>
            <span class="sb-sparkle" style="top: 38%; right: 12%; animation-delay:.9s;"></span>
            <div class="flex items-start justify-between gap-3">
                <div class="{{ $projectorMode ? 'text-3xl lg:text-4xl' : 'text-base' }} font-semibold flex items-center gap-2"><i data-lucide="user-round" class="w-5 h-5 text-amber-600"></i>Player: {{ $auction?->currentPlayer?->name ?? 'N/A' }}</div>
                <img src="{{ $auction?->currentPlayer?->image_path ? asset('storage/'.$auction->currentPlayer->image_path) : asset('images/team-placeholder.svg') }}" alt="Current player" class="h-14 w-14 rounded-xl object-cover border border-slate-200" />
            </div>
            <div class="{{ $projectorMode ? 'text-4xl lg:text-6xl' : 'text-xl' }} font-black transition transform mt-2" :class="highlight ? 'text-emerald-500 scale-105' : ''">
                Highest Bid: {{ number_format($auction?->current_bid ?? 0, 2) }}
            </div>
            <div class="{{ $projectorMode ? 'text-2xl lg:text-3xl' : 'text-base' }} flex items-center gap-2 mt-2">
                <i data-lucide="shield" class="w-5 h-5 text-red-600"></i>
                <span>Team: {{ $auction?->currentHighestTeam?->name ?? '-' }}</span>
                @if($auction?->currentHighestTeam)
                    <img src="{{ $auction->currentHighestTeam->logo_path ? asset('storage/'.$auction->currentHighestTeam->logo_path) : asset('images/team-placeholder.svg') }}" alt="Leading team logo" class="h-7 w-7 rounded-lg object-cover border border-slate-200" />
                    <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $auction->currentHighestTeam->primary_color ?: '#e2e8f0' }}"></span>
                    <span class="h-5 w-5 rounded-full border border-slate-200" style="background-color: {{ $auction->currentHighestTeam->secondary_color ?: '#cbd5e1' }}"></span>
                @endif
            </div>
        </div>

        <div class="sb-shiny-box p-4 lg:p-6 {{ $darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : '' }} relative">
            <span class="sb-sparkle" style="top: 20%; right: 14%; animation-delay:.6s;"></span>
            <span class="sb-sparkle" style="bottom: 20%; left: 20%; animation-delay:1.4s;"></span>
            <div class="text-xs uppercase tracking-wide font-semibold {{ $darkMode ? 'text-slate-300' : 'text-amber-700/80' }}">Auction Timer</div>
            <div class="{{ $projectorMode ? 'text-6xl lg:text-7xl' : 'text-4xl' }} font-black mt-2" :class="(new Date('{{ $auction?->ends_at }}').getTime() - now) <= 5000 ? 'text-red-500 animate-pulse' : '{{ $darkMode ? 'text-amber-200' : 'text-emerald-800' }}'">
                <span x-text="Math.max(Math.floor((new Date('{{ $auction?->ends_at }}').getTime() - now)/1000),0)"></span>s
            </div>
            <div class="mt-3 text-sm {{ $darkMode ? 'text-slate-300' : 'text-slate-600' }}">Auto-refresh every 3 seconds + live event sync</div>
        </div>
    </div>

    <div class="grid {{ $projectorMode ? 'lg:grid-cols-2 gap-6' : 'md:grid-cols-2 gap-4' }}">
        <div class="rounded-2xl p-4 {{ $darkMode ? 'bg-slate-900 border border-slate-700' : 'sb-card' }}">
            <h2 class="font-semibold mb-2 {{ $projectorMode ? 'text-2xl' : '' }} flex items-center gap-2"><i data-lucide="badge-check" class="w-5 h-5 text-amber-700"></i>Sold Players</h2>
            @forelse($soldPlayers as $player)
                <div class="{{ $projectorMode ? 'text-xl' : 'text-sm' }} py-1 border-b border-slate-200/60 last:border-b-0 flex items-center gap-2">
                    <img src="{{ $player->image_path ? asset('storage/'.$player->image_path) : asset('images/team-placeholder.svg') }}" alt="{{ $player->name }}" class="h-6 w-6 rounded-md object-cover border border-slate-200" />
                    <span>{{ $player->name }} - {{ number_format($player->final_price, 2) }}</span>
                </div>
            @empty
                <div class="text-sm {{ $darkMode ? 'text-slate-400' : 'text-slate-500' }}">No sold players yet.</div>
            @endforelse
        </div>

        <div class="rounded-2xl p-4 {{ $darkMode ? 'bg-slate-900 border border-slate-700' : 'sb-card' }}">
            <h2 class="font-semibold mb-2 {{ $projectorMode ? 'text-2xl' : '' }} flex items-center gap-2"><i data-lucide="podium" class="w-5 h-5 text-red-600"></i>Leaderboard</h2>
            @forelse($leaderboard as $team)
                <div class="{{ $projectorMode ? 'text-xl' : 'text-sm' }} py-1 border-b border-slate-200/60 last:border-b-0 flex items-center gap-2">
                    <img src="{{ $team->logo_path ? asset('storage/'.$team->logo_path) : asset('images/team-placeholder.svg') }}" alt="{{ $team->name }} logo" class="h-6 w-6 rounded-md object-cover border border-slate-200" />
                    <span>{{ $team->name }} ({{ $team->squad_count }})</span>
                    <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $team->primary_color ?: '#e2e8f0' }}"></span>
                    <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $team->secondary_color ?: '#cbd5e1' }}"></span>
                </div>
            @empty
                <div class="text-sm {{ $darkMode ? 'text-slate-400' : 'text-slate-500' }}">No teams on leaderboard yet.</div>
            @endforelse
        </div>
    </div>
</div>
