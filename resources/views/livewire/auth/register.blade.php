<div class="space-y-5">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2"><i data-lucide="user-plus" class="w-5 h-5 text-amber-600"></i>Create account</h2>
        <p class="text-sm text-slate-600 mt-1">Join your team and start bidding.</p>
    </div>

    <form wire:submit="register" class="space-y-3">
        <div>
            <label class="block text-sm mb-1 font-medium text-slate-700">Name</label>
            <input wire:model="name" type="text" class="sb-input" />
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
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
        <div>
            <label class="block text-sm mb-1 font-medium text-slate-700">Confirm Password</label>
            <input wire:model="password_confirmation" type="password" class="sb-input" />
        </div>
        <button type="submit" wire:loading.attr="disabled" class="w-full sb-btn-primary px-4 py-2.5" wire:target="register">
            <span class="inline-flex items-center">
                <svg wire:loading wire:target="register" class="animate-spin h-4 w-4 mr-1 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                <span wire:loading wire:target="register">Loading...</span>
                <span wire:loading.remove wire:target="register">Register</span>
            </span>
        </button>
    </form>

    <div class="text-sm">
        <a href="{{ route('login') }}" class="text-amber-700 hover:underline font-medium">Already have an account?</a>
    </div>
</div>
