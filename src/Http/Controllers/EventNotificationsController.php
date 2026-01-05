<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Ingenius\Core\Services\EventRegistryService;

class EventNotificationsController extends Controller {
    use AuthorizesRequests;

    public function __construct(
        protected EventRegistryService $eventRegistry
    ) {}

    public function registeredEvents(): JsonResponse {

        $events = collect($this->eventRegistry->all())->map(fn($data) => [
            'key' => $data['key'],
            'label' => $data['label'],
        ])->values();

        return Response::api(
            message: __('Registered notifiable events retrieved successfully.'),
            data: $events
        );
    }
}