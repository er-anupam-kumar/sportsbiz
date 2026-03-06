<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerSold implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $tournamentId,
        public int $auctionId,
        public int $playerId,
        public int $teamId,
        public float $amount,
        public ?int $actorId = null,
    ) {
        $this->actorId = $this->actorId ?? auth()->id();
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('tournament.'.$this->tournamentId),
            new Channel('tournament.public.'.$this->tournamentId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PlayerSold';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_id' => $this->auctionId,
            'player_id' => $this->playerId,
            'team_id' => $this->teamId,
            'amount' => $this->amount,
            'actor_id' => $this->actorId,
        ];
    }
}
