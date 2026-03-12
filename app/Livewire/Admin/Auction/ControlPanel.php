<?php

namespace App\Livewire\Admin\Auction;

use App\Events\AuctionPaused;
use App\Events\PlayerShuffled;
use App\Events\AuctionStarted;
use App\Events\TimerExtended;
use App\Exceptions\AuctionException;
use App\Models\Auction;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Services\AuctionEngine;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ControlPanel extends Component
{
    use AuthorizesRequests;

    public Tournament $tournament;
    public int $selectedTournamentId = 0;
    public ?int $selectedPlayerId = null;
    public ?int $selectedBidTeamId = null;
    public string $startMode = 'manual';
    public bool $randomPickEnabled = true;
    public string $snapshotKey = '';
    public string $soundTriggerMode = 'polling';
    public bool $showSquadModal = false;
    public string $squadTeamName = '';
    public array $squadPlayers = [];
    public ?int $lockedAdvanceAt = null;

    public function isAdminOnlyBidding(): bool
    {
        return ($this->tournament->bidding_type ?? 'admin_only') === 'admin_only';
    }

    private function safeBroadcast(object $event): void
    {
        try {
            event($event);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function getListeners(): array
    {
        return [
            "echo-presence:tournament.{$this->tournament->id},BidPlaced" => 'handleBidPlaced',
            "echo-presence:tournament.{$this->tournament->id},TimerExtended" => 'handleTimerExtended',
            "echo-presence:tournament.{$this->tournament->id},PlayerSold" => 'handlePlayerSold',
            "echo-presence:tournament.{$this->tournament->id},AuctionStarted" => 'handleAuctionStarted',
            "echo-presence:tournament.{$this->tournament->id},AuctionPaused" => 'handleAuctionPaused',
            "echo-presence:tournament.{$this->tournament->id},PlayerShuffled" => 'handlePlayerShuffled',
        ];
    }

    public function handleBidPlaced(array $payload = []): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        if (isset($payload['actor_id']) && (int) $payload['actor_id'] === (int) auth()->id()) {
            return;
        }

        $this->dispatch('auction-activity', action: 'bid');
    }

    public function handleTimerExtended(array $payload = []): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        if (isset($payload['actor_id']) && (int) $payload['actor_id'] === (int) auth()->id()) {
            return;
        }

        $this->dispatch('auction-activity', action: 'timer_extended');
    }

    public function handlePlayerSold(array $payload = []): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        if (isset($payload['actor_id']) && (int) $payload['actor_id'] === (int) auth()->id()) {
            return;
        }

        $this->dispatch('auction-activity', action: 'player_sold');
    }

    public function handleAuctionStarted(array $payload = []): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        if (isset($payload['actor_id']) && (int) $payload['actor_id'] === (int) auth()->id()) {
            return;
        }

        $this->dispatch('auction-activity', action: 'auction_started');
    }

    public function handleAuctionPaused(array $payload = []): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        if (isset($payload['actor_id']) && (int) $payload['actor_id'] === (int) auth()->id()) {
            return;
        }

        $this->dispatch('auction-activity', action: 'auction_paused');
    }

    public function handlePlayerShuffled(array $payload = []): void
    {
        if ($this->soundTriggerMode !== 'websocket') {
            return;
        }

        if (isset($payload['actor_id']) && (int) $payload['actor_id'] === (int) auth()->id()) {
            return;
        }

        $this->dispatch('auction-activity', action: 'player_shuffled');
    }

    public function mount(Tournament $tournament): void
    {
        $this->authorize('update', $tournament);
        $this->tournament = $tournament;
        $this->soundTriggerMode = (string) Cache::get('platform_sound_trigger_mode', 'polling');
        $this->selectedTournamentId = (int) $tournament->id;

        $currentPlayerId = Auction::query()
            ->where('tournament_id', $tournament->id)
            ->value('current_player_id');

        $this->selectedPlayerId = $currentPlayerId ? (int) $currentPlayerId : null;
        $this->selectedBidTeamId = Team::query()
            ->where('tournament_id', $this->tournament->id)
            ->orderBy('name')
            ->value('id');
        $this->snapshotKey = $this->buildSnapshotKey();
    }

    public function refreshAuctionState(): void
    {
        if ($this->lockedAdvanceAt !== null && now()->timestamp >= $this->lockedAdvanceAt) {
            $this->finalizeLockedAdvance();
        }

        $currentSnapshot = $this->buildSnapshotKey();

        if ($this->soundTriggerMode === 'polling' && $this->snapshotKey !== '' && $this->snapshotKey !== $currentSnapshot) {
            $this->dispatch('auction-activity', action: $this->detectActionFromSnapshots($this->snapshotKey, $currentSnapshot));
        }

        $this->snapshotKey = $currentSnapshot;
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

    private function buildSnapshotKey(): string
    {
        $auction = Auction::query()
            ->where('tournament_id', $this->tournament->id)
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

    public function loadTournament(): void
    {
        $targetId = (int) $this->selectedTournamentId;

        if ($targetId <= 0) {
            $this->dispatch('toast', message: 'Please select a tournament first.');
            return;
        }

        if ((int) $this->tournament->id === $targetId) {
            $this->dispatch('toast', message: 'This tournament is already loaded.');
            return;
        }

        $target = Tournament::query()
            ->where('admin_id', auth()->id())
            ->find($targetId);

        if (! $target) {
            $this->dispatch('toast', message: 'Selected tournament is not accessible.');
            return;
        }

        $this->redirectRoute('admin.auction.control', ['tournament' => $target->id], navigate: true);
    }

    public function updatedStartMode(string $value): void
    {
        if (! in_array($value, ['auto', 'manual'], true)) {
            $this->startMode = 'manual';
            return;
        }

        if ($value === 'manual') {
            $auction = Auction::query()
                ->where('tournament_id', $this->tournament->id)
                ->first();

            if ($auction && ! $auction->is_paused) {
                $auction->update(['is_paused' => true]);
                $this->safeBroadcast(new AuctionPaused($this->tournament->id, auth()->id()));
            }

            $this->dispatch('toast', message: 'Manual mode enabled. Choose a player card and click Bring Live.');
            return;
        }

        $modeLabel = $this->randomPickEnabled ? 'random player pick is ON' : 'random player pick is OFF';
        $this->dispatch('toast', message: "Auto mode enabled. {$modeLabel}.");
    }

    public function updatedRandomPickEnabled(bool $value): void
    {
        $this->randomPickEnabled = $value;

        if ($this->startMode !== 'auto') {
            return;
        }

        $message = $value
            ? 'Random player pick enabled for auto mode.'
            : 'Random player pick disabled. Auto mode will use player order.';

        $this->dispatch('toast', message: $message);
    }

    public function startAuction(): void
    {
        $auction = Auction::query()->firstOrCreate(
            ['tournament_id' => $this->tournament->id],
            ['current_bid' => 0]
        );

        // If auction is paused with a live player, treat Start as Resume and preserve remaining time.
        if ($auction->is_paused && $auction->current_player_id) {
            $pausedRemaining = 0;
            if ($auction->ends_at && $auction->updated_at) {
                $pausedRemaining = max((int) $auction->updated_at->diffInSeconds($auction->ends_at, false), 0);
            }

            $auction->update([
                'is_paused' => false,
                'ends_at' => $pausedRemaining > 0
                    ? now()->addSeconds($pausedRemaining)
                    : now()->addSeconds($this->tournament->auction_timer_seconds),
            ]);

            $this->selectedPlayerId = (int) $auction->current_player_id;
            $this->safeBroadcast(new AuctionStarted($this->tournament->id, auth()->id()));
            $this->dispatch('toast', message: 'Auction resumed.');
            return;
        }

        $currentPlayerId = null;

        if ($this->startMode === 'manual') {
            if (! $this->selectedPlayerId) {
                $this->dispatch('toast', message: 'Select a player first in manual mode.');
                return;
            }

            $currentPlayerId = Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'available')
                ->whereKey($this->selectedPlayerId)
                ->value('id');
        } else {
            $currentPlayerId = $this->resolveCurrentPlayerId($auction);
        }

        if (! $currentPlayerId) {
            $this->dispatch('toast', message: 'No available players to start auction.');
            return;
        }

        $auction->update([
            'current_player_id' => $currentPlayerId,
            'current_highest_team_id' => null,
            'current_bid' => 0,
            'is_paused' => false,
            'started_at' => now(),
            'ends_at' => now()->addSeconds($this->tournament->auction_timer_seconds),
        ]);

        $this->selectedPlayerId = (int) $currentPlayerId;

        $this->safeBroadcast(new AuctionStarted($this->tournament->id, auth()->id()));
        $this->dispatch('toast', message: 'Auction started.');
    }

    public function placeBidForTeam(int $teamId, AuctionEngine $auctionEngine): void
    {
        if (! $this->isAdminOnlyBidding()) {
            $this->dispatch('toast', message: 'Admin bidding is disabled for this tournament.');
            return;
        }

        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();

        if (! $auction || ! $auction->current_player_id) {
            $this->dispatch('toast', message: 'No active player to place bid on.');
            return;
        }

        $team = Team::query()
            ->where('tournament_id', $this->tournament->id)
            ->whereKey($teamId)
            ->first();

        if (! $team) {
            $this->dispatch('toast', message: 'Team is not available for this tournament.');
            return;
        }

        try {
            $auctionEngine->placeBid((int) $team->id, (int) $auction->current_player_id);
            $this->dispatch('toast', message: 'Bid placed for '.$team->name.'.');
        } catch (AuctionException $exception) {
            $this->dispatch('toast', message: $exception->getMessage());
        }
    }

    public function viewSquad(int $teamId): void
    {
        $team = Team::query()
            ->where('tournament_id', $this->tournament->id)
            ->whereKey($teamId)
            ->first();

        if (! $team) {
            $this->dispatch('toast', message: 'Team not found.');
            return;
        }

        $this->squadTeamName = $team->name;
        $this->squadPlayers = Player::query()
            ->where('tournament_id', $this->tournament->id)
            ->where('sold_team_id', $team->id)
            ->where('status', 'sold')
            ->with('category:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'serial_no', 'image_path', 'category_id', 'final_price'])
            ->map(fn (Player $player) => [
                'id' => $player->id,
                'name' => $player->name,
                'serial_no' => $player->serial_no,
                'image_url' => $player->image_url,
                'category' => $player->category?->name,
                'final_price' => $player->final_price,
            ])
            ->all();

        $this->showSquadModal = true;
    }

    public function closeSquadModal(): void
    {
        $this->showSquadModal = false;
    }

    public function bringPlayerLive(int $playerId): void
    {
        $player = Player::query()
            ->where('tournament_id', $this->tournament->id)
            ->where('status', 'available')
            ->find($playerId);

        if (! $player) {
            $this->dispatch('toast', message: 'Selected player is not available.');
            return;
        }

        $auction = Auction::query()->firstOrCreate(
            ['tournament_id' => $this->tournament->id],
            ['current_bid' => 0]
        );

        $auction->update([
            'current_player_id' => $player->id,
            'current_highest_team_id' => null,
            'current_bid' => 0,
            'last_bid_at' => null,
            'is_paused' => false,
            'started_at' => $auction->started_at ?? now(),
            'ends_at' => now()->addSeconds($this->tournament->auction_timer_seconds),
        ]);

        $this->selectedPlayerId = (int) $player->id;

        $this->safeBroadcast(new PlayerShuffled($this->tournament->id, (int) $player->id, auth()->id()));
        $this->safeBroadcast(new AuctionStarted($this->tournament->id, auth()->id()));

        $this->dispatch('player-live-set');
        $this->dispatch('toast', message: 'Player brought live.');
    }

    public function bringNextAvailable(): void
    {
        $nextPlayerId = Player::query()
            ->where('tournament_id', $this->tournament->id)
            ->where('status', 'available')
            ->orderBy('id')
            ->value('id');

        if (! $nextPlayerId) {
            $this->dispatch('toast', message: 'No available players to bring live.');
            return;
        }

        $this->bringPlayerLive((int) $nextPlayerId);
    }

    public function pauseAuction(): void
    {
        Auction::query()->where('tournament_id', $this->tournament->id)->update(['is_paused' => true]);
        $this->safeBroadcast(new AuctionPaused($this->tournament->id, auth()->id()));
        $this->dispatch('toast', message: 'Auction paused.');
    }

    public function resumeAuction(): void
    {
        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();
        if (! $auction) {
            $this->dispatch('toast', message: 'Auction is not initialized yet.');
            return;
        }

        if (! $auction->current_player_id) {
            $nextPlayerId = $this->resolveCurrentPlayerId($auction);
            if (! $nextPlayerId) {
                $this->dispatch('toast', message: 'No available players to resume auction.');
                return;
            }
            $auction->current_player_id = $nextPlayerId;
        }

        $pausedRemaining = 0;
        if ($auction->is_paused && $auction->ends_at && $auction->updated_at) {
            $pausedRemaining = max((int) $auction->updated_at->diffInSeconds($auction->ends_at, false), 0);
        }

        $auction->is_paused = false;
        if ($pausedRemaining > 0) {
            $auction->ends_at = now()->addSeconds($pausedRemaining);
        } elseif (! $auction->ends_at || now()->greaterThan($auction->ends_at)) {
            $auction->ends_at = now()->addSeconds($this->tournament->auction_timer_seconds);
        }
        $auction->save();

        $this->safeBroadcast(new AuctionStarted($this->tournament->id, auth()->id()));
        $this->dispatch('toast', message: 'Auction resumed.');
    }

    public function extendTimer(int $seconds = 30): void
    {
        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();
        if (! $auction || ! $auction->ends_at) {
            $this->dispatch('toast', message: 'Cannot extend timer before auction starts.');
            return;
        }

        $auction->update(['ends_at' => $auction->ends_at->addSeconds($seconds)]);
        $this->safeBroadcast(new TimerExtended($this->tournament->id, $seconds, auth()->id()));
        $this->dispatch('toast', message: "Timer extended by {$seconds}s.");
    }

    public function markSold(AuctionEngine $auctionEngine): void
    {
        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();
        if (! $auction) {
            $this->dispatch('toast', message: 'Auction is not initialized yet.');
            return;
        }

        $lockedPlayer = null;
        if ($auction->current_player_id) {
            $player = Player::query()
                ->with('category:id,name')
                ->find($auction->current_player_id, ['id', 'name', 'serial_no', 'image_path', 'category_id']);

            $winnerTeam = null;
            if ($auction->current_highest_team_id) {
                $winnerTeam = Team::query()->whereKey($auction->current_highest_team_id)->first(['id', 'name', 'logo_path']);
            }

            if ($player) {
                $lockedPlayer = [
                    'id' => (int) $player->id,
                    'name' => $player->name,
                    'serial_no' => $player->serial_no,
                    'image_url' => $player->image_url,
                    'category' => $player->category?->name,
                    'amount' => (float) ($auction->current_bid ?? 0),
                    'team' => $winnerTeam?->name,
                    'team_logo_url' => $winnerTeam?->logo_url,
                ];
            }
        }

        try {
            $auctionEngine->markPlayerSold($auction->id);
            if ($this->startMode === 'auto') {
                $auction->update([
                    'current_player_id' => null,
                    'current_highest_team_id' => null,
                    'current_bid' => 0,
                    'last_bid_at' => null,
                    'ends_at' => null,
                    'is_paused' => true,
                ]);
                $this->selectedPlayerId = null;
                $this->lockedAdvanceAt = now()->addSeconds(7)->timestamp;
            } else {
                $auction->update([
                    'current_player_id' => null,
                    'current_highest_team_id' => null,
                    'current_bid' => 0,
                    'last_bid_at' => null,
                    'ends_at' => null,
                    'is_paused' => true,
                ]);
                $this->selectedPlayerId = null;
            }
            $this->dispatch('auction-player-locked', player: $lockedPlayer);
            $this->dispatch('toast', message: 'Player marked sold.');
        } catch (AuctionException $exception) {
            $this->dispatch('toast', message: $exception->getMessage());
        }
    }

    public function finalizeLockedAdvance(): void
    {
        if ($this->lockedAdvanceAt === null) {
            return;
        }

        $this->lockedAdvanceAt = null;

        if ($this->startMode !== 'auto') {
            return;
        }

        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();
        if (! $auction) {
            return;
        }

        $this->advanceToNextPlayer($auction);
    }

    public function markUnsold(): void
    {
        $auction = Auction::query()->where('tournament_id', $this->tournament->id)->first();
        if (! $auction || ! $auction->current_player_id) {
            $this->dispatch('toast', message: 'No active player to mark unsold.');
            return;
        }

        $player = Player::query()->whereKey($auction->current_player_id)->first();
        if (! $player) {
            $this->dispatch('toast', message: 'Current player not found.');
            return;
        }

        $player->update([
            'status' => 'unsold',
            'sold_team_id' => null,
            'final_price' => null,
        ]);

        if ($this->startMode === 'auto') {
            $this->advanceToNextPlayer($auction);
        } else {
            $auction->update([
                'current_player_id' => null,
                'current_highest_team_id' => null,
                'current_bid' => 0,
                'last_bid_at' => null,
                'ends_at' => null,
                'is_paused' => true,
            ]);
            $this->selectedPlayerId = null;
        }

        $this->dispatch('toast', message: 'Player marked UNSOLD.');
    }

    public function shufflePlayers(): void
    {
        $auction = Auction::query()->firstOrCreate(
            ['tournament_id' => $this->tournament->id],
            ['current_bid' => 0]
        );

        $nextPlayerId = Player::query()
            ->where('tournament_id', $this->tournament->id)
            ->where('status', 'available')
            ->inRandomOrder()
            ->value('id');

        if (! $nextPlayerId) {
            $recycled = Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'unsold')
                ->update(['status' => 'available']);

            if ($recycled > 0) {
                $nextPlayerId = Player::query()
                    ->where('tournament_id', $this->tournament->id)
                    ->where('status', 'available')
                    ->inRandomOrder()
                    ->value('id');
            }
        }

        if (! $nextPlayerId) {
            $this->dispatch('toast', message: 'No players available to shuffle.');
            return;
        }

        $auction->update([
            'current_player_id' => $nextPlayerId,
            'current_highest_team_id' => null,
            'current_bid' => 0,
            'last_bid_at' => null,
            'ends_at' => now()->addSeconds($this->tournament->auction_timer_seconds),
        ]);

        $this->selectedPlayerId = (int) $nextPlayerId;

        $this->safeBroadcast(new PlayerShuffled($this->tournament->id, (int) $nextPlayerId, auth()->id()));

        $this->dispatch('toast', message: 'Players shuffled. New player loaded.');
    }

    private function resolveCurrentPlayerId(Auction $auction): ?int
    {
        $currentIsAvailable = $auction->current_player_id
            ? Player::query()
                ->whereKey($auction->current_player_id)
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'available')
                ->exists()
            : false;

        if ($currentIsAvailable) {
            return (int) $auction->current_player_id;
        }

        $query = Player::query()
            ->where('tournament_id', $this->tournament->id)
            ->where('status', 'available');

        if ($this->shouldUseRandomAutoPick()) {
            return $query->inRandomOrder()->value('id');
        }

        return $query->orderBy('id')->value('id');
    }

    private function advanceToNextPlayer(Auction $auction): void
    {
        $baseQuery = Player::query()
            ->where('tournament_id', $this->tournament->id)
            ->where('status', 'available');

        $nextPlayerId = $this->shouldUseRandomAutoPick()
            ? $baseQuery->inRandomOrder()->value('id')
            : $baseQuery->orderBy('id')->value('id');

        if (! $nextPlayerId) {
            $recycledUnsold = Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'unsold')
                ->update(['status' => 'available']);

            if ($recycledUnsold > 0) {
                $nextPlayerId = Player::query()
                    ->where('tournament_id', $this->tournament->id)
                    ->where('status', 'available')
                    ->when(
                        $this->shouldUseRandomAutoPick(),
                        fn ($query) => $query->inRandomOrder(),
                        fn ($query) => $query->orderBy('id')
                    )
                    ->value('id');
            }
        }

        if ($nextPlayerId) {
            $auction->update([
                'current_player_id' => $nextPlayerId,
                'current_highest_team_id' => null,
                'current_bid' => 0,
                'last_bid_at' => null,
                'ends_at' => now()->addSeconds($this->tournament->auction_timer_seconds),
                'is_paused' => false,
            ]);
            $this->selectedPlayerId = (int) $nextPlayerId;
            return;
        }

        $auction->update([
            'current_player_id' => null,
            'current_highest_team_id' => null,
            'current_bid' => 0,
            'last_bid_at' => null,
            'ends_at' => null,
            'is_paused' => true,
        ]);

        $this->selectedPlayerId = null;
    }

    private function shouldUseRandomAutoPick(): bool
    {
        return $this->startMode === 'auto' && $this->randomPickEnabled;
    }

    public function render()
    {
        $auction = Auction::query()
            ->with('currentPlayer:id,name,serial_no,image_path,category_id', 'currentPlayer.category:id,name', 'currentHighestTeam:id,name,logo_path,primary_color,secondary_color')
            ->where('tournament_id', $this->tournament->id)
            ->first();

        $remainingSeconds = 0;
        if ($auction?->ends_at) {
            if ($auction->is_paused && $auction->updated_at) {
                $remainingSeconds = max((int) $auction->updated_at->diffInSeconds($auction->ends_at, false), 0);
            } else {
                $remainingSeconds = max((int) now()->diffInSeconds($auction->ends_at, false), 0);
            }
        }

        return view('livewire.admin.auction.control-panel', [
            'auction' => $auction,
            'remainingSeconds' => $remainingSeconds,
            'tournaments' => Tournament::query()
                ->where('admin_id', auth()->id())
                ->orderBy('name')
                ->get(['id', 'name']),
            'modalPlayers' => Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->with('category:id,name', 'soldTeam:id,name')
                ->orderByRaw("CASE status WHEN 'available' THEN 1 WHEN 'unsold' THEN 2 WHEN 'sold' THEN 3 ELSE 4 END")
                ->orderBy('name')
                ->get(['id', 'name', 'serial_no', 'image_path', 'category_id', 'base_price', 'status', 'sold_team_id', 'final_price']),
            'availableCount' => Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'available')
                ->count(),
            'unsoldCount' => Player::query()
                ->where('tournament_id', $this->tournament->id)
                ->where('status', 'unsold')
                ->count(),
            'leaderboard' => Team::query()
                ->where('tournament_id', $this->tournament->id)
                ->orderByDesc('squad_count')
                ->limit(5)
                ->get(['id', 'name', 'logo_path', 'primary_color', 'secondary_color', 'wallet_balance', 'squad_count']),
            'bidTeams' => Team::query()
                ->where('tournament_id', $this->tournament->id)
                ->orderBy('name')
                ->get(['id', 'name', 'logo_path', 'wallet_balance', 'is_locked']),
        ]);
    }
}
