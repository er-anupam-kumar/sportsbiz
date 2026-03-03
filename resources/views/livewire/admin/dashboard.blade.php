<div class="space-y-4">
    <div class="rounded-2xl bg-gradient-to-r from-amber-700 via-rose-700 to-emerald-700 text-white p-5 shadow-lg">
        <h1 class="text-2xl font-extrabold">Admin Dashboard</h1>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        <div class="sb-card p-4 border-t-4 border-amber-700">Tournaments: {{ $tournaments }}</div>
        <div class="sb-card p-4 border-t-4 border-emerald-700">Team Users: {{ $teams }}</div>
    </div>
</div>
