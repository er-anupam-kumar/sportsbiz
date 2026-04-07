<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamJerseyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'tournament_id',
        'team_id',
        'player_id',
        'request_for',
        'player_name',
        'staff_name',
        'size',
        'nickname',
        'jersey_number',
        'additional_jersey_required',
        'additional_jersey_quantity',
    ];

    protected function casts(): array
    {
        return [
            'additional_jersey_required' => 'boolean',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
