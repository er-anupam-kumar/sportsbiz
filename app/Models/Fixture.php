<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fixture extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'tournament_id',
        'home_team_id',
        'away_team_id',
        'winner_team_id',
        'loser_team_id',
        'home_source_type',
        'away_source_type',
        'home_source_fixture_id',
        'away_source_fixture_id',
        'home_slot_label',
        'away_slot_label',
        'match_at',
        'venue',
        'match_label',
        'status',
        'home_points',
        'away_points',
        'current_innings',
        'score_payload',
        'result_text',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'match_at' => 'datetime',
            'score_payload' => 'array',
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

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function loserTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'loser_team_id');
    }

    public function homeSourceFixture(): BelongsTo
    {
        return $this->belongsTo(self::class, 'home_source_fixture_id');
    }

    public function awaySourceFixture(): BelongsTo
    {
        return $this->belongsTo(self::class, 'away_source_fixture_id');
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->match_label ?: ('Match #'.$this->id);
    }

    public function getHomeDisplayNameAttribute(): string
    {
        return $this->homeTeam?->name
            ?? $this->home_slot_label
            ?? 'TBD';
    }

    public function getAwayDisplayNameAttribute(): string
    {
        return $this->awayTeam?->name
            ?? $this->away_slot_label
            ?? 'TBD';
    }
}
