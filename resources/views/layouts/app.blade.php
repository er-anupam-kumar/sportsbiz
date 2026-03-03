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
    @livewireStyles
</head>
<body class="min-h-screen sb-shell-bg text-slate-900">
    <div class="min-h-screen">
        <header class="sticky top-0 z-40 border-b border-white/40 sb-glass">
            <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2 font-extrabold text-lg text-slate-900">
                    <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-8 w-auto" />
                </a>
                <div class="flex items-center gap-2 text-sm">
                    <a href="{{ url('/') }}" class="px-3 py-2 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-medium">Home</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-3 py-2 rounded-lg bg-white border border-slate-200 font-medium">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg bg-white border border-slate-200 font-medium">Login</a>
                    @endauth
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-7xl p-4 md:p-6">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', () => window.lucide?.createIcons());
        document.addEventListener('livewire:navigated', () => window.lucide?.createIcons());
    </script>
</body>
</html>
