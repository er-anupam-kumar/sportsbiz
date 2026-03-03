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
    @endphp
    <div class="min-h-screen flex">
        <aside class="fixed inset-y-0 left-0 z-40 w-64 sb-sidebar text-white transform transition md:translate-x-0 shadow-2xl" :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            <div class="h-16 px-4 flex items-center gap-2 border-b border-white/20 tracking-wide">
                <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-8 w-auto" />
            </div>
            <nav class="p-3 space-y-4 text-sm h-[calc(100vh-4rem)] overflow-y-auto">
                <div class="space-y-1">
                    <p class="px-3 sb-section-kicker">Overview</p>
                    <a href="{{ route('super-admin.dashboard') }}" class="sb-nav-link {{ $isActive(['super-admin.dashboard']) ? 'bg-white/20' : '' }}"><i data-lucide="layout-dashboard" class="w-4 h-4"></i>Dashboard</a>
                    <a href="{{ route('super-admin.reports') }}" class="sb-nav-link {{ $isActive(['super-admin.reports']) ? 'bg-white/20' : '' }}"><i data-lucide="bar-chart-3" class="w-4 h-4"></i>Reports</a>
                </div>

                <div class="space-y-1">
                    <p class="px-3 sb-section-kicker">Management</p>
                    <a href="{{ route('super-admin.admins') }}" class="sb-nav-link {{ $isActive(['super-admin.admins']) ? 'bg-white/20' : '' }}"><i data-lucide="users-round" class="w-4 h-4"></i>Admin Manager</a>
                    <a href="{{ route('super-admin.sports') }}" class="sb-nav-link {{ $isActive(['super-admin.sports']) ? 'bg-white/20' : '' }}"><i data-lucide="dumbbell" class="w-4 h-4"></i>Sports Manager</a>
                    <a href="{{ route('super-admin.subscriptions') }}" class="sb-nav-link {{ $isActive(['super-admin.subscriptions']) ? 'bg-white/20' : '' }}"><i data-lucide="badge-dollar-sign" class="w-4 h-4"></i>Subscriptions</a>
                </div>

                <div class="space-y-1">
                    <p class="px-3 sb-section-kicker">Platform</p>
                    <a href="{{ route('super-admin.settings') }}" class="sb-nav-link {{ $isActive(['super-admin.settings']) ? 'bg-white/20' : '' }}"><i data-lucide="settings-2" class="w-4 h-4"></i>Platform Settings</a>
                </div>
            </nav>
        </aside>

        <div class="flex-1 md:ml-64">
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

            <main class="p-4 md:p-6">
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
