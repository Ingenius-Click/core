<?php

namespace Ingenius\Core\Actions;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Ingenius\Core\Events\TenantCreated;
use Ingenius\Core\Models\Template;
use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Settings\CustomizeSettings;
use Ingenius\Core\Support\TenantInitializationManager;

class CreateTenantAction
{
    private TenantInitializationManager $tenantInitializationManager;

    public function __construct(TenantInitializationManager $tenantInitializationManager)
    {
        $this->tenantInitializationManager = $tenantInitializationManager;
    }

    public function handle(array $data): Tenant
    {
        $d = [];

        $id = Str::slug($data['name'], '_');

        $d['id'] = $id;

        $template = Template::where('identifier', $data['template'])->first();

        $d['template_id'] = $template->id;

        $d['styles'] = isset($data['styles']) ? $data['styles'] : $template->styles_vars;

        $tenant = Tenant::create($d);

        $tenant->setName($data['name']);

        $tenant->domains()->create([
            'domain' => $data['domain'],
        ]);

        Artisan::call('ingenius:tenants:migrate', [
            '--tenants' => [$id],
            '--force' => true,
        ]);

        $this->tenantInitializationManager->initializeTenantViaRequest($tenant, request());

        return $tenant;
    }
}
