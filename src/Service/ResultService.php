<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Dto\Result;
use RuntimeException;

class ResultService
{
    public function __construct(private EventCollection $eventCollection)
    {
    }

    public function getResult(string $actionId): Result
    {
        $event = $this->eventCollection->get($actionId);

        if ($event->action->externalAccess === false) {
            throw new RuntimeException('Action ' . $actionId . ' does not allow external access');
        }

        return $this->eventCollection->getResult($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->eventCollection->isExists($actionId);
    }
}
