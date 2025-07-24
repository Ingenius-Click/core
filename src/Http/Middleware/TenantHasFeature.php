<?php

namespace Ingenius\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TenantHasFeature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Check if we're in a tenant context
        if (!tenant()) {
            abort(404, 'Not found');
        }

        if (!tenant()->hasFeature($feature)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
