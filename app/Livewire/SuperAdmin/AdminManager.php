<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.super-admin')]
class AdminManager extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public bool $formOpen = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->formOpen = true;
    }

    public function edit(int $userId): void
    {
        $admin = User::query()->role('Admin')->findOrFail($userId);

        $this->editingId = $admin->id;
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->phone = (string) ($admin->phone ?? '');
        $this->password = '';
        $this->formOpen = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'phone' => ['nullable', 'string', 'max:30'],
        ];

        if (! $this->editingId) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } elseif ($this->password !== '') {
            $rules['password'] = ['string', 'min:8'];
        }

        $validated = $this->validate($rules);

        if ($this->editingId) {
            $admin = User::query()->role('Admin')->findOrFail($this->editingId);
            $payload = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?: null,
            ];
            if (! empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
            }
            $admin->update($payload);
            $message = 'Admin updated.';
        } else {
            $admin = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?: null,
                'password' => Hash::make($validated['password']),
                'status' => 'active',
            ]);
            $admin->assignRole('Admin');
            $message = 'Admin created.';
        }

        $this->resetForm();
        $this->dispatch('toast', message: $message);
    }

    public function suspend(int $userId): void
    {
        User::query()->role('Admin')->whereKey($userId)->update(['status' => 'suspended']);
        $this->dispatch('toast', message: 'Admin suspended.');
    }

    public function activate(int $userId): void
    {
        User::query()->role('Admin')->whereKey($userId)->update(['status' => 'active']);
        $this->dispatch('toast', message: 'Admin activated.');
    }

    public function delete(int $userId): void
    {
        $admin = User::query()->role('Admin')->findOrFail($userId);
        $admin->delete();
        $this->dispatch('toast', message: 'Admin deleted.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'password', 'formOpen']);
    }

    public function render()
    {
        return view('livewire.super-admin.admin-manager', [
            'admins' => User::query()
                ->role('Admin')
                ->when($this->search !== '', function ($query) {
                    $query->where(function ($inner) {
                        $inner->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                })
                ->latest()
                ->paginate(10),
        ]);
    }
}
