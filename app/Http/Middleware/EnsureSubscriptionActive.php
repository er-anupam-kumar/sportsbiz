<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasRole('SuperAdmin')) {
            return $next($request);
        }

        $adminId = $user->hasRole('Admin') ? $user->id : $user->parent_admin_id;

        $activeSubscription = Subscription::query()
            ->where('admin_id', $adminId)
            ->where('is_active', true)
            ->whereDate('expires_at', '>=', now())
            ->exists();

        if (! $activeSubscription) {
            abort(403, 'Subscription expired or inactive.');
        }

        return $next($request);
    }
}
