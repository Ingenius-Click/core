<?php

namespace Ingenius\Core\Actions;

use Illuminate\Support\Str;
use Ingenius\Core\Events\TenantCreated;
use Ingenius\Core\Models\Template;
use Ingenius\Core\Models\Tenant;
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

        $template_id = Template::where('identifier', $data['template'])->first()->id;

        $d['template_id'] = $template_id;

        $tenant = Tenant::create($d);

        $tenant->setName($data['name']);

        $tenant->domains()->create([
            'domain' => $data['domain'],
        ]);

        $this->tenantInitializationManager->initializeTenantViaRequest($tenant, request());

        return $tenant;
    }
}
