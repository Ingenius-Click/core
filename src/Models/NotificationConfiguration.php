<?php

namespace Ingenius\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ingenius\Core\Services\EventRegistryService;

class NotificationConfiguration extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_key',
        'event_name',
        'channel',
        'is_enabled',
        'notify_customer',
        'admin_recipients',
        'template_key',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'notify_customer' => 'boolean',
        'admin_recipients' => 'array',
        'metadata' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'event_label',
    ];

    /**
     * Get logs for this configuration
     */
    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Get the template for this configuration
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_key', 'template_key');
    }

    /**
     * Get the template or fall back to default for event
     */
    public function getTemplateOrDefault(): ?NotificationTemplate
    {
        // If specific template is set, use it
        if ($this->template_key) {
            return $this->template;
        }

        return NotificationTemplate::where('template_key', $this->event_key)
            ->where('is_system', true)
            ->first();
    }

    /**
     * Get the translated event label from EventRegistry
     */
    public function getEventLabelAttribute(): ?string
    {
        $registry = app(EventRegistryService::class);
        $event = $registry->getByKey($this->event_key);

        return $event['label'] ?? $this->event_name ?? $this->event_key;
    }
}
