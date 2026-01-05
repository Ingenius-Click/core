<?php

namespace Ingenius\Core\Support;

use Ingenius\Core\Enums\RecipientType;

class Recipient
{
    protected string $name;
    protected string $email;
    protected bool $isCustomer;
    protected ?string $phone;
    protected array $data;

    public function __construct(
        ?string $name,
        string $email,
        bool $isCustomer,
        ?string $phone = null,
        array $data = []
    ) {
        $this->name = $name ?? ($isCustomer ? __('Valued Customer') : __('Administrator'));
        $this->email = $email;
        $this->isCustomer = $isCustomer;
        $this->phone = $phone;
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isCustomer(): bool
    {
        return $this->isCustomer;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get RecipientType enum
     *
     * @return RecipientType
     */
    public function getType(): RecipientType
    {
        return $this->isCustomer ? RecipientType::CUSTOMER : RecipientType::ADMIN;
    }

    /**
     * Get recipient value for a specific channel
     *
     * @param string $channel
     * @return string|null
     */
    public function getRecipientForChannel(string $channel): ?string
    {
        return match ($channel) {
            'email' => $this->email,
            'sms' => $this->phone,
            default => null,
        };
    }

    /**
     * Check if recipient has value for a specific channel
     *
     * @param string $channel
     * @return bool
     */
    public function hasChannelRecipient(string $channel): bool
    {
        return !empty($this->getRecipientForChannel($channel));
    }
}
    