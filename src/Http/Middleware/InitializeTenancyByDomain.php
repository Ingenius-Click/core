<?php

namespace Ingenius\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain as BaseInitializeTenancyByDomain;

class InitializeTenancyByDomain extends BaseInitializeTenancyByDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check for X-Forwarded-Host header first (for load balancers/proxies)
        $forwardedHost = $request->header('X-Forwarded-Host');
        $originalHost = $request->getHost();

        $queryParamForwardedHost = $request->query('tenant');

        $fallbackHost = $queryParamForwardedHost ?: $originalHost;

        $host = $forwardedHost ?: $fallbackHost;


        try {
            return $this->initializeTenancy($request, $next, $host);
        } catch (\Exception $e) {
            Log::error('Tenancy initialization failed', [
                'host' => $host,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
