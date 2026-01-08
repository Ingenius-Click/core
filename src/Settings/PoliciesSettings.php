<?php

namespace Ingenius\Core\Settings;

class PoliciesSettings extends Settings
{
    /**
     * Return policy content (rich text)
     *
     * @var string
     */
    public string $return_policy = '';

    /**
     * Shipping policy content (rich text)
     *
     * @var string
     */
    public string $shipping_policy = '';

    /**
     * Warranty policy content (rich text)
     *
     * @var string
     */
    public string $warranty_policy = '';

    /**
     * Get the group name for these settings.
     *
     * @return string
     */
    public static function group(): string
    {
        return 'policies';
    }

    /**
     * Get the properties that should be encrypted.
     *
     * @return array
     */
    public static function encrypted(): array
    {
        return [];
    }
}
