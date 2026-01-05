<?php

namespace Ingenius\Core\Services;

use Illuminate\Support\Facades\Log;
use Ingenius\Core\Models\NotificationTemplate;
use Illuminate\Support\Facades\View;
use Ingenius\Core\Support\Recipient;

class NotificationTemplateRenderer
{
    public function __construct(
        protected EventRegistryService $eventRegistry
    ) {
    }
    /**
     * Render a notification template with slots and data
     *
     * @param NotificationTemplate $template
     * @param array $data Event data and context
     * @param string $eventKey Event key to lookup view name
     * @return array ['subject' => string, 'html' => string]
     */
    public function render(NotificationTemplate $template, array $data, string $eventKey): array
    {
        // Extract scalar values for variable replacement
        $variables = $this->extractScalarVariables($data);

        // Render subject with variables
        $subject = $template->renderSubject($variables);

        // Prepare slots with variable replacement
        $slots = $this->prepareSlots($template, $variables);

        $recipient = $data['recipient'] ?? null;

        // Determine which template view to use
        $viewName = $this->getViewName($eventKey, $recipient);

        // Render the HTML
        $html = View::make($viewName, array_merge($data, [
            'slots' => $slots,
            'subject' => $subject,
        ]))->render();

        return [
            'subject' => $subject,
            'html' => $html,
        ];
    }

    /**
     * Prepare slots by rendering variables
     *
     * @param NotificationTemplate $template
     * @param array $variables
     * @return array
     */
    protected function prepareSlots(NotificationTemplate $template, array $variables): array
    {
        $slots = $template->slots ?? [];
        $rendered = [];

        foreach ($slots as $slotName => $slotContent) {
            $rendered[$slotName] = $template->renderSlot($slotName, $variables);
        }

        return $rendered;
    }

    /**
     * Extract scalar variables from data
     *
     * @param array $data
     * @param string $prefix
     * @return array
     */
    protected function extractScalarVariables(array $data, string $prefix = ''): array
    {
        $variables = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_scalar($value) || $value === null) {
                $variables[$fullKey] = $value;
            } elseif (is_object($value) && method_exists($value, 'toArray')) {
                // Recursively extract from objects that can be converted to arrays
                $variables = array_merge(
                    $variables,
                    $this->extractScalarVariables($value->toArray(), $fullKey)
                );
            } elseif (is_object($value)) {
                // Extract public properties
                $variables = array_merge(
                    $variables,
                    $this->extractScalarVariables(get_object_vars($value), $fullKey)
                );
            } elseif (is_array($value) && $this->isAssociativeArray($value)) {
                // Recursively extract from associative arrays
                $variables = array_merge(
                    $variables,
                    $this->extractScalarVariables($value, $fullKey)
                );
            }
        }

        return $variables;
    }

    /**
     * Check if array is associative
     *
     * @param array $array
     * @return bool
     */
    protected function isAssociativeArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Get view name for event with tenant-specific fallback
     *
     * @param string $eventKey Event key to lookup view name
     * @param Recipient|null $recipient
     * @return string
     */
    protected function getViewName(string $eventKey, Recipient $recipient = null): string
    {
        // Get view name from event registry
        $eventData = $this->eventRegistry->getByKey($eventKey);
        $viewName = $eventData['view_name'] ?? 'general';

        $recipientType = $recipient?->isCustomer() ? 'customer' : 'admin';

        // Check if tenancy is initialized
        if (tenancy()->initialized) {
            $tenant = tenancy()->tenant;
            $tenantId = $tenant->getTenantKey();

            // Try tenant-specific view from storage first
            $tenantStoragePath = storage_path("/email-templates/{$recipientType}/{$viewName}.blade.php");

            if (file_exists($tenantStoragePath)) {
                // Register the tenant's template directory with Laravel's view finder
                $tenantViewPath = storage_path("/email-templates");

                // Add the path to view finder
                View::addLocation($tenantViewPath);

                // Return the view name relative to the tenant's template directory
                return "{$recipientType}.{$viewName}";
            }
        }

        // Fall back to default package view
        return "core::emails.templates.{$recipientType}.{$viewName}";
    }

    /**
     * Preview a template with sample data
     *
     * @param NotificationTemplate $template
     * @param string $eventKey Event key to lookup view name
     * @param array $sampleData
     * @return array
     */
    public function preview(NotificationTemplate $template, string $eventKey, array $sampleData = []): array
    {
        // Get view name from event registry for sample data generation
        $eventData = $this->eventRegistry->getByKey($eventKey);
        $viewName = $eventData['view_name'] ?? 'general';

        // Merge with default sample data based on view name pattern
        $data = array_merge($this->getDefaultSampleDataForView($viewName), $sampleData);

        return $this->render($template, $data, $eventKey);
    }

    /**
     * Get default sample data for preview based on view name
     *
     * @param string $viewName
     * @return array
     */
    protected function getDefaultSampleDataForView(string $viewName): array
    {
        // Determine sample data type from view name pattern
        // e.g., 'order-created' or 'order-cancelled' -> 'order'
        $dataType = explode('-', $viewName)[0] ?? 'general';

        return match ($dataType) {
            'order' => [
                'order' => (object) [
                    'order_number' => 'ORD-12345',
                    'created_at' => now(),
                    'status' => 'processing',
                    'total' => 99.99,
                ],
                'customer' => (object) [
                    'name' => 'Juan Pérez',
                    'email' => 'juan@example.com',
                ],
                'items' => [
                    ['name' => 'Producto 1', 'quantity' => 2, 'price' => 29.99],
                    ['name' => 'Producto 2', 'quantity' => 1, 'price' => 40.01],
                ],
            ],
            'user' => [
                'user' => (object) [
                    'name' => 'María García',
                    'email' => 'maria@example.com',
                    'created_at' => now(),
                ],
            ],
            'payment' => [
                'payment' => (object) [
                    'transaction_id' => 'TXN-98765',
                    'amount' => 149.99,
                    'payment_method' => 'Tarjeta de Crédito',
                    'status' => 'approved',
                    'created_at' => now(),
                ],
            ],
            default => [
                'data' => [
                    'message' => 'Esta es una notificación de ejemplo',
                    'timestamp' => now()->toDateTimeString(),
                ],
            ],
        };
    }
}
