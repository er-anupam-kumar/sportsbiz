<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SportsBiz') }}</title>
    <script defer src="https://unpkg.com/lucide@latest"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen sb-shell-bg text-slate-900">
    <div class="min-h-screen px-4 py-6 md:px-8 md:py-10">
        <header class="max-w-6xl mx-auto sb-glass rounded-2xl px-5 py-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-slate-900 font-extrabold text-lg min-w-0">
                <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-9 w-auto" />
                <span class="truncate">SportsBiz Auction Platform</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('public.tournaments.index') }}" class="px-3 py-2 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 font-semibold">Tournaments</a>
                @auth
                    <a href="{{ auth()->user()->hasRole('SuperAdmin') ? route('super-admin.dashboard') : (auth()->user()->hasRole('Admin') ? route('admin.dashboard') : route('team.dashboard')) }}" class="px-3 py-2 rounded-lg text-white font-semibold sb-btn-primary shadow-lg">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg border border-slate-300 bg-white/90 text-slate-700 font-semibold">Login</a>
                    <a href="{{ route('register') }}" class="px-3 py-2 rounded-lg text-white font-semibold sb-btn-primary shadow-lg">Register</a>
                @endauth
            </div>
        </header>

        <main class="max-w-6xl mx-auto mt-8 space-y-6">
            <section class="sb-glass rounded-2xl p-6 md:p-8">
                <div class="grid lg:grid-cols-2 gap-6 items-center">
                    <div class="space-y-4">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            <i data-lucide="gavel" class="w-4 h-4"></i>
                            AUCTION COMMAND CENTER
                        </span>
                        <h1 class="text-3xl md:text-5xl font-black leading-tight">Manage sports auctions with one clear platform.</h1>
                        <p class="text-slate-700 text-base md:text-lg">Create tournaments, run live bidding, and track squads, bids, and reports across SuperAdmin, Admin, and Team roles.</p>
                        <div class="flex flex-wrap gap-2 pt-1">
                            @auth
                                <a href="{{ auth()->user()->hasRole('SuperAdmin') ? route('super-admin.dashboard') : (auth()->user()->hasRole('Admin') ? route('admin.dashboard') : route('team.dashboard')) }}" class="px-4 py-2 rounded-lg text-white font-semibold sb-btn-primary shadow-lg">Open Dashboard</a>
                            @else
                                <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg text-white font-semibold sb-btn-primary shadow-lg">Start Now</a>
                                <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg border border-slate-300 bg-white/90 text-slate-700 font-semibold">Sign In</a>
                            @endauth
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div class="sb-card p-4">
                            <div class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="radio" class="w-4 h-4 text-amber-700"></i>Live Auction Room</div>
                            <p class="text-sm text-slate-600 mt-1">Real-time bidding screen with current player, timer, and bid status.</p>
                        </div>
                        <div class="sb-card p-4">
                            <div class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="users" class="w-4 h-4 text-indigo-700"></i>Team & Player Control</div>
                            <p class="text-sm text-slate-600 mt-1">Manage teams, categories, players, and tournament assignments.</p>
                        </div>
                        <div class="sb-card p-4">
                            <div class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="bar-chart-3" class="w-4 h-4 text-emerald-700"></i>Reports & Exports</div>
                            <p class="text-sm text-slate-600 mt-1">Track sold players, budgets, and downloadable tournament reports.</p>
                        </div>
                        <div class="sb-card p-4">
                            <div class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="shield-check" class="w-4 h-4 text-rose-700"></i>Role-based Security</div>
                            <p class="text-sm text-slate-600 mt-1">SuperAdmin, Admin, and Team access controls with tenant boundaries.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid md:grid-cols-3 gap-4">
                <a href="{{ route('login') }}" class="sb-card p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="crown" class="w-5 h-5 text-amber-700"></i>SuperAdmin</div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <p class="text-sm text-slate-600 mt-2">Control platform settings, sports, subscriptions, and admins.</p>
                </a>
                <a href="{{ route('login') }}" class="sb-card p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="shield" class="w-5 h-5 text-indigo-700"></i>Admin</div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <p class="text-sm text-slate-600 mt-2">Create tournaments, run auctions, and manage players and teams.</p>
                </a>
                <a href="{{ route('login') }}" class="sb-card p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="flag" class="w-5 h-5 text-emerald-700"></i>Team</div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <p class="text-sm text-slate-600 mt-2">Join live bidding rooms and monitor squad composition.</p>
                </a>
            </section>

            <section class="sb-glass rounded-2xl p-5 md:p-6 space-y-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-900 flex items-center gap-2">
                            <i data-lucide="radio-tower" class="w-5 h-5 text-indigo-700"></i>
                            Current Tournaments & Live Auctions
                        </h2>
                        <p class="text-sm text-slate-600 mt-1">Track tournament status and jump directly to the live auction view.</p>
                    </div>
                    <a href="{{ route('public.tournaments.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 text-sm font-semibold hover:bg-indigo-100 transition">
                        <i data-lucide="trophy" class="w-4 h-4"></i>
                        Browse Tournaments
                    </a>
                </div>

                @if(($tournaments ?? collect())->isEmpty())
                    <div class="rounded-xl border border-slate-200 bg-white/70 p-4 text-sm text-slate-600">No tournaments available yet.</div>
                @else
                    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($tournaments as $tournament)
                            @php
                                $isRunning = in_array($tournament->id, $runningTournamentIds ?? [], true);
                                $auctionStarted = (bool) ($tournament->auction?->current_player_id);
                                $auctionCompleted = (bool) ($tournament->auction?->is_completed);
                            @endphp
                            <article class="sb-card p-4 bg-white border border-slate-200 shadow-sm space-y-3">
                                <img src="{{ $tournament->banner_url }}" alt="{{ $tournament->name }} banner" class="w-full h-28 rounded-lg object-cover border border-slate-200">

                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-base font-bold text-slate-900 truncate">{{ $tournament->name }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $tournament->sport?->name ?? 'Sport' }}</div>
                                    </div>
                                    <span class="text-[11px] px-2 py-1 rounded-full font-semibold {{ $isRunning ? 'bg-emerald-100 text-emerald-700' : ($auctionStarted ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $isRunning ? 'LIVE' : ($auctionStarted ? 'PAUSED' : 'NOT STARTED') }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5">
                                        <div class="text-[10px] uppercase tracking-wide text-slate-500">Status</div>
                                        <div class="font-semibold text-slate-800">{{ strtoupper((string) ($tournament->status ?? 'draft')) }}</div>
                                    </div>
                                    <div class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5">
                                        <div class="text-[10px] uppercase tracking-wide text-slate-500">Start</div>
                                        <div class="font-semibold text-slate-800">{{ $tournament->starts_at ? $tournament->starts_at->format('d M Y') : 'TBD' }}</div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('public.tournaments.show', $tournament->id) }}" class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 text-sm font-semibold hover:bg-indigo-100 transition">View Fixtures</a>
                                    @if(!$auctionCompleted)
                                        <a href="{{ route('public.auction-viewer', $tournament->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-white text-sm font-semibold sb-btn-primary shadow">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                            Open Auction
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-3 py-2 rounded-lg border border-slate-200 bg-slate-100 text-slate-500 text-sm font-semibold">Auction Closed</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => window.lucide?.createIcons());
    </script>
</body>
</html>
