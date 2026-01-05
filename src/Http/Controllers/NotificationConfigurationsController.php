<?php

namespace Ingenius\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Ingenius\Core\Actions\CreateNotificationConfigurationAction;
use Ingenius\Core\Actions\CreateOrUpdateNotificationConfigurationAction;
use Ingenius\Core\Actions\PaginateNotificationConfiguration;
use Ingenius\Core\Actions\UpdateNotificationConfigurationAction;
use Ingenius\Core\Http\Requests\CreateNotificationConfigurationRequest;
use Ingenius\Core\Http\Requests\UpdateNotificationConfigurationRequest;
use Ingenius\Core\Models\NotificationConfiguration;
use Ingenius\Core\Services\EventRegistryService;
use Ingenius\Core\Helpers\AuthHelper;

class NotificationConfigurationsController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, PaginateNotificationConfiguration $action): JsonResponse {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'viewAny', NotificationConfiguration::class);

        $paginated = $action->handle($request->all());

        return Response::api(
            message: __('Notification configurations retrieved successfully.'),
            data: $paginated
        );
    }

    public function getAll(Request $request)
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'viewAny', NotificationConfiguration::class);

        $configurations = NotificationConfiguration::all();

        return Response::api(
            message: __('Notification configurations retrieved successfully.'),
            data: $configurations
        );
    }

    public function show(Request $request, NotificationConfiguration $configuration)
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'view', $configuration);

        return Response::api(
            message: __('Notification configuration retrieved successfully.'),
            data: $configuration
        );
    }

    public function createOrEdit(CreateNotificationConfigurationRequest $request, CreateOrUpdateNotificationConfigurationAction $action) {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'create', NotificationConfiguration::class);

        $validated = $request->validated();

        // Create or update the configuration
        $configuration = $action->handle($validated);

        return Response::api(
            message: __('Notification configuration created or updated successfully.'),
            data: $configuration
        );
    }

    /**
     * Store a new notification configuration
     *
     * @param CreateNotificationConfigurationRequest $request
     * @param CreateNotificationConfigurationAction $action
     * @return JsonResponse
     */
    public function store(
        CreateNotificationConfigurationRequest $request,
        CreateNotificationConfigurationAction $action
    ): JsonResponse {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'create', NotificationConfiguration::class);

        $validated = $request->validated();

        // Create the configuration
        $configuration = $action->handle($validated);

        return Response::api(
            message: __('Notification configuration created successfully.'),
            data: $configuration
        );
    }

    public function update(
        UpdateNotificationConfigurationRequest $request,
        NotificationConfiguration $configuration,
        UpdateNotificationConfigurationAction $action
    ) {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'update', $configuration);

        $validated = $request->validated();

        // Update the configuration
        $configuration = $action->handle($configuration, $validated);

        return Response::api(
            message: __('Notification configuration updated successfully.'),
            data: $configuration
        );
    }

    public function toggleEnable(Request $request, NotificationConfiguration $configuration)
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'update', $configuration);

        $configuration->update(['is_enabled' => !$configuration->is_enabled]);

        return Response::api(
            message: __('Notification configuration enabled/disabled successfully.'),
            data: $configuration->refresh()
        );
    }

    /**
     * Get notification configuration by event key and channel
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getByEventAndChannel(Request $request, EventRegistryService $registry): JsonResponse
    {
        $user = AuthHelper::getUser();
        $this->authorizeForUser($user, 'viewAny', NotificationConfiguration::class);

        $validated = $request->validate([
            'event_key' => 'required|string',
            'channel' => 'required|string|in:email,sms',
        ]);

        // Get event information from registry
        $eventData = $registry->getByKey($validated['event_key']);

        // Get configuration if exists
        $configuration = NotificationConfiguration::where('event_key', $validated['event_key'])
            ->where('channel', $validated['channel'])
            ->first();

        $responseData = [
            'event' => [
                'key' => $validated['event_key'],
                'label' => $eventData['label'] ?? $validated['event_key'],
            ],
            'configuration' => $configuration,
        ];

        return Response::api(
            message: __('Event and configuration data retrieved successfully.'),
            data: $responseData
        );
    }

}