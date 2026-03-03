<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerSold implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $auctionId,
        public int $playerId,
        public int $teamId,
        public float $amount,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('auction.'.$this->auctionId)];
    }

    public function broadcastAs(): string
    {
        return 'PlayerSold';
    }
}
