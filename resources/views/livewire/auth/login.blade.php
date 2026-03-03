<div class="space-y-5">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2"><i data-lucide="log-in" class="w-5 h-5 text-amber-600"></i>Sign in</h2>
        <p class="text-sm text-slate-600 mt-1">Access your auction command center.</p>
    </div>

    @if (session('status'))
        <div class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl p-2.5">{{ session('status') }}</div>
    @endif

    <form wire:submit="login" class="space-y-3">
        <div>
            <label class="block text-sm mb-1 font-medium text-slate-700">Email</label>
            <input wire:model="email" type="email" class="sb-input" />
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm mb-1 font-medium text-slate-700">Password</label>
            <input wire:model="password" type="password" class="sb-input" />
            @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input wire:model="remember" type="checkbox" /> Remember me
        </label>

        <button type="submit" wire:loading.attr="disabled" class="w-full sb-btn-primary px-4 py-2.5">Login</button>
    </form>

    <div class="text-sm flex justify-between">
        <a href="{{ route('register') }}" class="text-amber-700 hover:underline font-medium">Create account</a>
        <a href="{{ route('password.request') }}" class="text-amber-700 hover:underline font-medium">Forgot password?</a>
    </div>
</div>
