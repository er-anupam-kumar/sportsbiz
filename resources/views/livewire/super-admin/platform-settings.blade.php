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
        <div>
            <label class="block text-sm font-medium">Realtime Mode</label>
            <select wire:model="realtimeMode" class="sb-input">
                <option value="polling">Polling (Shared Hosting Safe)</option>
                <option value="websocket">WebSocket (Reverb / Pusher)</option>
            </select>
            <p class="text-xs text-slate-500 mt-1">Use Polling for shared hosting. WebSocket requires long-running realtime infrastructure.</p>
            @error('realtimeMode') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Sound Trigger Mode</label>
            <select wire:model="soundTriggerMode" class="sb-input">
                <option value="polling">Polling Snapshot (No Reverb required)</option>
                <option value="websocket">WebSocket Events (Reverb/Pusher)</option>
            </select>
            <p class="text-xs text-slate-500 mt-1">Controls how hooter/sound is triggered on Admin and Team auction screens.</p>
            @error('soundTriggerMode') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="maintenanceMode">
            Enable Maintenance Mode (platform level flag)
        </label>
        <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary">Save</button>
        <div class="mt-6 border-t pt-4 space-y-2">
            <div class="font-semibold text-sm mb-2">Maintenance Commands</div>
            <div class="flex flex-wrap gap-2 mb-2">
                <button wire:click="runCommand('migrate')" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary text-xs font-semibold">Migrate</button>
                <button wire:click="runCommand('storage:link')" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary text-xs font-semibold">Storage Link</button>
                <button wire:click="runCommand('composer update')" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary text-xs font-semibold">Composer Update</button>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="runCommand('cache:clear')" wire:loading.attr="disabled" class="px-3 py-1 rounded bg-blue-600 text-white text-xs">Cache Clear</button>
                <button wire:click="runCommand('config:clear')" wire:loading.attr="disabled" class="px-3 py-1 rounded bg-indigo-600 text-white text-xs">Config Clear</button>
                <button wire:click="runCommand('route:clear')" wire:loading.attr="disabled" class="px-3 py-1 rounded bg-indigo-600 text-white text-xs">Route Clear</button>
                <button wire:click="runCommand('view:clear')" wire:loading.attr="disabled" class="px-3 py-1 rounded bg-indigo-600 text-white text-xs">View Clear</button>
                <button wire:click="runCommand('optimize')" wire:loading.attr="disabled" class="px-3 py-1 rounded bg-green-600 text-white text-xs">Optimize</button>
                <button wire:click="runCommand('optimize:clear')" wire:loading.attr="disabled" class="px-3 py-1 rounded bg-green-600 text-white text-xs">Optimize Clear</button>
                <button wire:click="runCommand('composer install')" wire:loading.attr="disabled" class="px-3 py-1 rounded bg-slate-700 text-white text-xs">Composer Install</button>
                <button wire:click="runCommand('composer dump-autoload')" wire:loading.attr="disabled" class="px-3 py-1 rounded bg-slate-700 text-white text-xs">Composer Dump Autoload</button>
            </div>
            <div wire:loading wire:target="runCommand" class="text-xs text-slate-500 mt-2">Running command...</div>
            @if(session('commandOutput'))
                <div class="mt-2 p-2 rounded bg-slate-100 text-xs text-slate-700 border border-slate-300">
                    <strong>Output:</strong>
                    <pre class="whitespace-pre-wrap">{{ session('commandOutput') }}</pre>
                </div>
            @endif
        </div>
    </div>
</div>
