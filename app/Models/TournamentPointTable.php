<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentPointTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'team_id',
        'position',
        'played',
        'won',
        'lost',
        'tied',
        'no_result',
        'points',
        'net_run_rate',
    ];

    protected function casts(): array
    {
        return [
            'net_run_rate' => 'decimal:3',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
