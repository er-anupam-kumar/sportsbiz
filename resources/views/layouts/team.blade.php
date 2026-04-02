<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SportsBiz') }} - Team</title>
    <script defer src="https://unpkg.com/lucide@latest"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="min-h-screen sb-shell-bg text-slate-900 overflow-x-hidden" x-data="{ mobileSidebar: false, desktopSidebarOpen: true, toast: '' }" x-on:toast.window="toast = $event.detail.message; setTimeout(() => toast = '', 2200)">
    @php
        $teamModel = \App\Models\Team::query()->where('user_id', auth()->id())->first();
        $teamTournamentId = $teamModel?->tournament_id;
        $currentRoute = request()->route()?->getName();
        $isActive = fn (array $names): bool => in_array($currentRoute, $names, true);
        $activeSection = $isActive(['team.auction-room', 'team.squad', 'team.bid-history', 'team.jersey-requirements']) ? 'auction' : 'overview';
    @endphp
    <div class="min-h-screen flex w-full min-w-0 overflow-x-hidden">
        <aside class="fixed inset-y-0 left-0 z-40 w-64 sb-sidebar text-white transform transition shadow-2xl" :class="mobileSidebar ? 'translate-x-0' : (desktopSidebarOpen ? '-translate-x-full md:translate-x-0' : '-translate-x-full md:-translate-x-full')">
            <div class="h-16 px-4 flex items-center gap-2 border-b border-white/20 tracking-wide">
                <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-8 w-auto" />
            </div>
            <nav class="p-3 space-y-3 text-sm h-[calc(100vh-4rem)] overflow-y-auto" x-data="{ activeSection: @js($activeSection) }">
                <div class="space-y-1">
                    <button type="button" @click="activeSection = activeSection === 'overview' ? '' : 'overview'" class="w-full px-3 py-2 flex items-center justify-between rounded-lg hover:bg-white/10">
                        <span class="sb-section-kicker">Overview</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="activeSection === 'overview' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="activeSection === 'overview'" class="space-y-1">
                        <a href="{{ route('team.dashboard') }}" class="sb-nav-link {{ $isActive(['team.dashboard']) ? 'bg-white/20' : '' }}"><i data-lucide="layout-dashboard" class="w-4 h-4"></i>Dashboard</a>
                    </div>
                </div>
                @if($teamTournamentId)
                    <div class="space-y-1">
                        <button type="button" @click="activeSection = activeSection === 'auction' ? '' : 'auction'" class="w-full px-3 py-2 flex items-center justify-between rounded-lg hover:bg-white/10">
                            <span class="sb-section-kicker">Auction</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="activeSection === 'auction' ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="activeSection === 'auction'" class="space-y-1">
                            <a href="{{ route('team.auction-room', $teamTournamentId) }}" class="sb-nav-link {{ $isActive(['team.auction-room']) ? 'bg-white/20' : '' }}"><i data-lucide="gavel" class="w-4 h-4"></i>Auction Room</a>
                            <a href="{{ route('team.squad', $teamTournamentId) }}" class="sb-nav-link {{ $isActive(['team.squad']) ? 'bg-white/20' : '' }}"><i data-lucide="users" class="w-4 h-4"></i>Squad</a>
                            <a href="{{ route('team.bid-history', $teamTournamentId) }}" class="sb-nav-link {{ $isActive(['team.bid-history']) ? 'bg-white/20' : '' }}"><i data-lucide="scroll-text" class="w-4 h-4"></i>Bid History</a>
                            <a href="{{ route('team.jersey-requirements') }}" class="sb-nav-link {{ $isActive(['team.jersey-requirements']) ? 'bg-white/20' : '' }}"><i data-lucide="shirt" class="w-4 h-4"></i>Jersey Requirements</a>
                        </div>
                    </div>
                @endif
            </nav>
        </aside>

        <div class="flex-1 min-w-0 overflow-x-hidden transition-all duration-200" :class="desktopSidebarOpen ? 'md:ml-64' : 'md:ml-0'">
            <header class="h-16 sb-glass sb-topbar px-4 md:px-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button class="md:hidden px-3 py-2 rounded-lg sb-menu-btn flex items-center gap-1" @click="mobileSidebar = !mobileSidebar"><i data-lucide="menu" class="w-4 h-4"></i>Menu</button>
                    <button class="hidden md:inline-flex px-3 py-2 rounded-lg sb-menu-btn items-center gap-1" @click="desktopSidebarOpen = !desktopSidebarOpen">
                        <i data-lucide="panel-left-close" class="w-4 h-4" x-show="desktopSidebarOpen"></i>
                        <i data-lucide="panel-left-open" class="w-4 h-4" x-show="!desktopSidebarOpen" x-cloak></i>
                        <span x-text="desktopSidebarOpen ? 'Hide Sidebar' : 'Show Sidebar'"></span>
                    </button>
                </div>
                <div class="hidden md:block text-sm font-semibold sb-brand-gradient">Bidder Command Center</div>
                <div class="relative" x-data="{ open: false }">
                    <button class="px-3 py-2 rounded-lg sb-user-btn font-medium flex items-center gap-2" @click="open = !open"><i data-lucide="user-circle-2" class="w-4 h-4"></i>{{ auth()->user()?->name ?? 'Team User' }}</button>
                    <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-56 sb-glass border rounded-xl shadow-xl z-20" x-cloak>
                        <div class="px-3 py-2 text-xs text-slate-500 border-b">{{ auth()->user()?->email }}</div>
                        <form method="GET" action="{{ route('logout') }}">
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-xl flex items-center gap-2"><i data-lucide="log-out" class="w-4 h-4"></i>Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="p-4 md:p-6 w-full min-w-0 overflow-x-hidden">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div x-show="toast" x-transition class="fixed top-4 right-4 sb-toast px-4 py-2 rounded-lg shadow-xl" x-text="toast" x-cloak></div>

    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', () => window.lucide?.createIcons());
        document.addEventListener('livewire:navigated', () => window.lucide?.createIcons());
    </script>
</body>
</html>
