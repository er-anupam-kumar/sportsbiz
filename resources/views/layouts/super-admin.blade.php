<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SportsBiz') }} - SuperAdmin</title>
    <script defer src="https://unpkg.com/lucide@latest"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="min-h-screen sb-shell-bg text-slate-900" x-data="{ mobileSidebar: false, toast: '' }" x-on:toast.window="toast = $event.detail.message; setTimeout(() => toast = '', 2200)">
    @php
        $currentRoute = request()->route()?->getName();
        $isActive = fn (array $names): bool => in_array($currentRoute, $names, true);
        $activeSection = 'overview';

        if ($isActive(['super-admin.admins', 'super-admin.sports', 'super-admin.subscriptions'])) {
            $activeSection = 'management';
        }

        if ($isActive(['super-admin.settings'])) {
            $activeSection = 'platform';
        }
    @endphp
    <div class="min-h-screen flex">
        <aside class="fixed inset-y-0 left-0 z-40 w-64 sb-sidebar text-white transform transition md:translate-x-0 shadow-2xl" :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            <div class="h-16 px-4 flex items-center gap-2 border-b border-white/20 tracking-wide">
                <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-8 w-auto" />
            </div>
            <nav class="p-3 space-y-3 text-sm h-[calc(100vh-4rem)] overflow-y-auto" x-data="{ activeSection: @js($activeSection) }">
                <div class="space-y-1">
                    <button type="button" @click="activeSection = activeSection === 'overview' ? '' : 'overview'" class="w-full px-3 py-2 flex items-center justify-between rounded-lg hover:bg-white/10">
                        <span class="sb-section-kicker">Overview</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="activeSection === 'overview' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="activeSection === 'overview'" class="space-y-1 ml-6 pl-3 border-l border-white/25">
                        <a href="{{ route('super-admin.dashboard') }}" class="sb-nav-link pl-4 {{ $isActive(['super-admin.dashboard']) ? 'bg-white/20' : '' }}">Dashboard</a>
                        <a href="{{ route('super-admin.reports') }}" class="sb-nav-link pl-4 {{ $isActive(['super-admin.reports']) ? 'bg-white/20' : '' }}">Reports</a>
                    </div>
                </div>

                <div class="space-y-1">
                    <button type="button" @click="activeSection = activeSection === 'management' ? '' : 'management'" class="w-full px-3 py-2 flex items-center justify-between rounded-lg hover:bg-white/10">
                        <span class="sb-section-kicker">Management</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="activeSection === 'management' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="activeSection === 'management'" class="space-y-1 ml-6 pl-3 border-l border-white/25">
                        <a href="{{ route('super-admin.admins') }}" class="sb-nav-link pl-4 {{ $isActive(['super-admin.admins']) ? 'bg-white/20' : '' }}">Admin Manager</a>
                        <a href="{{ route('super-admin.sports') }}" class="sb-nav-link pl-4 {{ $isActive(['super-admin.sports']) ? 'bg-white/20' : '' }}">Sports Manager</a>
                        <a href="{{ route('super-admin.subscriptions') }}" class="sb-nav-link pl-4 {{ $isActive(['super-admin.subscriptions']) ? 'bg-white/20' : '' }}">Subscriptions</a>
                    </div>
                </div>

                <div class="space-y-1">
                    <button type="button" @click="activeSection = activeSection === 'platform' ? '' : 'platform'" class="w-full px-3 py-2 flex items-center justify-between rounded-lg hover:bg-white/10">
                        <span class="sb-section-kicker">Platform</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="activeSection === 'platform' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="activeSection === 'platform'" class="space-y-1 ml-6 pl-3 border-l border-white/25">
                        <a href="{{ route('super-admin.settings') }}" class="sb-nav-link pl-4 {{ $isActive(['super-admin.settings']) ? 'bg-white/20' : '' }}">Platform Settings</a>
                    </div>
                </div>
            </nav>
        </aside>

        <div class="flex-1 md:ml-64 h-screen flex flex-col overflow-hidden">
            <header class="h-16 sb-glass sb-topbar px-4 md:px-6 flex items-center justify-between">
                <button class="md:hidden px-3 py-2 rounded-lg sb-menu-btn flex items-center gap-1" @click="mobileSidebar = !mobileSidebar"><i data-lucide="menu" class="w-4 h-4"></i>Menu</button>
                <div class="hidden md:block text-sm font-semibold sb-brand-gradient">SuperAdmin Control Arena</div>
                <div class="relative" x-data="{ open: false }">
                    <button class="px-3 py-2 rounded-lg sb-user-btn font-medium flex items-center gap-2" @click="open = !open"><i data-lucide="user-circle-2" class="w-4 h-4"></i>{{ auth()->user()?->name ?? 'User' }}</button>
                    <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-56 sb-glass rounded-xl border border-slate-200 shadow-xl z-20" x-cloak>
                        <div class="px-3 py-2 text-xs text-slate-500 border-b">{{ auth()->user()?->email }}</div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-xl flex items-center gap-2"><i data-lucide="log-out" class="w-4 h-4"></i>Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-6">
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
