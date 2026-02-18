<?php

namespace Ingenius\Core\Http\Middleware;

use Closure;

/**
 * Soft variant of InitializeTenancyByRequestData.
 *
 * Initializes tenancy when an X-Tenant header or ?tenant query parameter is present.
 * Gracefully skips (passes through as central context) when neither is provided.
 *
 * Used on routes that must serve both central and tenant requests (e.g. /sanctum/csrf-cookie).
 */
class InitializeTenancyByRequestDataIfPresent extends InitializeTenancyByRequestData
{
    public function handle($request, Closure $next)
    {
        if ($request->method() === 'OPTIONS') {
            return $next($request);
        }

        $payload = $this->getPayload($request);

        if (!$payload) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
