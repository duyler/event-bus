<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Result;
use RuntimeException;

class ResultService
{
    public function __construct(
        private EventCollection $eventCollection,
        private TriggerRelationCollection $triggerRelationCollection,
    ) {}

    public function getResult(string $actionId): ?Result
    {
        if ($this->eventCollection->isExists($actionId)) {
            $event = $this->eventCollection->get($actionId);

            if (false === $event->action->externalAccess) {
                throw new RuntimeException('Action ' . $actionId . ' does not allow external access');
            }

            return $this->eventCollection->getResult($actionId);
        }

        if ($this->triggerRelationCollection->isExists($actionId)) {
            $triggerRelation = $this->triggerRelationCollection->getLast($actionId);
            return new Result(
                $triggerRelation->trigger->status,
                $triggerRelation->trigger->data,
            );
        }

        return null;
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->eventCollection->isExists($actionId)
            || $this->triggerRelationCollection->isExists($actionId);
    }
}
