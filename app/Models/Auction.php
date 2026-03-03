<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'current_player_id',
        'current_highest_team_id',
        'current_bid',
        'is_paused',
        'started_at',
        'ends_at',
        'last_bid_at',
    ];

    protected function casts(): array
    {
        return [
            'current_bid' => 'decimal:2',
            'is_paused' => 'boolean',
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'last_bid_at' => 'datetime',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function currentPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'current_player_id');
    }

    public function currentHighestTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_highest_team_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }
}
