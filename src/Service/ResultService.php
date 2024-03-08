<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
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

        if (false === $this->triggerRelationCollection->isExists($actionId)) {
            throw new ResultNotExistsException($actionId);
        }

        $triggerRelation = $this->triggerRelationCollection->getLast($actionId);

        return new Result(
            ResultStatus::Success,
            $triggerRelation->trigger->data,
        );
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->completeActionCollection->isExists($actionId)
            || $this->triggerRelationCollection->isExists($actionId);
    }
}
