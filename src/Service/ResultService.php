<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Service;

use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Storage\TriggerRelationStorage;
use Duyler\ActionBus\Dto\Result;
use Duyler\ActionBus\Enum\ResultStatus;
use Duyler\ActionBus\Exception\ActionNotAllowExternalAccessException;
use Duyler\ActionBus\Exception\ResultNotExistsException;

class ResultService
{
    public function __construct(
        private CompleteActionStorage $completeActionStorage,
        private TriggerRelationStorage $triggerRelationStorage,
    ) {}

    public function getResult(string $actionId): Result
    {
        if ($this->completeActionStorage->isExists($actionId)) {
            $completeAction = $this->completeActionStorage->get($actionId);

            if (false === $completeAction->action->externalAccess) {
                throw new ActionNotAllowExternalAccessException($actionId);
            }

            return $this->completeActionStorage->getResult($actionId);
        }

        if (false === $this->triggerRelationStorage->isExists($actionId)) {
            throw new ResultNotExistsException($actionId);
        }

        $triggerRelation = $this->triggerRelationStorage->getLast($actionId);

        return new Result(
            ResultStatus::Success,
            $triggerRelation->trigger->data,
        );
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->completeActionStorage->isExists($actionId)
            || $this->triggerRelationStorage->isExists($actionId);
    }
}
