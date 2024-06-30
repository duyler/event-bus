<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Service;

use Duyler\ActionBus\Storage\CompleteActionStorage;
use Duyler\ActionBus\Storage\EventRelationStorage;
use Duyler\ActionBus\Dto\Result;
use Duyler\ActionBus\Enum\ResultStatus;
use Duyler\ActionBus\Exception\ActionNotAllowExternalAccessException;
use Duyler\ActionBus\Exception\ResultNotExistsException;

class ResultService
{
    public function __construct(
        private CompleteActionStorage $completeActionStorage,
        private EventRelationStorage $eventRelationStorage,
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

        if (false === $this->eventRelationStorage->isExists($actionId)) {
            throw new ResultNotExistsException($actionId);
        }

        $eventRelation = $this->eventRelationStorage->getLast($actionId);

        return new Result(
            ResultStatus::Success,
            $eventRelation->event->data,
        );
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->completeActionStorage->isExists($actionId)
            || $this->eventRelationStorage->isExists($actionId);
    }
}
