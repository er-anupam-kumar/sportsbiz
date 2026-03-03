<?php

namespace App\Livewire\Admin\Categories;

use App\Models\PlayerCategory;
use App\Models\Tournament;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Manager extends Component
{
    use WithPagination;

    public int $tournamentId = 0;
    public ?int $editingId = null;
    public int $formTournamentId = 0;
    public string $name = '';
    public int $maxPerTeam = 5;

    public function updatedTournamentId(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $this->validate([
            'formTournamentId' => [
                'required',
                'integer',
                Rule::exists('tournaments', 'id')->where(fn ($query) => $query->where('admin_id', auth()->id())),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('player_categories', 'name')
                    ->where(fn ($query) => $query->where('tournament_id', $this->formTournamentId))
                    ->ignore($this->editingId),
            ],
            'maxPerTeam' => ['required', 'integer', 'min:1'],
        ]);

        $payload = [
            'tournament_id' => $this->formTournamentId,
            'name' => $this->name,
            'max_per_team' => $this->maxPerTeam,
        ];

        if ($this->editingId) {
            PlayerCategory::query()->whereKey($this->editingId)->update($payload);
            $message = 'Category updated.';
        } else {
            PlayerCategory::query()->create($payload);
            $message = 'Category created.';
        }

        $this->resetForm();
        $this->dispatch('toast', message: $message);
    }

    public function edit(int $categoryId): void
    {
        $category = PlayerCategory::query()
            ->whereHas('tournament', fn ($query) => $query->where('admin_id', auth()->id()))
            ->findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->formTournamentId = (int) $category->tournament_id;
        $this->name = $category->name;
        $this->maxPerTeam = (int) $category->max_per_team;
    }

    public function delete(int $categoryId): void
    {
        PlayerCategory::query()
            ->whereHas('tournament', fn ($query) => $query->where('admin_id', auth()->id()))
            ->whereKey($categoryId)
            ->delete();

        if ($this->editingId === $categoryId) {
            $this->resetForm();
        }

        $this->dispatch('toast', message: 'Category deleted.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'formTournamentId', 'name', 'maxPerTeam']);
        $this->maxPerTeam = 5;
    }

    public function mount(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        $tournaments = Tournament::query()
            ->where('admin_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.categories.manager', [
            'tournaments' => $tournaments,
            'categories' => PlayerCategory::query()
                ->whereHas('tournament', function ($query) {
                    $query->where('admin_id', auth()->id())
                        ->when($this->tournamentId > 0, fn ($inner) => $inner->where('id', $this->tournamentId));
                })
                ->with('tournament:id,name')
                ->latest()
                ->paginate(12),
        ]);
    }
}
