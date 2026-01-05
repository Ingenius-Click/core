<?php

namespace Ingenius\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Ingenius\Core\Services\EventRegistryService;
use Ingenius\Core\Services\ChannelRegistryService;
use Ingenius\Core\Models\NotificationTemplate;
use Ingenius\Core\Models\NotificationConfiguration;

class UpdateNotificationConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $channelRegistry = app(ChannelRegistryService::class);

        // Get all registered channels
        $registeredChannels = array_keys($channelRegistry->all());

        // Get the current configuration being updated
        $configurationId = $this->route('notification_configuration') ?? $this->route('id');

        return [
            'event_key' => [
                'sometimes',
                'string',
                function ($attribute, $value, $fail) {
                    $eventRegistry = app(EventRegistryService::class);
                    if (!$eventRegistry->hasKey($value)) {
                        $fail("The selected event is not registered in the system.");
                    }
                },
                // Unique combination of event_key + channel (excluding current record)
                function ($attribute, $value, $fail) use ($configurationId) {
                    $query = NotificationConfiguration::where('event_key', $value)
                        ->where('channel', $this->input('channel'));

                    if ($configurationId) {
                        $query->where('id', '!=', $configurationId);
                    }

                    if ($query->exists()) {
                        $fail('A notification configuration already exists for this event and channel combination.');
                    }
                },
            ],
            'channel' => [
                'sometimes',
                'string',
                Rule::in($registeredChannels),
            ],
            'is_enabled' => [
                'sometimes',
                'boolean',
            ],
            'notify_customer' => [
                'sometimes',
                'boolean',
            ],
            'admin_recipients' => [
                'nullable',
                'array',
            ],
            'admin_recipients.*' => [
                'required_with:admin_recipients',
                'string',
                // Validate format based on channel
                function ($attribute, $value, $fail) {
                    $channel = $this->input('channel');

                    if ($channel === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail("The {$attribute} must be a valid email address.");
                    }

                    if ($channel === 'sms' && !preg_match('/^\+?[1-9]\d{1,14}$/', $value)) {
                        $fail("The {$attribute} must be a valid phone number.");
                    }
                },
            ],
            'template_key' => [
                'nullable',
                'string',
                Rule::exists('notification_templates', 'template_key'),
                // Validate template matches event key
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        return;
                    }

                    $template = NotificationTemplate::where('template_key', $value)->first();
                    if (!$template) {
                        return;
                    }

                    // Get event key (use existing if not provided in update)
                    $eventKey = $this->input('event_key');

                    // If event_key is not being updated, get it from the current configuration
                    if (!$eventKey) {
                        $configurationId = $this->route('notification_configuration') ?? $this->route('id');
                        $configuration = NotificationConfiguration::find($configurationId);
                        $eventKey = $configuration?->event_key;
                    }

                    if (!$eventKey) {
                        return;
                    }

                    // Validate that template_key matches event_key
                    // This ensures the template is designed for this specific event
                    if ($template->template_key !== $eventKey) {
                        $fail("The selected template is not compatible with this event. The template must be for '{$eventKey}'.");
                    }
                },
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'event_key.required' => 'Please select an event.',
            'channel.required' => 'Please select a notification channel.',
            'channel.in' => 'The selected channel is not available.',
            'admin_recipients.array' => 'Admin recipients must be a list.',
            'admin_recipients.*.required_with' => 'Each admin recipient must have a value.',
            'template_key.exists' => 'The selected template does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'event_key' => 'event',
            'admin_recipients.*' => 'admin recipient',
            'template_key' => 'notification template',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean admin_recipients array (remove empty values)
        if ($this->has('admin_recipients') && is_array($this->admin_recipients)) {
            $this->merge([
                'admin_recipients' => array_values(array_filter($this->admin_recipients)),
            ]);
        }
    }

    /**
     * Get validated data with event_name resolved from event_key
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Resolve event_name from event_key if event_key is being updated
        if (isset($validated['event_key'])) {
            $eventRegistry = app(EventRegistryService::class);
            $eventName = $eventRegistry->getByKey($validated['event_key'])['label'] ?? 'Unknown Event';

            // Add event_name to validated data
            $validated['event_name'] = $eventName;
        }

        return $validated;
    }
}
