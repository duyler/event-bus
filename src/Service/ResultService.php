<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Exception\ActionNotAllowExternalAccessException;
use Duyler\EventBus\Exception\ResultNotExistsException;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;

class ResultService
{
    public function __construct(
        private readonly CompleteActionStorage $completeActionStorage,
        private readonly EventRelationStorage $eventRelationStorage,
    ) {}

    public function getResult(string $actionId): Result
    {
        if ($this->completeActionStorage->isExists($actionId)) {
            $completeAction = $this->completeActionStorage->get($actionId);

            if (false === $completeAction->action->isExternalAccess()) {
                throw new ActionNotAllowExternalAccessException($actionId);
            }

            return $this->completeActionStorage->getResult($actionId);
        }

        if (false === $this->eventRelationStorage->isExists($actionId)) {
            throw new ResultNotExistsException($actionId);
        }

        $eventRelation = $this->eventRelationStorage->getLast($actionId);

        return Result::success(
            $eventRelation->event->data,
        );
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->completeActionStorage->isExists($actionId)
            || $this->eventRelationStorage->isExists($actionId);
    }
}
