<?php

namespace Ingenius\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\Models\Domain;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dynamic CORS middleware for API routes.
 *
 * Allows requests from two origin sources:
 *   1. Tenant domains — fetched from the central DB domains table and cached.
 *      Only the root domain is stored (e.g. zonakliente.com); any subdomain is
 *      automatically allowed (backoffice.zonakliente.com, api.zonakliente.com…).
 *   2. Extra origins — comma-separated list in CORS_EXTRA_ORIGINS env variable,
 *      for central admin frontends or any other trusted origin not in the DB.
 *      Example: CORS_EXTRA_ORIGINS=admin.ingenius.click,localhost:3000
 *
 * Cache is invalidated by TTL. To allow a newly added tenant domain immediately,
 * flush the application cache manually.
 */
class DynamicCorsMiddleware
{
    protected const CACHE_KEY = 'cors_tenant_domains';

    protected const CACHE_TTL = 300; // 5 minutes

    protected const ALLOWED_METHODS = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';

    protected const ALLOWED_HEADERS = 'Authorization, Content-Type, X-Requested-With, X-Guest-Token, Accept, Origin, X-Tenant';

    protected const MAX_AGE = '86400';

    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');

        if (!$origin) {
            return $next($request);
        }

        $host = parse_url($origin, PHP_URL_HOST);

        Log::info('Incomming Host');
        Log::info($host);

        if (!$host || !$this->isAllowedOrigin($host)) {
            return $next($request);
        }

        if ($request->isMethod('OPTIONS')) {
            return $this->preflightResponse($origin);
        }

        $response = $next($request);

        return $this->withCorsHeaders($response, $origin);
    }

    protected function isAllowedOrigin(string $host): bool
    {
        foreach ($this->getAllowedBaseDomains() as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                Log::info('DynamicCors');
                Log::info($host);
                return true;
            }
        }

        return false;
    }

    protected function getAllowedBaseDomains(): array
    {
        $tenantDomains = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Domain::pluck('domain')->all();
        });

        $extraOrigins = array_filter(
            explode(',', env('CORS_EXTRA_ORIGINS', ''))
        );

        return array_merge($tenantDomains, $extraOrigins);
    }

    protected function preflightResponse(string $origin): Response
    {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', $origin)
            ->header('Access-Control-Allow-Methods', self::ALLOWED_METHODS)
            ->header('Access-Control-Allow-Headers', self::ALLOWED_HEADERS)
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Max-Age', self::MAX_AGE)
            ->header('Vary', 'Origin');
    }

    protected function withCorsHeaders(Response $response, string $origin): Response
    {
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Vary', 'Origin');

        return $response;
    }
}
