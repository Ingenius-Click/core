<?php

namespace Ingenius\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

// Configure Sanctum stateful domains and session settings for cross-domain tenant access.
//
// Supports two execution contexts:
//   - Pre-initialization (API routes): reads X-Tenant header or ?tenant param, configures
//     Sanctum before StartSession runs.
//   - Post-initialization (e.g. /sanctum/csrf-cookie): tenancy is already active, so the
//     domain is read from the initialized tenant. Central requests (no tenant) are skipped.
class ConfigureSanctumStatefulDomains
{
    public function handle(Request $request, Closure $next)
    {
        $tenantDomain = $this->resolveTenantDomain($request);

        if ($tenantDomain) {
            $statefulDomains = Config::get('sanctum.stateful', []);

            if (!in_array($tenantDomain, $statefulDomains)) {
                $statefulDomains[] = $tenantDomain;
                Config::set('sanctum.stateful', $statefulDomains);
            }

            Config::set([
                'session.same_site' => 'lax',
                'session.secure' => true,
            ]);
        }

        return $next($request);
    }

    protected function resolveTenantDomain(Request $request): ?string
    {
        // If tenancy is already initialized, read from the active tenant's first domain.
        if (tenancy()->initialized && tenancy()->tenant) {
            return optional(tenancy()->tenant->domains->first())->domain;
        }

        // Otherwise fall back to the request identifier (pre-initialization context).
        return $request->header('X-Tenant') ?: $request->query('tenant');
    }
}
