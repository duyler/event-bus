<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Duyler\EventBus\Build\Subscription;
use Exception;

class SubscriptionNotFoundException extends Exception
{
    public function __construct(Subscription $subscription)
    {
        parent::__construct('Subscription not found: ' . $subscription->actionId . '@' . $subscription->subjectId);
    }
}
