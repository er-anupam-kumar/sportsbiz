<div x-show="lockedPopup" x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm" x-cloak>
    <div class="relative w-full max-w-2xl overflow-hidden rounded-[2rem] border border-violet-300/35 bg-gradient-to-br from-violet-700 via-fuchsia-600 to-indigo-700 px-6 py-5 text-white shadow-2xl">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.26),_transparent_34%),radial-gradient(circle_at_bottom,_rgba(255,255,255,0.12),_transparent_28%)]"></div>
        <span class="absolute left-8 top-8 h-3 w-3 rounded-full bg-amber-200 animate-ping"></span>
        <span class="absolute left-20 top-20 h-2.5 w-2.5 rounded-full bg-emerald-300 animate-ping" style="animation-delay:.2s"></span>
        <span class="absolute right-16 top-14 h-3 w-3 rounded-full bg-white/70 animate-ping" style="animation-delay:.35s"></span>
        <span class="absolute right-10 bottom-12 h-2.5 w-2.5 rounded-full bg-rose-200 animate-ping" style="animation-delay:.5s"></span>
        <span class="absolute left-1/3 bottom-10 h-2.5 w-2.5 rounded-full bg-cyan-100 animate-ping" style="animation-delay:.75s"></span>

        <div class="relative">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white/95 shadow-lg shadow-violet-950/30 ring-4 ring-white/20">
                <svg viewBox="0 0 24 24" class="h-11 w-11 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
            </div>

            <div class="mt-3 text-center">
                    <div class="text-4xl font-black tracking-wide">LOCKED</div>
                <p class="mt-1 text-sm text-white/85">Player locked successfully</p>
            </div>

            <div class="mt-5 grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-4 rounded-[1.5rem] border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-md" x-show="lockedPlayer" x-cloak>
                <div class="flex min-w-0 items-center gap-3">
                    <template x-if="lockedPlayer?.image_url">
                        <img :src="lockedPlayer.image_url" alt="Player" class="h-16 w-16 rounded-full border-4 border-white/25 object-cover shadow-md shadow-blue-900/20" />
                    </template>
                    <template x-if="!lockedPlayer?.image_url">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-white/25 bg-gradient-to-br from-violet-400 to-indigo-600 text-xl font-black uppercase shadow-md shadow-blue-900/20" x-text="(lockedPlayer?.name || 'P').split(' ').map(part => part[0]).join('').slice(0, 2)"></div>
                    </template>
                    <div class="min-w-0">
                        <div class="truncate text-2xl font-black" x-text="lockedPlayer?.name || 'Player'"></div>
                        <div class="mt-0.5 text-sm text-white/75">Player</div>
                        <div class="mt-1 text-xs text-white/80">Serial: <span x-text="lockedPlayer?.serial_no ?? '-' "></span></div>
                    </div>
                </div>

                <div class="hidden h-16 w-px bg-white/20 md:block"></div>

                <div class="flex min-w-0 items-center justify-end gap-3 text-right">
                    <div class="min-w-0 order-2 md:order-1">
                        <div class="truncate text-2xl font-black" x-text="lockedPlayer?.team || 'Winner'">Winner</div>
                        <div class="mt-0.5 text-sm text-white/75">New Owner</div>
                        <div class="mt-1 text-xs text-white/80">Category: <span x-text="lockedPlayer?.category || 'Uncategorized'"></span></div>
                    </div>
                    <template x-if="lockedPlayer?.team_logo_url">
                        <img :src="lockedPlayer.team_logo_url" alt="Winner team" class="order-1 h-16 w-16 rounded-full border-4 border-white/25 bg-white/10 object-cover shadow-md shadow-blue-900/20 md:order-2" />
                    </template>
                    <template x-if="!lockedPlayer?.team_logo_url">
                        <div class="order-1 flex h-16 w-16 items-center justify-center rounded-full border-4 border-white/25 bg-gradient-to-br from-orange-400 to-rose-600 text-xl font-black uppercase shadow-md shadow-blue-900/20 md:order-2" x-text="(lockedPlayer?.team || 'W').split(' ').map(part => part[0]).join('').slice(0, 2)"></div>
                    </template>
                </div>
            </div>

            <div class="mt-4 rounded-[1.4rem] border border-white/15 bg-white/12 px-4 py-5 text-center shadow-inner shadow-violet-950/10" x-show="lockedPlayer" x-cloak>
                <div class="text-sm font-semibold text-white/80">Final Bid Amount</div>
                <div class="mt-1 text-4xl font-black tracking-wide text-amber-300" x-text="Number(lockedPlayer?.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></div>
            </div>

            <div class="mt-4 text-center text-sm text-white/80">
                Preparing next player in <span class="font-bold text-white" x-text="lockedCountdown || 0"></span>s...
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/20">
                <div class="h-full origin-left rounded-full bg-gradient-to-r from-amber-300 via-rose-300 to-violet-200" :style="`transform: scaleX(${(lockedProgress || 0) / 100})`"></div>
            </div>
        </div>
    </div>
</div>
