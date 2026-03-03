<div class="space-y-4">
    <div class="flex flex-wrap gap-2 items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-9 w-9 object-contain rounded-lg border border-slate-200 p-1 bg-white" />
            <h1 class="text-2xl font-bold">Admin Manager</h1>
        </div>
        <button wire:click="openCreate" class="px-4 py-2 sb-btn-primary">+ Create Admin</button>
    </div>

    <div class="sb-card p-4 max-w-xl">
        <label class="block text-sm font-medium mb-1">Search Admin</label>
        <input wire:model.live.debounce.400ms="search" class="sb-input" placeholder="Search admin by name/email">
    </div>

    @if($formOpen)
        <div class="sb-card p-4 space-y-3">
            <h2 class="font-semibold">{{ $editingId ? 'Edit Admin' : 'Create Admin' }}</h2>
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="text-sm">Name</label>
                    <input wire:model="name" class="sb-input">
                    @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm">Email</label>
                    <input wire:model="email" type="email" class="sb-input">
                    @error('email') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm">Phone</label>
                    <input wire:model="phone" class="sb-input">
                    @error('phone') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm">Password {{ $editingId ? '(optional)' : '' }}</label>
                    <input wire:model="password" type="password" class="sb-input">
                    @error('password') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-2">
                <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary">Save</button>
                <button wire:click="resetForm" class="px-4 py-2 border border-slate-300 rounded-lg">Cancel</button>
            </div>
        </div>
    @endif

    <div class="sb-card overflow-x-auto">
        <table class="w-full">
            <thead class="text-left text-sm text-slate-700 border-b bg-gradient-to-r from-amber-50 via-rose-50 to-emerald-50">
                <tr>
                    <th class="p-3">Name</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Phone</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($admins as $admin)
                    <tr class="border-b last:border-b-0">
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <span class="h-8 w-8 rounded-full bg-gradient-to-r from-amber-700 via-rose-600 to-emerald-700 text-white text-xs font-bold inline-flex items-center justify-center">{{ strtoupper(substr($admin->name, 0, 1)) }}</span>
                                <span>{{ $admin->name }}</span>
                            </div>
                        </td>
                        <td class="p-3">{{ $admin->email }}</td>
                        <td class="p-3">{{ $admin->phone ?: '-' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs {{ $admin->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ strtoupper($admin->status) }}</span>
                        </td>
                        <td class="p-3" x-data="{open:false}">
                            <button @click="open=!open" class="px-2 py-1 border border-amber-200 rounded text-sm text-amber-700">Actions</button>
                            <div x-show="open" @click.outside="open=false" class="absolute mt-1 bg-white border rounded shadow z-10" x-cloak>
                                <button wire:click="edit({{ $admin->id }})" class="block w-full text-left px-3 py-2 text-sm hover:bg-slate-50">Edit</button>
                                @if ($admin->status === 'suspended')
                                    <button wire:click="activate({{ $admin->id }})" class="block w-full text-left px-3 py-2 text-sm hover:bg-slate-50">Activate</button>
                                @else
                                    <button wire:click="suspend({{ $admin->id }})" class="block w-full text-left px-3 py-2 text-sm hover:bg-slate-50">Suspend</button>
                                @endif
                                <button wire:click="delete({{ $admin->id }})" wire:confirm="Delete this admin?" class="block w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-slate-500">No admins found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $admins->links() }}</div>
</div>
