<div class="space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-amber-700 via-rose-700 to-emerald-700 text-white p-5 shadow-lg">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold">SuperAdmin Dashboard</h1>
                <p class="text-sm text-white/90">Platform level controls and analytics.</p>
            </div>
            <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-10 w-10 object-contain rounded-lg bg-white/10 p-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="sb-card p-5 border-t-4 border-amber-700">
            <p class="text-sm text-slate-500 flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-600"></span>Total Admin Accounts</p>
            <p class="text-3xl font-bold mt-2">{{ $adminCount }}</p>
        </div>
        <div class="sb-card p-5 border-t-4 border-rose-600">
            <p class="text-sm text-slate-500 flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>Active / Paused Tournaments</p>
            <p class="text-3xl font-bold mt-2">{{ $activeTournaments }}</p>
        </div>
        <div class="sb-card p-5 border-t-4 border-emerald-700">
            <p class="text-sm text-slate-500 flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>Successful Payment Volume</p>
            <p class="text-3xl font-bold mt-2">{{ number_format($successfulPayments, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('super-admin.admins') }}" class="sb-card p-4 hover:bg-amber-50/70 border border-slate-200">Manage Admin Accounts</a>
        <a href="{{ route('super-admin.sports') }}" class="sb-card p-4 hover:bg-rose-50/70 border border-slate-200">Manage Sports</a>
        <a href="{{ route('super-admin.subscriptions') }}" class="sb-card p-4 hover:bg-emerald-50/70 border border-slate-200">Manage Subscriptions</a>
        <a href="{{ route('super-admin.settings') }}" class="sb-card p-4 hover:bg-rose-50/70 border border-slate-200">Platform Settings</a>
        <a href="{{ route('super-admin.reports') }}" class="sb-card p-4 hover:bg-amber-50/70 border border-slate-200">Reports & Analytics</a>
    </div>
</div>
