<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $tenantContext = app(TenantContext::class);

        if ($user->hasRole('SuperAdmin')) {
            $tenantContext->setAdminId(null);

            return $next($request);
        }

        $adminId = $user->hasRole('Admin') ? $user->id : $user->parent_admin_id;

        if (! $adminId && $user->hasRole('Team')) {
            $adminId = Team::query()->where('user_id', $user->id)->value('admin_id');
        }

        if (! $adminId) {
            abort(403, 'Tenant context could not be resolved.');
        }

        $tenantContext->setAdminId($adminId);

        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if (! is_object($parameter)) {
                continue;
            }

            if (isset($parameter->admin_id) && (int) $parameter->admin_id !== $adminId) {
                abort(403, 'Cross-tenant access denied.');
            }
        }

        return $next($request);
    }
}
