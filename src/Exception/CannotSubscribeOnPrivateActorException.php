<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class CannotSubscribeOnPrivateActorException extends Exception
{
    public function __construct(string $actor, string $subscriptionActor)
    {
        parent::__construct(
            sprintf('Actor %s cannot subscribe on private actor %s', $actor, $subscriptionActor),
        );
    }
}
