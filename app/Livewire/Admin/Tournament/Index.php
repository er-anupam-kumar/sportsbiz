<?php

namespace App\Livewire\Admin\Tournament;

use App\Models\Tournament;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $tournamentId): void
    {
        Tournament::query()
            ->where('admin_id', auth()->id())
            ->whereKey($tournamentId)
            ->delete();

        $this->dispatch('toast', message: 'Tournament deleted.');
    }

    public function render()
    {
        return view('livewire.admin.tournament.index', [
            'tournaments' => Tournament::query()
                ->where('admin_id', auth()->id())
                ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
                ->with('sport:id,name')
                ->latest()
                ->paginate(12),
        ]);
    }
}
