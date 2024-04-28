<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Exception;

use Duyler\ActionBus\Dto\Subscription;
use Exception;

class SubscriptionAlreadyDefinedException extends Exception
{
    public function __construct(Subscription $subscription)
    {
        parent::__construct(
            sprintf(
                'Subscription with action id %s, status %s, and subject id %s already defined',
                $subscription->actionId,
                $subscription->status->value,
                $subscription->subjectId,
            ),
        );
    }
}
