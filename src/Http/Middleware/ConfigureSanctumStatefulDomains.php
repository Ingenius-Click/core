<?php

namespace Ingenius\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * Configure Sanctum stateful domains and session settings before Sanctum middleware runs
 *
 * This middleware runs BEFORE StartSession and EnsureFrontendRequestsAreStateful and dynamically
 * configures the sanctum.stateful domains and session settings (same_site, secure) based on the
 * tenant identified from request headers (X-Tenant or ?tenant parameter).
 *
 * This ensures that session cookies are created with SameSite=none for cross-domain tenant access.
 */
class ConfigureSanctumStatefulDomains
{
    public function handle(Request $request, Closure $next)
    {
        // Get tenant domain from X-Tenant header or query parameter
        $tenantHeader = $request->header('X-Tenant');
        $queryTenant = $request->query('tenant');
        $tenantDomain = $tenantHeader ?: $queryTenant;

        if ($tenantDomain) {
            // Get current stateful domains (defaults from config)
            $statefulDomains = Config::get('sanctum.stateful', []);

            // Add the tenant domain to stateful list if not already present
            if (!in_array($tenantDomain, $statefulDomains)) {
                $statefulDomains[] = $tenantDomain;
                Config::set('sanctum.stateful', $statefulDomains);
            }

            // Configure session for cross-domain cookies BEFORE StartSession middleware
            Config::set([
                'session.same_site' => 'none',
                'session.secure' => true,
            ]);
        }

        return $next($request);
    }
}
