<div x-show="roundCompletePopup" x-transition.opacity class="fixed inset-0 z-[132] flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm" x-cloak>
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
                <div class="text-4xl font-black tracking-wide">ROUND COMPLETE</div>
                <p class="mt-1 text-sm text-white/85">This round of auction is complete</p>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-4 rounded-[1.5rem] border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-md">
                <div class="rounded-2xl border border-emerald-300/35 bg-emerald-400/10 px-4 py-3 text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-white/80">Sold Players</div>
                    <div class="mt-1 text-4xl font-black text-emerald-200" x-text="roundSoldCount || 0"></div>
                </div>
                <div class="rounded-2xl border border-amber-300/35 bg-amber-400/10 px-4 py-3 text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-white/80">Unsold Players</div>
                    <div class="mt-1 text-4xl font-black text-amber-200" x-text="roundUnsoldCount || 0"></div>
                </div>
            </div>

            <div class="mt-4 text-center text-sm text-white/80">
                Preparing recycled pool for the next round...
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/20">
                <div class="h-full origin-left rounded-full bg-gradient-to-r from-amber-300 via-rose-300 to-violet-200" :style="`transform: scaleX(${(roundProgress || 0) / 100})`"></div>
            </div>
        </div>
    </div>
</div>
