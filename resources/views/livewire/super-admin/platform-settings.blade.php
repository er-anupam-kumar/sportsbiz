<div class="space-y-4">
    <div class="flex items-center gap-3">
        <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-9 w-9 object-contain rounded-lg border border-slate-200 p-1 bg-white" />
        <h1 class="text-2xl font-bold">Platform Settings</h1>
    </div>
    <div class="sb-card p-4 max-w-2xl space-y-4">
        <div>
            <label class="block text-sm font-medium">Platform Commission (%)</label>
            <input wire:model="commissionPercent" type="number" step="0.01" class="sb-input">
            @error('commissionPercent') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Default Payment Gateway</label>
            <select wire:model="defaultGateway" class="sb-input">
                <option value="stripe">Stripe</option>
                <option value="razorpay">Razorpay</option>
            </select>
            @error('defaultGateway') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="maintenanceMode">
            Enable Maintenance Mode (platform level flag)
        </label>
        <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary">Save</button>
    </div>
</div>
