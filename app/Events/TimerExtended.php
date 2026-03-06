<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimerExtended implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $tournamentId, public int $seconds, public ?int $actorId = null)
    {
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
        return 'TimerExtended';
    }

    public function broadcastWith(): array
    {
        return [
            'seconds' => $this->seconds,
            'actor_id' => $this->actorId,
        ];
    }
}
