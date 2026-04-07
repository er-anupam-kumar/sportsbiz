<?php

namespace App\Livewire\Team;

use App\Models\Player;
use App\Models\Team;
use App\Models\TeamJerseyRequest;
use App\Models\Tournament;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.team')]
class JerseyRequirements extends Component
{
    use WithPagination;

    public ?Team $team = null;
    public ?Tournament $tournament = null;

    public string $requestFor = 'player';
    public int $playerId = 0;
    public string $staffName = '';
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

    public function updatedRequestFor(string $value): void
    {
        if ($value === 'player') {
            $this->staffName = '';
            return;
        }

        $this->playerId = 0;
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
            'requestFor' => ['required', 'in:player,staff'],
            'playerId' => [
                Rule::excludeIf($this->requestFor === 'staff'),
                Rule::requiredIf($this->requestFor === 'player'),
                'nullable',
                'integer',
                Rule::exists('players', 'id'),
            ],
            'staffName' => [
                Rule::excludeIf($this->requestFor === 'player'),
                Rule::requiredIf($this->requestFor === 'staff'),
                'nullable',
                'string',
                'max:120',
            ],
            'size' => ['required', 'string', 'in:XS,S,M,L,XL,XXL,3XL'],
            'nickname' => ['nullable', 'string', 'max:60'],
            'jerseyNumber' => ['required', 'string', 'max:20'],
            'additionalJerseyRequired' => ['boolean'],
            'additionalJerseyQuantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validated['requestFor'] === 'player' && ! $validated['playerId']) {
            $this->addError('playerId', 'Please select a player.');
            return;
        }

        if ($validated['requestFor'] === 'staff' && trim((string) $validated['staffName']) === '') {
            $this->addError('staffName', 'Please enter staff name.');
            return;
        }

        if ((bool) $validated['additionalJerseyRequired'] && ! $validated['additionalJerseyQuantity']) {
            $this->addError('additionalJerseyQuantity', 'Please enter number of additional jerseys required.');
            return;
        }

        $player = null;

        if ($validated['requestFor'] === 'player') {
            $player = Player::query()
                ->whereKey($validated['playerId'])
                ->where('tournament_id', $this->team->tournament_id)
                ->first();

            if (! $player) {
                $this->addError('playerId', 'Selected player is not from your tournament.');
                return;
            }
        }

        TeamJerseyRequest::query()->create([
            'admin_id' => (int) $this->team->admin_id,
            'tournament_id' => (int) $this->team->tournament_id,
            'team_id' => (int) $this->team->id,
            'player_id' => $player?->id,
            'request_for' => $validated['requestFor'],
            'player_name' => $player?->name ?? trim((string) $validated['staffName']),
            'staff_name' => $validated['requestFor'] === 'staff' ? trim((string) $validated['staffName']) : null,
            'size' => $validated['size'],
            'nickname' => $validated['nickname'] ?? null,
            'jersey_number' => $validated['jerseyNumber'],
            'additional_jersey_required' => (bool) $validated['additionalJerseyRequired'],
            'additional_jersey_quantity' => (bool) $validated['additionalJerseyRequired']
                ? (int) $validated['additionalJerseyQuantity']
                : null,
        ]);

        $this->reset(['requestFor', 'playerId', 'staffName', 'size', 'nickname', 'jerseyNumber', 'additionalJerseyRequired', 'additionalJerseyQuantity']);
        $this->requestFor = 'player';
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
                    ->paginate(15, ['id', 'request_for', 'player_name', 'staff_name', 'size', 'nickname', 'jersey_number', 'additional_jersey_required', 'additional_jersey_quantity', 'created_at'])
                : collect(),
        ]);
    }
}
