<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class CannotSubscribeOnRequiredActorException extends Exception
{
    public function __construct(string $actor, string $subscriptionActor)
    {
        parent::__construct(
            sprintf('Actor %s cannot subscribe and require at the same time on actor %s', $actor, $subscriptionActor),
        );
    }
}
