<div class="space-y-5">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2"><i data-lucide="key-round" class="w-5 h-5 text-amber-600"></i>Forgot password</h2>
        <p class="text-sm text-slate-600 mt-1">We’ll send a secure reset link to your inbox.</p>
    </div>
    @if($status)
        <div class="text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl p-2.5">{{ $status }}</div>
    @endif
    <form wire:submit="sendResetLink" class="space-y-3">
        <div>
            <label class="block text-sm mb-1 font-medium text-slate-700">Email</label>
            <input wire:model="email" type="email" class="sb-input" />
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <button type="submit" wire:loading.attr="disabled" class="w-full sb-btn-primary px-4 py-2.5">Send reset link</button>
    </form>
    <a href="{{ route('login') }}" class="text-sm text-amber-700 hover:underline font-medium">Back to login</a>
</div>
