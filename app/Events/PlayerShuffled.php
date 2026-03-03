<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerShuffled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $tournamentId, public int $playerId)
    {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('tournament.'.$this->tournamentId)];
    }

    public function broadcastAs(): string
    {
        return 'PlayerShuffled';
    }
}
