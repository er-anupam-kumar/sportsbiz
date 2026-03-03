<?php

namespace App\Livewire\Admin\Tournament;

use App\Models\Sport;
use App\Models\Tournament;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Create extends Component
{
    public int $sportId = 0;
    public string $name = '';
    public float $purseAmount = 0;
    public int $maxPlayersPerTeam = 15;
    public float $baseIncrement = 1000;
    public int $auctionTimerSeconds = 30;
    public bool $antiSniping = true;
    public string $auctionType = 'live';

    public function save(): void
    {
        $this->validate([
            'sportId' => ['required', 'integer', 'exists:sports,id'],
            'name' => ['required', 'string', 'max:255'],
            'purseAmount' => ['required', 'numeric', 'min:0'],
            'maxPlayersPerTeam' => ['required', 'integer', 'min:1'],
            'baseIncrement' => ['required', 'numeric', 'min:1'],
            'auctionTimerSeconds' => ['required', 'integer', 'min:5'],
            'auctionType' => ['required', 'in:live,silent'],
        ]);

        Tournament::query()->create([
            'admin_id' => auth()->id(),
            'sport_id' => $this->sportId,
            'name' => $this->name,
            'purse_amount' => $this->purseAmount,
            'max_players_per_team' => $this->maxPlayersPerTeam,
            'base_increment' => $this->baseIncrement,
            'auction_timer_seconds' => $this->auctionTimerSeconds,
            'anti_sniping' => $this->antiSniping,
            'auction_type' => $this->auctionType,
            'status' => 'draft',
        ]);

        $this->resetForm();
        $this->dispatch('toast', message: 'Tournament created.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'sportId',
            'name',
            'purseAmount',
            'maxPlayersPerTeam',
            'baseIncrement',
            'auctionTimerSeconds',
            'antiSniping',
            'auctionType',
        ]);

        $this->maxPlayersPerTeam = 15;
        $this->baseIncrement = 1000;
        $this->auctionTimerSeconds = 30;
        $this->antiSniping = true;
        $this->auctionType = 'live';
    }

    public function mount(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.admin.tournament.create', [
            'sports' => Sport::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
