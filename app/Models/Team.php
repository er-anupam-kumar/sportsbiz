<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'tournament_id',
        'user_id',
        'name',
        'logo_path',
        'jersey_image_path',
        'primary_color',
        'secondary_color',
        'captain_player_id',
        'wicketkeeper_player_id',
        'wallet_balance',
        'squad_count',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'wallet_balance' => 'decimal:2',
            'is_locked' => 'boolean',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(TeamWalletTransaction::class);
    }

    public function soldPlayers(): HasMany
    {
        return $this->hasMany(Player::class, 'sold_team_id');
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'captain_player_id');
    }

    public function wicketkeeper(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'wicketkeeper_player_id');
    }

    public function tournamentPoints(): HasMany
    {
        return $this->hasMany(TournamentPointTable::class, 'team_id');
    }

    public function homeFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    public function awayFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }

    public function jerseyRequests(): HasMany
    {
        return $this->hasMany(TeamJerseyRequest::class);
    }

    public function getLogoUrlAttribute(): string
    {
        if (! $this->logo_path) {
            return asset('images/team-placeholder.svg');
        }

        return str_starts_with($this->logo_path, 'http')
            ? $this->logo_path
            : asset('storage/'.$this->logo_path);
    }

    public function getJerseyImageUrlAttribute(): string
    {
        if (! $this->jersey_image_path) {
            return asset('images/team-placeholder.svg');
        }

        return str_starts_with($this->jersey_image_path, 'http')
            ? $this->jersey_image_path
            : asset('storage/'.$this->jersey_image_path);
    }
}
