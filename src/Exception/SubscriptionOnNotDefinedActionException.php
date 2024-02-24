<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Duyler\EventBus\Dto\Subscription;
use Exception;

class SubscriptionOnNotDefinedActionException extends Exception
{
    public function __construct(Subscription $subscription)
    {
        parent::__construct('Action ' . $subscription->actionId . ' not defined in the bus');
    }
}
