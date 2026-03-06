<?php

namespace App\Livewire\Public;

use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AuctionViewer extends Component
{
    public int $tournamentId;
    public bool $projectorMode = false;
    public bool $darkMode = false;
    public bool $compactMode = false;
    public string $snapshotKey = '';
    public ?int $selectedTeamId = null;
    public string $realtimeMode = 'polling';

    public function mount(int $tournamentId): void
    {
        $this->tournamentId = $tournamentId;
        $this->projectorMode = request()->boolean('projector');
        $this->darkMode = request()->boolean('dark');
        $this->compactMode = request()->boolean('compact');
        $this->realtimeMode = (string) Cache::get('platform_realtime_mode', 'polling');
        $this->snapshotKey = $this->buildSnapshotKey();
    }

    public function getListeners(): array
    {
        if ($this->realtimeMode === 'polling') {
            return [];
        }

        return [
            "echo:tournament.public.{$this->tournamentId},BidPlaced" => 'handleAuctionActivity',
            "echo:tournament.public.{$this->tournamentId},TimerExtended" => 'handleAuctionActivity',
            "echo:tournament.public.{$this->tournamentId},PlayerSold" => 'handleAuctionActivity',
            "echo:tournament.public.{$this->tournamentId},AuctionStarted" => 'handleAuctionActivity',
            "echo:tournament.public.{$this->tournamentId},AuctionPaused" => 'handleAuctionActivity',
            "echo:tournament.public.{$this->tournamentId},PlayerShuffled" => 'handlePlayerShuffled',
        ];
    }

    public function handleAuctionActivity(): void
    {
        $this->dispatch('auction-activity');
    }

    public function handlePlayerShuffled(): void
    {
        $this->dispatch('auction-activity');
        $this->dispatch('auction-player-shuffled');
    }

    public function refreshAuctionState(): void
    {
        $currentSnapshot = $this->buildSnapshotKey();

        if ($this->snapshotKey !== '' && $this->snapshotKey !== $currentSnapshot) {
            $this->dispatch('auction-activity');
        }

        $this->snapshotKey = $currentSnapshot;
    }

    private function buildSnapshotKey(): string
    {
        $auction = Auction::query()
            ->where('tournament_id', $this->tournamentId)
            ->first(['id', 'current_player_id', 'current_highest_team_id', 'current_bid', 'is_paused', 'ends_at']);

        if (! $auction) {
            return 'no-auction';
        }

        return implode('|', [
            $auction->id,
            $auction->current_player_id,
            $auction->current_highest_team_id,
            $auction->current_bid,
            (int) $auction->is_paused,
            optional($auction->ends_at)?->toIso8601String() ?? 'no-end',
        ]);
    }

    public function render()
    {
        $auction = Auction::with('currentPlayer', 'currentHighestTeam')
            ->where('tournament_id', $this->tournamentId)
            ->first();

        $soldPlayers = Player::query()
            ->where('tournament_id', $this->tournamentId)
            ->where('status', 'sold')
            ->with('category:id,name', 'soldTeam:id,name,logo_path,primary_color,secondary_color')
            ->orderByDesc('final_price')
            ->orderByDesc('updated_at')
            ->get([
                'id',
                'name',
                'image_path',
                'category_id',
                'sold_team_id',
                'base_price',
                'final_price',
                'age',
                'country',
                'previous_team',
            ]);

        $teamwiseCategoryPlayers = $soldPlayers
            ->groupBy(fn (Player $player) => (string) ($player->sold_team_id ?? 0))
            ->map(function ($teamPlayers) {
                $team = $teamPlayers->first()?->soldTeam;

                return [
                    'team' => $team,
                    'count' => $teamPlayers->count(),
                    'categories' => $teamPlayers->groupBy(fn (Player $player) => $player->category?->name ?: 'Uncategorized'),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $remainingSeconds = 0;
        if ($auction?->ends_at) {
            if ($auction->is_paused && $auction->updated_at) {
                $remainingSeconds = max((int) $auction->updated_at->diffInSeconds($auction->ends_at, false), 0);
            } else {
                $remainingSeconds = max((int) now()->diffInSeconds($auction->ends_at, false), 0);
            }
        }

        $timerTotal = 30;
        if ($auction?->ends_at && $auction?->started_at) {
            $timerTotal = max((int) $auction->started_at->diffInSeconds($auction->ends_at), 1);
        }

        $timerPct = max(0, min(100, (int) round(($remainingSeconds / max($timerTotal, 1)) * 100)));

        return view('livewire.public.auction-viewer', [
            'auction' => $auction,
            'soldPlayers' => $soldPlayers,
            'teamwiseCategoryPlayers' => $teamwiseCategoryPlayers,
            'leaderboard' => Team::where('tournament_id', $this->tournamentId)->orderByDesc('squad_count')->limit(5)->get(),
            'remainingSeconds' => $remainingSeconds,
            'timerPct' => $timerPct,
            'projectorMode' => $this->projectorMode,
            'darkMode' => $this->darkMode,
            'compactMode' => $this->compactMode,
        ]);
    }
}
