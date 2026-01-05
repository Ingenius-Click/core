<?php

namespace Ingenius\Core\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Ingenius\Core\Models\NotificationConfiguration;

class PaginateNotificationConfiguration {

    public function handle(array $filters = []): LengthAwarePaginator {

        $query = NotificationConfiguration::query();

        return table_handler_paginate($filters, $query);
    }

}