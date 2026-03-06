<?php

namespace App\Livewire\Admin\Players;

use App\Models\Player;
use App\Models\PlayerCategory;
use App\Models\Tournament;
use App\Support\AdminQuota;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Manager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public int $tournamentId = 0;
    public ?int $editingId = null;
    public int $formTournamentId = 0;
    public ?int $categoryId = null;
    public string $name = '';
    public float $basePrice = 0;
    public ?int $age = null;
    public string $country = '';
    public string $previousTeam = '';
    public string $status = 'available';
    public $image;
    public ?string $existingImagePath = null;

    public function updatedTournamentId(): void
    {
        $this->resetPage();
    }

    public function updatedFormTournamentId(): void
    {
        $this->categoryId = null;
    }

    public function isFormPage(): bool
    {
        return in_array((string) request()->route()?->getName(), ['admin.players.create', 'admin.players.edit'], true);
    }

    public function save(): void
    {
        $adminId = (int) auth()->id();

        $this->validate([
            'formTournamentId' => [
                'required',
                'integer',
                Rule::exists('tournaments', 'id')->where(fn ($query) => $query->where('admin_id', auth()->id())),
            ],
            'categoryId' => [
                'nullable',
                'integer',
                Rule::exists('player_categories', 'id')->where(fn ($query) => $query->where('tournament_id', $this->formTournamentId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'basePrice' => ['required', 'numeric', 'min:0'],
            'age' => ['nullable', 'integer', 'between:10,60'],
            'country' => ['nullable', 'string', 'max:100'],
            'previousTeam' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:available,sold,unsold,retained,withdrawn'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if (! $this->editingId) {
            $limitMessage = AdminQuota::playerLimitMessage($adminId);
            if ($limitMessage) {
                $this->addError('name', $limitMessage);
                $this->dispatch('toast', message: $limitMessage);

                return;
            }
        }

        $payload = [
            'admin_id' => $adminId,
            'tournament_id' => $this->formTournamentId,
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'base_price' => $this->basePrice,
            'age' => $this->age,
            'country' => $this->country !== '' ? $this->country : null,
            'previous_team' => $this->previousTeam !== '' ? $this->previousTeam : null,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            $player = Player::query()
                ->where('admin_id', auth()->id())
                ->findOrFail($this->editingId);

            if ($this->image) {
                if ($player->image_path) {
                    Storage::disk('public')->delete($player->image_path);
                }
                $payload['image_path'] = $this->image->store('players', 'public');
            }

            $player->update($payload);
            $message = 'Player updated.';
        } else {
            if ($this->image) {
                $payload['image_path'] = $this->image->store('players', 'public');
            }
            Player::query()->create($payload);
            $message = 'Player created.';
        }

        $this->resetForm();
        $this->dispatch('toast', message: $message);
    }

    public function edit(int $playerId): void
    {
        $player = Player::query()->where('admin_id', auth()->id())->findOrFail($playerId);
        $this->editingId = $player->id;
        $this->formTournamentId = (int) $player->tournament_id;
        $this->categoryId = $player->category_id ? (int) $player->category_id : null;
        $this->name = $player->name;
        $this->basePrice = (float) $player->base_price;
        $this->age = $player->age ? (int) $player->age : null;
        $this->country = (string) ($player->country ?? '');
        $this->previousTeam = (string) ($player->previous_team ?? '');
        $this->status = $player->status;
        $this->existingImagePath = $player->image_path;
        $this->image = null;
    }

    public function delete(int $playerId): void
    {
        $player = Player::query()->where('admin_id', auth()->id())->findOrFail($playerId);
        if ($player->image_path) {
            Storage::disk('public')->delete($player->image_path);
        }
        $player->delete();
        if ($this->editingId === $playerId) {
            $this->resetForm();
        }
        $this->dispatch('toast', message: 'Player deleted.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'formTournamentId',
            'categoryId',
            'name',
            'basePrice',
            'age',
            'country',
            'previousTeam',
            'status',
            'image',
            'existingImagePath',
        ]);
        $this->basePrice = 0;
        $this->status = 'available';
    }

    public function mount(?int $player = null): void
    {
        $this->resetForm();

        if ($player) {
            $this->edit($player);
        }
    }

    public function render()
    {
        $adminId = (int) auth()->id();
        $tournamentIds = Tournament::where('admin_id', $adminId)->pluck('id');
        $isFormPage = $this->isFormPage();

        return view($isFormPage ? 'livewire.admin.players.form' : 'livewire.admin.players.index', [
            'tournaments' => Tournament::where('admin_id', $adminId)->get(['id', 'name']),
            'quota' => AdminQuota::playerStats($adminId),
            'categories' => $this->formTournamentId > 0
                ? PlayerCategory::query()->where('tournament_id', $this->formTournamentId)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'players' => Player::query()
                ->when($this->tournamentId > 0, fn ($query) => $query->where('tournament_id', $this->tournamentId))
                ->where('admin_id', $adminId)
                ->whereIn('tournament_id', $tournamentIds)
                ->with(['tournament:id,name', 'category:id,name'])
                ->latest()
                ->paginate(20),
        ]);
    }
}
