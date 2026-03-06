<?php

namespace App\Events;

use App\Models\Bid;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Bid $bid, public ?int $actorId = null)
    {
        $this->actorId = $this->actorId ?? auth()->id();
        $this->bid->loadMissing('team:id,name,logo_path');
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('tournament.'.$this->bid->tournament_id),
            new Channel('tournament.public.'.$this->bid->tournament_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'BidPlaced';
    }

    public function broadcastWith(): array
    {
        return [
            'bid_id' => $this->bid->id,
            'auction_id' => $this->bid->auction_id,
            'player_id' => $this->bid->player_id,
            'team_id' => $this->bid->team_id,
            'team_name' => $this->bid->team?->name,
            'team_logo' => $this->bid->team?->logo_path,
            'amount' => $this->bid->amount,
            'is_auto_bid' => $this->bid->is_auto_bid,
            'actor_id' => $this->actorId,
            'created_at' => $this->bid->created_at?->toIso8601String(),
        ];
    }
}
