<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Ingenius\Core\Actions\CreateTenantAction;
use Ingenius\Core\Actions\PaginateTenantsAction;
use Ingenius\Core\Http\Controllers\Controller;
use Ingenius\Core\Http\Requests\CreateTenantRequest;
use Ingenius\Core\Http\Requests\UpdateStylesRequest;
use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Settings\CustomizeSettings;

class TenantsController extends Controller
{
    use AuthorizesRequests;

    public function index(PaginateTenantsAction $action): JsonResponse
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = $action->handle();

        return response()->api(message: 'Tenants fetched successfully', data: $tenants);
    }

    public function store(CreateTenantRequest $request, CreateTenantAction $action): JsonResponse
    {
        $this->authorize('create', Tenant::class);

        $tenant = $action->handle($request->validated());

        return response()->api(message: 'Tenant created successfully', data: $tenant);
    }

    public function updateStyles(UpdateStylesRequest $request, string $tenant): JsonResponse
    {
        $this->authorize('edit', Tenant::findOrFail($tenant));

        $tenant = Tenant::findOrFail($tenant);

        $tenant->update(['styles' => $request->validated('styles')]);

        return response()->api(message: 'Styles updated successfully', data: $tenant);
    }

    public function getLayout(CustomizeSettings $customizeSettings): JsonResponse
    {
        $tenant = Tenant::findOrFail(tenant()->id);

        $template = $tenant->template->identifier;

        $styles = $tenant->styles;

        $settings = [
            'logo' => $customizeSettings->store_logo,
            'name' => $customizeSettings->store_name,
        ];

        return response()->api(message: 'Layout fetched successfully', data: [
            'template' => $template,
            'styles' => $styles,
            'settings' => $settings,
        ]);
    }
}
