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
        // Check for X-Tenant header first (for tenant identification)
        $tenantHeader = $request->header('X-Tenant');
        $originalHost = $request->getHost();

        $queryParamTenant = $request->query('tenant');

        $fallbackHost = $queryParamTenant ?: $originalHost;

        $host = $tenantHeader ?: $fallbackHost;


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
