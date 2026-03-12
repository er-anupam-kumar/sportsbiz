<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'sport_id',
        'name',
        'banner_path',
        'purse_amount',
        'max_players_per_team',
        'category_limits',
        'base_increment',
        'auction_timer_seconds',
        'anti_sniping',
        'auction_type',
        'bidding_type',
        'status',
        'starts_at',
        'trade_window_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'category_limits' => 'array',
            'anti_sniping' => 'boolean',
            'starts_at' => 'datetime',
            'trade_window_ends_at' => 'datetime',
            'purse_amount' => 'decimal:2',
            'base_increment' => 'decimal:2',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(PlayerCategory::class);
    }

    public function auction(): HasOne
    {
        return $this->hasOne(Auction::class);
    }

    public function getBannerUrlAttribute(): string
    {
        if (! $this->banner_path) {
            return asset('images/team-placeholder.svg');
        }

        return str_starts_with($this->banner_path, 'http')
            ? $this->banner_path
            : asset('storage/'.$this->banner_path);
    }
}
