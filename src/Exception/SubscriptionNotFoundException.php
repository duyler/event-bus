<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Exception;

use Duyler\ActionBus\Dto\Subscription;
use Exception;

class SubscriptionNotFoundException extends Exception
{
    public function __construct(Subscription $subscription)
    {
        parent::__construct('Subscription not found: ' . $subscription->actionId . '@' . $subscription->subjectId);
    }
}
