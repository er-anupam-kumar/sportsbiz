<div class="space-y-4">
    <div class="flex items-center gap-3">
        <img src="{{ asset('images/sportsbiz-logo.svg') }}" alt="SportsBiz" class="h-9 w-9 object-contain rounded-lg border border-slate-200 p-1 bg-white" />
        <h1 class="text-2xl font-bold">Sports Manager</h1>
    </div>

    <div class="sb-card p-4 space-y-3">
        <h2 class="font-semibold">{{ $editingId ? 'Edit Sport' : 'Create Sport' }}</h2>
        <div class="grid md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
                <label class="text-sm">Sport Name</label>
                <input wire:model="name" class="sb-input" placeholder="Sport name">
                @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm">Status</label>
                <select wire:model="isActive" class="sb-input">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2">
            <button wire:click="save" wire:loading.attr="disabled" class="px-4 py-2 sb-btn-primary" wire:target="save">
                <span wire:loading wire:target="save" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                <span wire:loading.remove wire:target="save">Save</span>
            </button>
            @if($editingId)
                <button wire:click="resetForm" class="px-4 py-2 border border-slate-300 rounded-lg">Cancel</button>
            @endif
        </div>
    </div>

    <div class="sb-card p-4 max-w-xl">
        <label class="block text-sm font-medium mb-1">Search Sport</label>
        <input wire:model.live.debounce.400ms="search" class="sb-input" placeholder="Search sport">
    </div>

    <div class="sb-card overflow-x-auto">
        <table class="w-full">
            <thead class="text-left text-sm text-slate-700 border-b bg-gradient-to-r from-amber-50 via-rose-50 to-emerald-50">
                <tr>
                    <th class="p-3">Name</th>
                    <th class="p-3">Slug</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sports as $sport)
                    <tr class="border-b last:border-b-0">
                        <td class="p-3">{{ $sport->name }}</td>
                        <td class="p-3">{{ $sport->slug }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs inline-flex items-center gap-1 {{ $sport->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-700' }}"><span class="h-2 w-2 rounded-full {{ $sport->is_active ? 'bg-green-600' : 'bg-slate-500' }}"></span>{{ $sport->is_active ? 'ACTIVE' : 'INACTIVE' }}</span>
                        </td>
                        <td class="p-3" x-data="{open:false}">
                            <button @click="open=!open" class="px-2 py-1 border border-amber-200 rounded text-sm text-amber-700">Actions</button>
                            <div x-show="open" @click.outside="open=false" class="absolute mt-1 bg-white border rounded shadow z-10" x-cloak>
                                <button wire:click="edit({{ $sport->id }})" wire:loading.attr="disabled" wire:target="edit({{ $sport->id }})" class="block w-full text-left px-3 py-2 text-sm hover:bg-slate-50">
                                    <span wire:loading wire:target="edit({{ $sport->id }})" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                                    <span wire:loading.remove wire:target="edit({{ $sport->id }})">Edit</span>
                                </button>
                                <button wire:click="toggle({{ $sport->id }})" wire:loading.attr="disabled" wire:target="toggle({{ $sport->id }})" class="block w-full text-left px-3 py-2 text-sm hover:bg-slate-50">
                                    <span wire:loading wire:target="toggle({{ $sport->id }})" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                                    <span wire:loading.remove wire:target="toggle({{ $sport->id }})">Toggle Status</span>
                                </button>
                                <button wire:click="delete({{ $sport->id }})" wire:confirm="Delete this sport?" wire:loading.attr="disabled" wire:target="delete({{ $sport->id }})" class="block w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <span wire:loading wire:target="delete({{ $sport->id }})" class="inline-flex items-center"><svg class="animate-spin h-4 w-4 mr-1 text-red-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Loading...</span>
                                    <span wire:loading.remove wire:target="delete({{ $sport->id }})">Delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-4 text-center text-slate-500">No sports found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $sports->links() }}</div>
</div>
