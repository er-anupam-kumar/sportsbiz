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
        <header class="max-w-6xl mx-auto sb-glass rounded-2xl px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2 text-slate-900 font-extrabold text-lg">
                <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-9 w-auto" />
            </div>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg border border-slate-300 bg-white/90 text-slate-700 font-semibold">Login</a>
                <a href="{{ route('register') }}" class="px-3 py-2 rounded-lg text-white font-semibold sb-btn-primary shadow-lg">Register</a>
            </div>
        </header>

        <main class="max-w-6xl mx-auto mt-8 grid lg:grid-cols-2 gap-6 items-stretch">
            <section class="sb-glass rounded-2xl p-6 md:p-8 space-y-4">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    LIVE AUCTION OS
                </span>
                <h1 class="text-3xl md:text-5xl font-black leading-tight">
                    Run <span class="sb-brand-gradient">yummy shiny</span> sports auctions at scale.
                </h1>
                <p class="text-slate-700 text-base md:text-lg">
                    Multi-tenant auction management for leagues, teams, and real-time bidding with strict controls.
                </p>
                <div class="grid sm:grid-cols-2 gap-3 pt-2">
                    <div class="sb-card p-4">
                        <div class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="radio" class="w-4 h-4 text-amber-700"></i>Real-time Broadcast</div>
                        <p class="text-sm text-slate-600 mt-1">Live bid updates for every room.</p>
                    </div>
                    <div class="sb-card p-4">
                        <div class="flex items-center gap-2 font-bold text-slate-900"><i data-lucide="shield-check" class="w-4 h-4 text-emerald-700"></i>Policy Guardrails</div>
                        <p class="text-sm text-slate-600 mt-1">Role, tenant, and budget-safe operations.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl p-6 md:p-8 bg-gradient-to-br from-slate-900 via-rose-800 to-emerald-800 text-white shadow-2xl">
                <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="layout-dashboard" class="w-5 h-5"></i>Quick Access</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <a href="{{ route('login') }}" class="flex items-center justify-between rounded-xl bg-white/15 hover:bg-white/25 px-4 py-3">
                        <span class="flex items-center gap-2"><i data-lucide="crown" class="w-4 h-4"></i>SuperAdmin Panel</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('login') }}" class="flex items-center justify-between rounded-xl bg-white/15 hover:bg-white/25 px-4 py-3">
                        <span class="flex items-center gap-2"><i data-lucide="shield" class="w-4 h-4"></i>Admin Panel</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('login') }}" class="flex items-center justify-between rounded-xl bg-white/15 hover:bg-white/25 px-4 py-3">
                        <span class="flex items-center gap-2"><i data-lucide="flag" class="w-4 h-4"></i>Team Panel</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => window.lucide?.createIcons());
    </script>
</body>
</html>
