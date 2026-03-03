<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Sport;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.super-admin')]
class SportsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $editingId = null;
    public string $name = '';
    public bool $isActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->save();
    }

    public function edit(int $sportId): void
    {
        $sport = Sport::query()->findOrFail($sportId);
        $this->editingId = $sport->id;
        $this->name = $sport->name;
        $this->isActive = (bool) $sport->is_active;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('sports', 'name')->ignore($this->editingId)],
            'isActive' => ['boolean'],
        ]);

        $slug = Str::slug($this->name);

        if ($this->editingId) {
            $sport = Sport::query()->findOrFail($this->editingId);
            $sport->update([
                'name' => $this->name,
                'slug' => $slug === $sport->slug ? $sport->slug : $slug.'-'.Str::lower(Str::random(4)),
                'is_active' => $this->isActive,
            ]);
            $message = 'Sport updated.';
        } else {
            Sport::query()->create([
                'name' => $this->name,
                'slug' => $slug.'-'.Str::lower(Str::random(4)),
                'is_active' => $this->isActive,
                'created_by' => auth()->id(),
            ]);
            $message = 'Sport created.';
        }

        $this->resetForm();
        $this->dispatch('toast', message: $message);
    }

    public function toggle(int $sportId): void
    {
        $sport = Sport::query()->findOrFail($sportId);
        $sport->update(['is_active' => ! $sport->is_active]);
        $this->dispatch('toast', message: 'Sport status updated.');
    }

    public function delete(int $sportId): void
    {
        Sport::query()->whereKey($sportId)->delete();
        $this->dispatch('toast', message: 'Sport deleted.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'isActive']);
        $this->isActive = true;
    }

    public function render()
    {
        return view('livewire.super-admin.sports-manager', [
            'sports' => Sport::query()
                ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(10),
        ]);
    }
}
