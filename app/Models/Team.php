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
        'primary_color',
        'secondary_color',
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
}
