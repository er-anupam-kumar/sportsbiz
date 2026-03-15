<div class="space-y-5">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2"><i data-lucide="lock-keyhole" class="w-5 h-5 text-amber-600"></i>Reset password</h2>
        <p class="text-sm text-slate-600 mt-1">Set a fresh password and get back into the action.</p>
    </div>
    @if($status)
        <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl p-2.5">{{ $status }}</div>
    @endif
    <form wire:submit="resetPassword" class="space-y-3">
        <div>
            <label class="block text-sm mb-1 font-medium text-slate-700">Email</label>
            <input wire:model="email" type="email" class="sb-input" />
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm mb-1 font-medium text-slate-700">New Password</label>
            <input wire:model="password" type="password" class="sb-input" />
            @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm mb-1 font-medium text-slate-700">Confirm New Password</label>
            <input wire:model="password_confirmation" type="password" class="sb-input" />
        </div>
        <button type="submit" wire:loading.attr="disabled" class="w-full sb-btn-primary px-4 py-2.5" wire:target="resetPassword">
            <span class="inline-flex items-center">
                <svg wire:loading wire:target="resetPassword" class="animate-spin h-4 w-4 mr-1 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                <span wire:loading wire:target="resetPassword">Loading...</span>
                <span wire:loading.remove wire:target="resetPassword">Reset password</span>
            </span>
        </button>
    </form>
</div>
