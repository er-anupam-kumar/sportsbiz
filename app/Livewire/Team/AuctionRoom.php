<?php

namespace App\Livewire\Team;

use App\Exceptions\AuctionException;
use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Services\AuctionEngine;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.team')]
class AuctionRoom extends Component
{
    public int $tournamentId;
    public ?int $teamId = null;
    public ?string $error = null;
    public string $snapshotKey = '';
    public string $soundTriggerMode = 'polling';
    public string $lastRoundCompleteToken = '';

    public function mount(int $tournamentId): void
    {
        $this->tournamentId = $tournamentId;
        $this->soundTriggerMode = (string) Cache::get('platform_sound_trigger_mode', 'polling');
        $this->teamId = Team::query()
            ->where('user_id', auth()->id())
            ->where('tournament_id', $this->tournamentId)
            ->value('id');
        $this->snapshotKey = $this->buildSnapshotKey();
        $roundPayload = Cache::get($this->roundCompleteCacheKey(), []);
        $this->lastRoundCompleteToken = (string) ($roundPayload['token'] ?? '');
    }

    public function refreshAuctionState(): void
    {
        $currentSnapshot = $this->buildSnapshotKey();

        if ($this->soundTriggerMode === 'polling' && $this->snapshotKey !== '' && $this->snapshotKey !== $currentSnapshot) {
            $action = $this->detectActionFromSnapshots($this->snapshotKey, $currentSnapshot);
            $this->dispatch('auction-activity', action: $action);

            if ($action === 'player_sold') {
                $this->dispatch('auction-player-locked', player: $this->resolveLockedPlayerCard());
            }
        }

        $this->snapshotKey = $currentSnapshot;
        $this->dispatchRoundCompleteIfNeeded();
    }

    private function dispatchRoundCompleteIfNeeded(): void
    {
        $payload = Cache::get($this->roundCompleteCacheKey(), []);
        $token = (string) ($payload['token'] ?? '');

        if ($token === '' || $token === $this->lastRoundCompleteToken) {
            return;
        }

        $this->lastRoundCompleteToken = $token;

        $this->dispatch(
            'auction-round-complete',
            soldCount: (int) ($payload['soldCount'] ?? 0),
            unsoldCount: (int) ($payload['unsoldCount'] ?? 0)
        );
    }

    private function roundCompleteCacheKey(): string
    {
        return "auction_round_complete:tournament:{$this->tournamentId}";
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

    public function placeBid(AuctionEngine $auctionEngine): void
    {
        $team = Team::query()
            ->where('user_id', auth()->id())
            ->where('tournament_id', $this->tournamentId)
            ->first();

        if (! $team) {
            $this->error = 'Team profile not found for this tournament.';
            return;
        }

        $auction = Auction::query()
            ->where('tournament_id', $this->tournamentId)
            ->first();

        if (! $auction || ! $auction->current_player_id) {
            $this->error = 'No player is currently live for bidding.';
            return;
        }

        $playerExists = Player::query()
            ->whereKey($auction->current_player_id)
            ->where('tournament_id', $this->tournamentId)
            ->where('status', 'available')
            ->exists();

        if (! $playerExists) {
            $this->error = 'Selected player is not available for bidding right now.';
            return;
        }

        try {
            $auctionEngine->placeBid($team->id, (int) $auction->current_player_id);
            $this->error = null;
        } catch (AuctionException $exception) {
            $this->error = $exception->getMessage();
        }
    }

    public function getListeners(): array
    {
        return [
            "echo-presence:tournament.{$this->tournamentId},BidPlaced" => 'handleBidPlaced',
            "echo-presence:tournament.{$this->tournamentId},TimerExtended" => 'handleTimerExtended',
            "echo-presence:tournament.{$this->tournamentId},PlayerSold" => 'handlePlayerSold',
            "echo-presence:tournament.{$this->tournamentId},AuctionStarted" => 'handleAuctionStarted',
            "echo-presence:tournament.{$this->tournamentId},AuctionPaused" => 'handleAuctionPaused',
            "echo-presence:tournament.{$this->tournamentId},PlayerShuffled" => 'handlePlayerShuffled',
        ];
    }

    public function handleBidPlaced(array $payload = []): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        if ($this->teamId && isset($payload['team_id']) && (int) $payload['team_id'] === (int) $this->teamId) {
            return;
        }

        $this->dispatch('auction-activity', action: 'bid');
    }

