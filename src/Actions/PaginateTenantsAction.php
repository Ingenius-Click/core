<?php

namespace Ingenius\Core\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Ingenius\Core\Models\Tenant;

class PaginateTenantsAction
{
    public function handle(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $tenants = Tenant::query();

        return table_handler_paginate($filters, $tenants);
    }
}
