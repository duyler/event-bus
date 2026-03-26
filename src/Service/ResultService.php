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

    public function getResult(string $actionId, string $scope = 'common'): Result
    {
        if ($this->completeActionStorage->isExists($actionId, $scope)) {
            $completeAction = $this->completeActionStorage->get($actionId, $scope);

            if (false === $completeAction->action->isExternalAccess()) {
                throw new ActionNotAllowExternalAccessException($actionId);
            }

            return $this->completeActionStorage->getResult($actionId, $scope);
        }

        if (false === $this->eventRelationStorage->isExists($actionId, $scope)) {
            throw new ResultNotExistsException($actionId);
        }

        $eventRelation = $this->eventRelationStorage->getLast($actionId, $scope);

        return Result::success(
            $eventRelation->event->data,
        );
    }

    public function resultIsExists(string $actionId, string $scope = 'common'): bool
    {
        return $this->completeActionStorage->isExists($actionId, $scope)
            || $this->eventRelationStorage->isExists($actionId, $scope);
    }
}
