<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Auth</title>
    <script defer src="https://unpkg.com/lucide@latest"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="min-h-screen sb-shell-bg text-slate-900">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-md sb-glass rounded-2xl p-6 md:p-7 space-y-5">
            <div class="flex items-center justify-center gap-2 text-slate-900">
                <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-10 w-auto" />
            </div>
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', () => window.lucide?.createIcons());
        document.addEventListener('livewire:navigated', () => window.lucide?.createIcons());
    </script>
</body>
</html>
