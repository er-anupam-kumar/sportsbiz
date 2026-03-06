<?php

namespace App\Support;

use App\Models\Player;
use App\Models\Subscription;
use App\Models\Team;
use App\Models\Tournament;

class AdminQuota
{
    public static function tournamentStats(int $adminId): array
    {
        return self::buildStats(
            $adminId,
            fn () => Tournament::query()->where('admin_id', $adminId)->count(),
            'max_tournaments'
        );
    }

    public static function teamStats(int $adminId): array
    {
        return self::buildStats(
            $adminId,
            fn () => Team::query()->where('admin_id', $adminId)->count(),
            'max_teams'
        );
    }

    public static function playerStats(int $adminId): array
    {
        return self::buildStats(
            $adminId,
            fn () => Player::query()->where('admin_id', $adminId)->count(),
            'max_players'
        );
    }

    public static function tournamentLimitMessage(int $adminId): ?string
    {
        $subscription = self::activeSubscription($adminId);

        if (! $subscription) {
            return 'No active subscription found for this admin.';
        }

        $used = Tournament::query()->where('admin_id', $adminId)->count();

        if ($used >= (int) $subscription->max_tournaments) {
            return 'Tournament limit reached for your plan.';
        }

        return null;
    }

    public static function teamLimitMessage(int $adminId): ?string
    {
        $subscription = self::activeSubscription($adminId);

        if (! $subscription) {
            return 'No active subscription found for this admin.';
        }

        $used = Team::query()->where('admin_id', $adminId)->count();

        if ($used >= (int) $subscription->max_teams) {
            return 'Team limit reached for your plan.';
        }

        return null;
    }

    public static function playerLimitMessage(int $adminId): ?string
    {
        $subscription = self::activeSubscription($adminId);

        if (! $subscription) {
            return 'No active subscription found for this admin.';
        }

        $used = Player::query()->where('admin_id', $adminId)->count();

        if ($used >= (int) $subscription->max_players) {
            return 'Player limit reached for your plan.';
        }

        return null;
    }

    private static function activeSubscription(int $adminId): ?Subscription
    {
        return Subscription::query()
            ->where('admin_id', $adminId)
            ->where('is_active', true)
            ->whereDate('expires_at', '>=', now())
            ->orderByDesc('expires_at')
            ->orderByDesc('id')
            ->first();
    }

    private static function buildStats(int $adminId, callable $usedResolver, string $limitField): array
    {
        $subscription = self::activeSubscription($adminId);

        if (! $subscription) {
            return [
                'used' => 0,
                'limit' => 0,
                'remaining' => 0,
                'has_subscription' => false,
            ];
        }

        $used = (int) $usedResolver();
        $limit = (int) $subscription->{$limitField};

        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => max(0, $limit - $used),
            'has_subscription' => true,
        ];
    }
}
