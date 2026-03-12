<?php

namespace App\Livewire\Public;

use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AuctionViewer extends Component
{
    public int $tournamentId;
    public bool $projectorMode = false;
    public bool $darkMode = false;
    public bool $compactMode = false;
    public string $snapshotKey = '';
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
            "echo:tournament.public.{$this->tournamentId},BidPlaced" => 'handleBidPlaced',
            "echo:tournament.public.{$this->tournamentId},TimerExtended" => 'handleTimerExtended',
            "echo:tournament.public.{$this->tournamentId},PlayerSold" => 'handlePlayerSold',
            "echo:tournament.public.{$this->tournamentId},AuctionStarted" => 'handleAuctionStarted',
            "echo:tournament.public.{$this->tournamentId},AuctionPaused" => 'handleAuctionPaused',
            "echo:tournament.public.{$this->tournamentId},PlayerShuffled" => 'handlePlayerShuffled',
        ];
    }

    public function handleBidPlaced(): void
    {
        $this->dispatch('auction-activity', action: 'bid');
    }

    public function handleTimerExtended(): void
    {
        $this->dispatch('auction-activity', action: 'timer_extended');
    }

    public function handlePlayerSold(array $payload = []): void
    {
        $this->dispatch('auction-activity', action: 'player_sold');
        $this->dispatch('auction-player-locked', player: $this->resolveLockedPlayerCard($payload));
    }

    public function handleAuctionStarted(): void
    {
        $this->dispatch('auction-activity', action: 'auction_started');
    }

    public function handleAuctionPaused(): void
    {
        $this->dispatch('auction-activity', action: 'auction_paused');
    }

    public function handlePlayerShuffled(): void
    {
        $this->dispatch('auction-activity', action: 'player_shuffled');
    }

    public function refreshAuctionState(): void
    {
        $currentSnapshot = $this->buildSnapshotKey();

        if ($this->snapshotKey !== '' && $this->snapshotKey !== $currentSnapshot) {
            $action = $this->detectActionFromSnapshots($this->snapshotKey, $currentSnapshot);
            $this->dispatch('auction-activity', action: $action);

            if ($action === 'player_sold') {
                $this->dispatch('auction-player-locked', player: $this->resolveLockedPlayerCard());
            }
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

    private function detectActionFromSnapshots(string $previous, string $current): string
    {
        $old = explode('|', $previous);
        $new = explode('|', $current);

        if (count($old) < 6 || count($new) < 6) {
            return 'state_changed';
        }

        if (($old[1] ?? null) !== ($new[1] ?? null)) {
            if (($new[1] ?? null) === '' || ($new[1] ?? null) === null) {
                return 'player_sold';
            }

            return 'player_shuffled';
        }

        if (($old[4] ?? null) !== ($new[4] ?? null)) {
            return ((int) ($new[4] ?? 0)) === 1 ? 'auction_paused' : 'auction_started';
        }

        if (((float) ($new[3] ?? 0)) > ((float) ($old[3] ?? 0))) {
            return 'bid';
        }

        if (($old[5] ?? null) !== ($new[5] ?? null)) {
            return 'timer_extended';
        }

        return 'state_changed';
    }

    private function resolveLockedPlayerCard(array $payload = []): ?array
    {
        $playerId = (int) ($payload['player_id'] ?? 0);
        $teamId = (int) ($payload['team_id'] ?? 0);
        $amount = array_key_exists('amount', $payload) ? (float) $payload['amount'] : null;

        $playerQuery = Player::query()
            ->where('tournament_id', $this->tournamentId)
            ->with('category:id,name');

        $player = $playerId > 0
            ? $playerQuery->whereKey($playerId)->first(['id', 'name', 'serial_no', 'image_path', 'category_id', 'final_price', 'sold_team_id'])
            : $playerQuery->where('status', 'sold')->orderByDesc('updated_at')->first(['id', 'name', 'serial_no', 'image_path', 'category_id', 'final_price', 'sold_team_id']);

        if (! $player) {
            return null;
        }

        if ($teamId <= 0) {
            $teamId = (int) ($player->sold_team_id ?? 0);
        }

        $team = $teamId > 0
            ? Team::query()->whereKey($teamId)->first(['id', 'name', 'logo_path'])
            : null;

        return [
            'id' => (int) $player->id,
            'name' => $player->name,
            'serial_no' => $player->serial_no,
            'image_url' => $player->image_url,
            'category' => $player->category?->name,
            'team' => $team?->name,
            'team_logo_url' => $team?->logo_url,
            'amount' => $amount ?? (float) ($player->final_price ?? 0),
        ];
    }

    public function render()
    {
        $auction = Auction::with('currentPlayer:id,tournament_id,name,serial_no,image_path,status', 'currentHighestTeam:id,name,logo_path,primary_color,secondary_color')
            ->where('tournament_id', $this->tournamentId)
            ->first();

        $tournament = Tournament::query()
            ->whereKey($this->tournamentId)
            ->first(['id', 'name', 'banner_path', 'purse_amount']);

        $soldPlayers = Player::query()
            ->where('tournament_id', $this->tournamentId)
            ->where('status', 'sold')
            ->with('category:id,name', 'soldTeam:id,name,logo_path,primary_color,secondary_color')
            ->orderByDesc('final_price')
            ->orderByDesc('updated_at')
            ->get([
                'id',
                'name',
                'serial_no',
                'image_path',
                'category_id',
                'sold_team_id',
                'base_price',
                'final_price',
                'age',
                'country',
                'previous_team',
            ]);

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
            'tournament' => $tournament,
            'soldPlayers' => $soldPlayers,
            'leaderboard' => Team::query()
                ->where('tournament_id', $this->tournamentId)
                ->orderByDesc('squad_count')
                ->limit(6)
                ->get(['id', 'name', 'logo_path', 'primary_color', 'secondary_color', 'wallet_balance', 'squad_count']),
            'remainingSeconds' => $remainingSeconds,
            'timerPct' => $timerPct,
            'projectorMode' => $this->projectorMode,
            'darkMode' => $this->darkMode,
            'compactMode' => $this->compactMode,
        ]);
    }
}
