<?php

use Illuminate\Database\Eloquent\Builder;
use Ingenius\Core\Services\AbstractTableHandler;
use Ingenius\Core\Services\SettingsService;

if (!function_exists('tenant')) {
    /**
     * Get the current tenant instance.
     *
     * @return \Ingenius\Core\Models\Tenant|null
     */
    function tenant()
    {
        return app(\Stancl\Tenancy\Tenancy::class)->tenant;
    }
}

if (!function_exists('tenancy')) {
    /**
     * Get the tenancy instance.
     *
     * @return \Stancl\Tenancy\Tenancy
     */
    function tenancy()
    {
        return app(\Stancl\Tenancy\Tenancy::class);
    }
}

if (!function_exists('is_tenant_route')) {
    /**
     * Check if the current route is a tenant route.
     *
     * @return bool
     */
    function is_tenant_route()
    {
        return tenant() !== null;
    }
}

if (!function_exists('settings')) {
    /**
     * Get the settings service instance.
     *
     * @param string|null $group The settings group
     * @param string|null $name The setting name
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed|\Ingenius\Core\Services\SettingsService
     */
    function settings(string $group = null, string $name = null, $default = null)
    {
        $settings = app(SettingsService::class);

        if (is_null($group)) {
            return $settings;
        }

        if (is_null($name)) {
            return $settings->getAllInGroup($group);
        }

        return $settings->get($group, $name, $default);
    }
}

if (!function_exists('central_user_class')) {
    /**
     * Get the central user model.
     *
     * @return \Ingenius\Core\Models\User
     */
    function central_user_class()
    {
        $userClass = config('core.central_user_model');

        return $userClass;
    }
}

if (!function_exists('tenant_user_class')) {
    /**
     * Get the tenant user model class.
     *
     * @return string
     */
    function tenant_user_class()
    {
        return config('core.tenant_user_model', 'Ingenius\\Auth\\Models\\User');
    }
}

if (!function_exists('table_handler_paginate')) {
    /**
     * Paginate a table handler.
     *
     * @param array $data
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    function table_handler_paginate(array $data, Builder $query, ?AbstractTableHandler $tableHandler = null)
    {
        $tableHandler = $tableHandler ?? app(AbstractTableHandler::class);

        return $tableHandler->paginate($data, $query);
    }
}

if (!function_exists('table_handler_paginate_with_metadata')) {
    /**
     * Paginate a table handler and include custom metadata.
     *
     * @param array $data
     * @param Builder $query
     * @param callable|array|null $metadataCallback A callback that receives the filtered query and returns metadata array,
     *                                               or an array of metadata to include directly, or null for no metadata
     * @return array Returns an array with 'paginator' and 'metadata' keys
     */
    function table_handler_paginate_with_metadata(array $data, Builder $query, $metadataCallback = null, ?AbstractTableHandler $tableHandler = null): array
    {
        $tableHandler = $tableHandler ?? app(AbstractTableHandler::class);

        $paginator = $tableHandler->paginate($data, $query);

        $filteredQuery = $tableHandler->getQuery();

        $metadata = [];

        if (is_callable($metadataCallback) && $filteredQuery) {
            $metadata = $metadataCallback($filteredQuery);
        } elseif (is_array($metadataCallback)) {
            $metadata = $metadataCallback;
        }

        return [
            'paginator' => $paginator,
            'metadata' => $metadata,
        ];
    }
}


if (!function_exists('format_date')) {
    /**
     * Format a date.
     *
     * @param string $date
     * @return string
     */
    function format_date($date)
    {
        return \Carbon\Carbon::parse($date)->format('d/m/Y H:i');
    }
}

if (!function_exists('generate_tenant_aware_image_url')) {
    /**
     * @param string $path
     * @return string
     */
    function generate_tenant_aware_image_url($path)
    {
        if (tenant()) {
            // For tenant context, use asset() which is tenant-aware
            return asset($path);
        }

        // For central app, use Storage::url()
        return Storage::url($path);
    }
}

if (!function_exists('convert_currency')) {
    /**
     * Convert an amount from base currency to the specified currency.
     * Uses the hook system to decouple from the coins package.
     *
     * @param int $amountInCents The amount in cents (base currency)
     * @param string|null $toCurrency The target currency code (e.g., 'EUR'). If null, uses current currency.
     * @param string|null $fromCurrency The source currency code. If null, uses base currency.
     * @return int The converted amount in cents
     */
    function convert_currency(int $amountInCents, ?string $toCurrency = null, ?string $fromCurrency = null): int
    {
        $hookManager = app(\Ingenius\Core\Services\PackageHookManager::class);

        $result = $hookManager->execute(
            'currency.convert',
            $amountInCents,
            [
                'to_currency' => $toCurrency ?? get_current_currency(),
                'from_currency' => $fromCurrency,
            ]
        );

        return $result ?? $amountInCents;
    }
}

if (!function_exists('get_current_currency')) {
    /**
     * Get the current currency code for the request.
     * Uses the hook system to decouple from the coins package.
     *
     * @return string The currency code (e.g., 'USD', 'EUR')
     */
    function get_current_currency(): string
    {
        $hookManager = app(\Ingenius\Core\Services\PackageHookManager::class);

        $result = $hookManager->execute('currency.current', null);

        return $result ?? 'USD';
    }
}

if (!function_exists('get_currency_metadata')) {
    /**
     * Get metadata for the current currency.
     * Uses the hook system to decouple from the coins package.
     *
     * @return array Currency metadata (code, symbol, position, exchange_rate)
     */
    function get_currency_metadata(): array
    {
        $hookManager = app(\Ingenius\Core\Services\PackageHookManager::class);

        $result = $hookManager->execute('currency.metadata', []);

        return $result ?? [
            'short_name' => 'USD',
            'symbol' => '$',
            'position' => 'front',
            'exchange_rate' => 1.0,
        ];
    }
}

if (!function_exists('format_currency_amount')) {
    /**
     * Format an amount with currency symbol and position.
     * Uses the hook system to decouple from the coins package.
     *
     * @param int $amountInCents The amount in cents
     * @param string|null $currencyCode The currency code. If null, uses current currency.
     * @return string Formatted currency string (e.g., '$100.00' or '100,00€')
     */
    function format_currency_amount(int $amountInCents, ?string $currencyCode = null): string
    {
        $hookManager = app(\Ingenius\Core\Services\PackageHookManager::class);

        $result = $hookManager->execute(
            'currency.format',
            $amountInCents,
            ['currency_code' => $currencyCode]
        );

        // Fallback formatting if hook not available
        if ($result === null || $result === $amountInCents) {
            $amount = $amountInCents / 100;
            return '$' . number_format($amount, 2);
        }

        return $result;
    }
}
