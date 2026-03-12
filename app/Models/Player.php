<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'tournament_id',
        'category_id',
        'name',
        'serial_no',
        'base_price',
        'image_path',
        'stats',
        'age',
        'country',
        'previous_team',
        'status',
        'sold_team_id',
        'final_price',
    ];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
            'base_price' => 'decimal:2',
            'final_price' => 'decimal:2',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlayerCategory::class, 'category_id');
    }

    public function soldTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'sold_team_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image_path) {
            return asset('images/team-placeholder.svg');
        }

        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : asset('storage/'.$this->image_path);
    }
}
