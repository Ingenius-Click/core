<?php

namespace Ingenius\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains as BasePreventAccessFromCentralDomains;

class PreventAccessFromCentralDomains extends BasePreventAccessFromCentralDomains
{
    public function handle(Request $request, Closure $next)
    {
        // Check for X-Forwarded-Host header first (for load balancers/proxies)
        $host = $request->header('X-Forwarded-Host') ?: $request->getHost();

        // If tenant query parameter is provided, use it as the host for validation
        // This allows verification links to work from central domains
        $tenantQueryParam = $request->query('tenant');
        if ($tenantQueryParam) {
            $host = $tenantQueryParam;
        }

        if (in_array($host, config('tenancy.central_domains'))) {
            $abortRequest = static::$abortRequest ?? function () {
                abort(404);
            };

            return $abortRequest($request, $next);
        }

        return $next($request);
    }
}