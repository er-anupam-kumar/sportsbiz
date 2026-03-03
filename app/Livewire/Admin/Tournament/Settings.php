<?php

namespace App\Livewire\Admin\Tournament;

use App\Models\Sport;
use App\Models\Tournament;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Settings extends Component
{
    use AuthorizesRequests;

    public Tournament $tournament;
    public int $sportId = 0;
    public string $name = '';
    public float $purseAmount = 0;
    public int $maxPlayersPerTeam = 15;
    public float $baseIncrement = 1000;
    public int $auctionTimerSeconds = 30;
    public bool $antiSniping = true;
    public string $auctionType = 'live';
    public string $status = 'draft';

    public function mount(Tournament $tournament): void
    {
        $this->authorize('update', $tournament);
        $this->tournament = $tournament;
        $this->sportId = (int) $tournament->sport_id;
        $this->name = $tournament->name;
        $this->purseAmount = (float) $tournament->purse_amount;
        $this->maxPlayersPerTeam = (int) $tournament->max_players_per_team;
        $this->baseIncrement = (float) $tournament->base_increment;
        $this->auctionTimerSeconds = (int) $tournament->auction_timer_seconds;
        $this->antiSniping = (bool) $tournament->anti_sniping;
        $this->auctionType = $tournament->auction_type;
        $this->status = $tournament->status;
    }

    public function save(): void
    {
        $this->authorize('update', $this->tournament);
        $this->validate([
            'sportId' => ['required', 'integer', 'exists:sports,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tournaments', 'name')
                    ->where(fn ($query) => $query->where('admin_id', auth()->id()))
                    ->ignore($this->tournament->id),
            ],
            'purseAmount' => ['required', 'numeric', 'min:0'],
            'maxPlayersPerTeam' => ['required', 'integer', 'min:1'],
            'baseIncrement' => ['required', 'numeric', 'min:1'],
            'auctionTimerSeconds' => ['required', 'integer', 'min:5'],
            'antiSniping' => ['boolean'],
            'auctionType' => ['required', 'in:live,silent'],
            'status' => ['required', 'in:draft,active,paused,completed'],
        ]);

        $this->tournament->update([
            'sport_id' => $this->sportId,
            'name' => $this->name,
            'purse_amount' => $this->purseAmount,
            'max_players_per_team' => $this->maxPlayersPerTeam,
            'base_increment' => $this->baseIncrement,
            'auction_timer_seconds' => $this->auctionTimerSeconds,
            'anti_sniping' => $this->antiSniping,
            'auction_type' => $this->auctionType,
            'status' => $this->status,
        ]);

        $this->dispatch('toast', message: 'Tournament updated.');
    }

    public function render()
    {
        return view('livewire.admin.tournament.settings', [
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
