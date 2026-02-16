<?php

namespace Ingenius\Core\Http\Middleware;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful as BaseEnsureFrontendRequestsAreStateful;

/**
 * Override Sanctum's EnsureFrontendRequestsAreStateful middleware
 *
 * The base Sanctum middleware hardcodes 'session.same_site' to 'lax' which doesn't work
 * for cross-domain multi-tenant setups. This override sets it to 'none' to allow
 * cross-domain cookie sharing between tenant frontends and the backend API.
 */
class EnsureFrontendRequestsAreStateful extends BaseEnsureFrontendRequestsAreStateful
{
    /**
     * Configure secure cookie sessions.
     *
     * Override to set same_site to 'none' for cross-domain tenant access
     * instead of 'lax' which is hardcoded in the base Sanctum middleware.
     *
     * @return void
     */
    protected function configureSecureCookieSessions()
    {
        config([
            'session.http_only' => true,
            'session.same_site' => 'none',
            'session.secure' => true,
        ]);
    }
}
