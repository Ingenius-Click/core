<?php

namespace Ingenius\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * Configure Sanctum stateful domains before Sanctum middleware runs
 *
 * This middleware runs BEFORE EnsureFrontendRequestsAreStateful and dynamically
 * configures the sanctum.stateful domains based on the tenant identified from
 * request headers (X-Forwarded-Host or ?tenant parameter).
 */
class ConfigureSanctumStatefulDomains
{
    public function handle(Request $request, Closure $next)
    {
        // Get tenant domain from headers or query parameter
        $forwardedHost = $request->header('X-Forwarded-Host');
        $queryTenant = $request->query('tenant');
        $tenantDomain = $forwardedHost ?: $queryTenant;

        if ($tenantDomain) {
            // Get current stateful domains (defaults from config)
            $statefulDomains = Config::get('sanctum.stateful', []);

            // Add the tenant domain to stateful list if not already present
            if (!in_array($tenantDomain, $statefulDomains)) {
                $statefulDomains[] = $tenantDomain;
                Config::set('sanctum.stateful', $statefulDomains);
            }
        }

        return $next($request);
    }
}
