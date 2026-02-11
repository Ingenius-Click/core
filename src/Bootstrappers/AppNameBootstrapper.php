<?php

namespace Ingenius\Core\Bootstrappers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;

class AppNameBootstrapper implements TenancyBootstrapper
{
    protected string $originalAppName;
    protected string $originalMailFromName;

    public function bootstrap(Tenant $tenant): void
    {
        // Store the original values
        $this->originalAppName = Config::get('app.name');
        $this->originalMailFromName = Config::get('mail.from.name');

        // Get tenant name
        $tenantName = $tenant->name ?? $tenant->id;

        Log::info('AppNameBootstrapper: Setting app name', [
            'original_app_name' => $this->originalAppName,
            'original_mail_from_name' => $this->originalMailFromName,
            'tenant_name' => $tenantName,
            'tenant_id' => $tenant->getTenantKey(),
        ]);

        // Set app name to the tenant's name
        // Config::set('app.name', $tenantName);

        // Also set mail.from.name to use the tenant's name
        Config::set('mail.from.name', $tenantName);

        // Verify it was set
        Log::info('AppNameBootstrapper: After set', [
            'config_app_name' => Config::get('app.name'),
            'config_mail_from_name' => Config::get('mail.from.name'),
        ]);
    }

    public function revert(): void
    {
        Log::info('AppNameBootstrapper: Reverting app name', [
            'reverting_app_name_to' => $this->originalAppName ?? 'not set',
            'reverting_mail_from_name_to' => $this->originalMailFromName ?? 'not set',
        ]);

        // Restore the original values when tenancy ends
        if (isset($this->originalAppName)) {
            // Config::set('app.name', $this->originalAppName);
        }

        if (isset($this->originalMailFromName)) {
            Config::set('mail.from.name', $this->originalMailFromName);
        }
    }
}
