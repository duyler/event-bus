<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Duyler\EventBus\Enum\SubscriptionType;
use Exception;

class InvalidSubscriptionCombinationException extends Exception
{
    /**
     * @param array<SubscriptionType> $subscriptionTypes
     */
    public function __construct(array $subscriptionTypes)
    {
        $typeNames = array_map(
            static fn(SubscriptionType $type): string => $type->name,
            $subscriptionTypes,
        );

        parent::__construct(
            'Action cannot have multiple subscription types: ' . implode(', ', $typeNames),
        );
    }
}
