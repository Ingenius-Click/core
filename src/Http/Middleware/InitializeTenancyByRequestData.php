<?php

namespace Ingenius\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData as BaseInitializeTenancyByRequestData;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Tenancy;

class InitializeTenancyByRequestData extends BaseInitializeTenancyByRequestData
{
    public static $header = 'X-Tenant';

    public static $queryParameter = 'tenant';

    public function __construct(Tenancy $tenancy, DomainTenantResolver $resolver)
    {
        $this->tenancy = $tenancy;
        $this->resolver = $resolver;
    }

    public function handle($request, Closure $next)
    {
        if ($request->method() === 'OPTIONS') {
            return $next($request);
        }

        $payload = $this->getPayload($request);

        try {
            return $this->initializeTenancy($request, $next, $payload);
        } catch (\Exception $e) {
            Log::error('Tenancy initialization by request data failed', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
