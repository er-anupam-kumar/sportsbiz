<?php

namespace App\Livewire\Admin\Tournament;

use App\Models\Auction;
use App\Models\Sport;
use App\Models\Tournament;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Settings extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Tournament $tournament;
    public int $sportId = 0;
    public string $name = '';
    public float $purseAmount = 0;
    public int $maxPlayersPerTeam = 15;
    public float $baseIncrement = 1000;
    public int $auctionTimerSeconds = 30;
    public bool $antiSniping = true;
    public string $auctionType = 'live';
    public string $biddingType = 'admin_only';
    public bool $jerseyModuleEnabled = false;
    public string $status = 'draft';
    public $banner;
    public ?string $existingBannerPath = null;

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
        $this->biddingType = $tournament->bidding_type ?: 'admin_only';
        $this->jerseyModuleEnabled = (bool) $tournament->jersey_module_enabled;
        $this->status = $tournament->status;
        $this->existingBannerPath = $tournament->banner_path;
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
            'biddingType' => ['required', 'in:admin_only,team_open'],
            'jerseyModuleEnabled' => ['boolean'],
            'status' => ['required', 'in:draft,active,paused,completed'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ]);

        $payload = [
            'sport_id' => $this->sportId,
            'name' => $this->name,
            'purse_amount' => $this->purseAmount,
            'max_players_per_team' => $this->maxPlayersPerTeam,
            'base_increment' => $this->baseIncrement,
            'auction_timer_seconds' => $this->auctionTimerSeconds,
            'anti_sniping' => $this->antiSniping,
            'auction_type' => $this->auctionType,
            'bidding_type' => $this->biddingType,
            'jersey_module_enabled' => $this->jerseyModuleEnabled,
            'status' => $this->status,
        ];

        if ($this->banner) {
            if ($this->tournament->banner_path) {
                Storage::disk('public')->delete($this->tournament->banner_path);
            }
            $payload['banner_path'] = $this->banner->store('tournament-banners', 'public');
        }

        $this->tournament->update($payload);
        $this->tournament->refresh();
        $this->existingBannerPath = $this->tournament->banner_path;
        $this->banner = null;

        $this->dispatch('toast', message: 'Tournament updated.');
    }

    public function render()
    {
        $auction = Auction::query()
            ->where('tournament_id', (int) $this->tournament->id)
            ->first(['id', 'current_player_id', 'is_paused', 'is_completed', 'completed_at']);

        $auctionStatus = [
            'label' => 'Not Started',
            'tone' => 'bg-slate-100 text-slate-700 border-slate-200',
            'completed_at' => null,
        ];

        if ($auction) {
            if ((bool) $auction->is_completed) {
                $auctionStatus = [
                    'label' => 'Completed',
                    'tone' => 'bg-rose-100 text-rose-700 border-rose-200',
                    'completed_at' => $auction->completed_at,
                ];
            } elseif ($auction->current_player_id && ! $auction->is_paused) {
                $auctionStatus = [
                    'label' => 'Live',
                    'tone' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'completed_at' => null,
                ];
            } elseif ($auction->current_player_id && $auction->is_paused) {
                $auctionStatus = [
                    'label' => 'Paused',
                    'tone' => 'bg-amber-100 text-amber-700 border-amber-200',
                    'completed_at' => null,
                ];
            }
        }

        return view('livewire.admin.tournament.settings', [
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'auctionStatus' => $auctionStatus,
        ]);
    }
}
