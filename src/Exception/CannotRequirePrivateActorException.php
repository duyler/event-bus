<?php

declare(strict_types=1);

namespace Duyler\EventBus\Exception;

use Exception;

class CannotRequirePrivateActorException extends Exception
{
    public function __construct(string $actor, string $requiredActor)
    {
        parent::__construct(
            sprintf('Actor %s cannot require private actor %s', $actor, $requiredActor),
        );
    }
}
