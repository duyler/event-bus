<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Exception\ActionNotAllowExternalAccessException;
use Duyler\EventBus\Exception\ResultNotExistsException;

class ResultService
{
    public function __construct(
        private CompleteActionCollection $completeActionCollection,
        private TriggerRelationCollection $triggerRelationCollection,
    ) {}

    public function getResult(string $actionId): Result
    {
        if ($this->completeActionCollection->isExists($actionId)) {
            $completeAction = $this->completeActionCollection->get($actionId);

            if (false === $completeAction->action->externalAccess) {
                throw new ActionNotAllowExternalAccessException($actionId);
            }

            return $this->completeActionCollection->getResult($actionId);
        }

        if ($this->triggerRelationCollection->isExists($actionId) === false) {
            throw new ResultNotExistsException($actionId);
        }

        $triggerRelation = $this->triggerRelationCollection->getLast($actionId);
        return new Result(
            $triggerRelation->trigger->status,
            $triggerRelation->trigger->data,
        );
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->completeActionCollection->isExists($actionId)
            || $this->triggerRelationCollection->isExists($actionId);
    }
}