    public function handleTimerExtended(): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        $this->dispatch('auction-activity', action: 'timer_extended');
    }

    public function handlePlayerSold(array $payload = []): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        $this->dispatch('auction-activity', action: 'player_sold');
        $this->dispatch('auction-player-locked', player: $this->resolveLockedPlayerCard($payload));
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

    public function handleAuctionStarted(): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        $this->dispatch('auction-activity', action: 'auction_started');
    }

    public function handleAuctionPaused(): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        $this->dispatch('auction-activity', action: 'auction_paused');
    }

    public function handlePlayerShuffled(): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        $this->dispatch('auction-activity', action: 'player_shuffled');
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

    public function render()
    {
        // Resync squad_count for all teams in this tournament
        $teams = Team::where('tournament_id', $this->tournamentId)->get();
        foreach ($teams as $team) {
            $actualCount = Player::where('sold_team_id', $team->id)->where('status', 'sold')->count();
            if ($team->squad_count !== $actualCount) {
                $team->squad_count = $actualCount;
                $team->save();
            }
        }

        $auction = Auction::with('currentPlayer:id,tournament_id,category_id,name,serial_no,image_path,base_price,age,country,previous_team,status', 'currentPlayer.category', 'currentHighestTeam')
            ->where('tournament_id', $this->tournamentId)
            ->first();

        $tournament = Tournament::query()
            ->whereKey($this->tournamentId)
            ->first(['id', 'name', 'banner_path', 'purse_amount', 'base_increment', 'auction_timer_seconds', 'bidding_type']);

        $team = $this->teamId
            ? Team::query()->whereKey($this->teamId)->first()
            : Team::query()->where('user_id', auth()->id())->where('tournament_id', $this->tournamentId)->first();

        $soldPlayers = Player::query()
            ->where('tournament_id', $this->tournamentId)
            ->where('status', 'sold')
            ->with('category:id,name', 'soldTeam:id,name,logo_path,primary_color,secondary_color')
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

        $adminTotalPurse = (float) ($tournament?->purse_amount ?? 0);
        $adminUtilizedPurse = (float) $soldPlayers->sum(fn (Player $player) => (float) ($player->final_price ?? 0));
        $adminRemainingPurse = max($adminTotalPurse - $adminUtilizedPurse, 0);

        $remainingSeconds = 0;
        if ($auction?->ends_at) {
            if ($auction->is_paused && $auction->updated_at) {
                $remainingSeconds = max((int) $auction->updated_at->diffInSeconds($auction->ends_at, false), 0);
            } else {
                $remainingSeconds = max((int) now()->diffInSeconds($auction->ends_at, false), 0);
            }
        }

        return view('livewire.team.auction-room', [
            'auction' => $auction,
            'tournament' => $tournament,
            'remainingSeconds' => $remainingSeconds,
            'team' => $team,
            'soldPlayers' => $soldPlayers,
            'adminTotalPurse' => $adminTotalPurse,
            'adminUtilizedPurse' => $adminUtilizedPurse,
            'adminRemainingPurse' => $adminRemainingPurse,
            'leaderboard' => Team::query()
                ->where('tournament_id', $this->tournamentId)
                ->orderByDesc('squad_count')
                ->get(['id', 'name', 'logo_path', 'primary_color', 'secondary_color', 'wallet_balance', 'squad_count']),
        ]);
    }
}
