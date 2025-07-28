<?php

namespace Ingenius\Core\Helpers;

use Illuminate\Support\Facades\Auth;

/**
 * Helper for authentication across different guards in core package
 *
 * Example usage:
 *
 * ```php
 * use Ingenius\Core\Helpers\AuthHelper;
 *
 * // Get authenticated user from any guard
 * $user = AuthHelper::getUser();
 *
 * // Check if user is authenticated in any guard
 * if (AuthHelper::check()) {
 *    // User is authenticated
 * }
 *
 * // Get user from a specific guard
 * $tenantUser = AuthHelper::getUserFromGuard('tenant');
 * $sanctumUser = AuthHelper::getUserFromGuard('sanctum');
 * ```
 */
class AuthHelper
{
    /**
     * Get authenticated user from sanctum or tenant guard
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function getUser()
    {
        // Try to get user from sanctum guard first (for API authentication)
        $user = Auth::guard('sanctum')->user();

        // If no user found in sanctum guard, try tenant guard
        if (!$user) {
            $user = Auth::guard('tenant')->user();
        }

        // If still no user and we're not in tenant context, try web guard
        if (!$user && !tenant()) {
            $user = Auth::guard('web')->user();
        }

        return $user;
    }

    /**
     * Check if user is authenticated in any of the guards
     *
     * @return bool
     */
    public static function check()
    {
        // Check sanctum guard (API authentication)
        if (Auth::guard('sanctum')->check()) {
            return true;
        }

        // Check tenant guard (tenant context)
        if (Auth::guard('tenant')->check()) {
            return true;
        }

        // Check web guard (central context)
        if (!tenant() && Auth::guard('web')->check()) {
            return true;
        }

        return false;
    }

    /**
     * Get user from a specific guard
     *
     * @param string $guard
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function getUserFromGuard(string $guard)
    {
        return Auth::guard($guard)->user();
    }

    /**
     * Get the appropriate authentication guard for the current context
     *
     * @return string
     */
    public static function getContextualGuard(): string
    {
        // If we're in a tenant context, prefer tenant guard
        if (tenant()) {
            return 'tenant';
        }

        // For central context, use web guard
        return 'web';
    }

    /**
     * Get authenticated user from the contextually appropriate guard
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function getContextualUser()
    {
        $guard = self::getContextualGuard();
        return Auth::guard($guard)->user();
    }
}
