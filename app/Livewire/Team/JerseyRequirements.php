<?php

namespace App\Livewire\Team;

use App\Models\Player;
use App\Models\Team;
use App\Models\TeamJerseyRequest;
use App\Models\Tournament;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.team')]
class JerseyRequirements extends Component
{
    use WithPagination;

    public ?Team $team = null;
    public ?Tournament $tournament = null;

    public int $playerId = 0;
    public string $size = '';
    public string $nickname = '';
    public string $jerseyNumber = '';
    public bool $additionalJerseyRequired = false;
    public ?int $additionalJerseyQuantity = null;

    public function updatedAdditionalJerseyRequired(bool $value): void
    {
        if (! $value) {
            $this->additionalJerseyQuantity = null;
        }
    }

    public function mount(): void
    {
        $this->team = Team::query()
            ->where('user_id', auth()->id())
            ->first();

        if (! $this->team) {
            return;
        }

        $this->tournament = Tournament::query()->find($this->team->tournament_id);
    }

    public function save(): void
    {
        if (! $this->team || ! $this->tournament) {
            $this->addError('playerId', 'Team profile is missing.');
            return;
        }

        if (! $this->tournament->jersey_module_enabled) {
            $this->addError('playerId', 'Jersey module is currently disabled by admin.');
            return;
        }

        $validated = $this->validate([
            'playerId' => ['required', 'integer', 'exists:players,id'],
            'size' => ['required', 'string', 'in:XS,S,M,L,XL,XXL,3XL'],
            'nickname' => ['nullable', 'string', 'max:60'],
            'jerseyNumber' => ['required', 'string', 'max:20'],
            'additionalJerseyRequired' => ['boolean'],
            'additionalJerseyQuantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ((bool) $validated['additionalJerseyRequired'] && ! $validated['additionalJerseyQuantity']) {
            $this->addError('additionalJerseyQuantity', 'Please enter number of additional jerseys required.');
            return;
        }

        $player = Player::query()
            ->whereKey($validated['playerId'])
            ->where('tournament_id', $this->team->tournament_id)
            ->first();

        if (! $player) {
            $this->addError('playerId', 'Selected player is not from your tournament.');
            return;
        }

        TeamJerseyRequest::query()->create([
            'admin_id' => (int) $this->team->admin_id,
            'tournament_id' => (int) $this->team->tournament_id,
            'team_id' => (int) $this->team->id,
            'player_id' => (int) $player->id,
            'player_name' => (string) $player->name,
            'size' => $validated['size'],
            'nickname' => $validated['nickname'] ?? null,
            'jersey_number' => $validated['jerseyNumber'],
            'additional_jersey_required' => (bool) $validated['additionalJerseyRequired'],
            'additional_jersey_quantity' => (bool) $validated['additionalJerseyRequired']
                ? (int) $validated['additionalJerseyQuantity']
                : null,
        ]);

        $this->reset(['playerId', 'size', 'nickname', 'jerseyNumber', 'additionalJerseyRequired', 'additionalJerseyQuantity']);
        $this->resetPage();
        $this->dispatch('toast', message: 'Jersey entry added.');
    }

    public function render()
    {
        $team = $this->team;
        $tournament = $this->tournament;

        return view('livewire.team.jersey-requirements', [
            'team' => $team,
            'tournament' => $tournament,
            'players' => $team
                ? Player::query()
                    ->where('tournament_id', $team->tournament_id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'serial_no'])
                : collect(),
            'entries' => $team
                ? TeamJerseyRequest::query()
                    ->where('team_id', $team->id)
                    ->latest()
                    ->paginate(15, ['id', 'player_name', 'size', 'nickname', 'jersey_number', 'additional_jersey_required', 'additional_jersey_quantity', 'created_at'])
                : collect(),
        ]);
    }
}
