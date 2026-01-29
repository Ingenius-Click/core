<?php

namespace Ingenius\Core\Notifications\Resolvers;

use Ingenius\Core\Interfaces\RecipientResolverInterface;

class ContactRecipientResolver implements RecipientResolverInterface
{
    public function resolve($event): array
    {
        return [];
    }

    public function getNotificationData($event): array
    {
        return [
            'email' => $event->getEmail(),
            'name' => $event->getName(),
            'contact_message' => $event->getMessage(),
        ];
    }
}