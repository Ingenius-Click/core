<?php

namespace Ingenius\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'event_type',
        'template_key',
        'subject',
        'slots',
        'available_variables',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'slots' => 'array',
        'available_variables' => 'array',
        'is_system' => 'boolean',
    ];

    /**
     * Get configurations using this template
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(NotificationConfiguration::class, 'template_key', 'template_key');
    }

    /**
     * Get default slots for a template
     *
     * @return array
     */
    public function getDefaultSlots(): array
    {
        return [
            'header' => $this->slots['header'] ?? '',
            'main_message' => $this->slots['main_message'] ?? '',
            'footer' => $this->slots['footer'] ?? '',
        ];
    }

    /**
     * Render subject with variables
     *
     * @param array $variables
     * @return string
     */
    public function renderSubject(array $variables): string
    {
        return $this->replaceVariables($this->subject, $variables);
    }

    /**
     * Render a slot with variables
     *
     * @param string $slotName
     * @param array $variables
     * @return string
     */
    public function renderSlot(string $slotName, array $variables): string
    {
        $content = $this->slots[$slotName] ?? '';
        return $this->replaceVariables($content, $variables);
    }

    /**
     * Replace variables in content
     *
     * @param string $content
     * @param array $variables
     * @return string
     */
    protected function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            // Only replace if value is scalar
            if (is_scalar($value)) {
                $content = str_replace("{{{$key}}}", $value, $content);
            }
        }

        return $content;
    }

    /**
     * Validate that all used variables are available
     *
     * @param string $content
     * @return array Missing variables
     */
    public function validateVariables(string $content): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $content, $matches);
        $usedVariables = $matches[1] ?? [];
        $availableVariables = $this->available_variables ?? [];

        return array_diff($usedVariables, $availableVariables);
    }
}
