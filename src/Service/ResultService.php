<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Exception\ActorNotAllowExternalAccessException;
use Duyler\EventBus\Exception\ResultNotExistsException;
use Duyler\EventBus\Storage\CompleteActorStorage;
use Duyler\EventBus\Storage\EventRelationStorage;

class ResultService
{
    public function __construct(
        private readonly CompleteActorStorage $completeActorStorage,
        private readonly EventRelationStorage $eventRelationStorage,
    ) {}

    public function getResult(string $actorId, string $scope = 'common'): Result
    {
        if ($this->completeActorStorage->isExists($actorId, $scope)) {
            $completeActor = $this->completeActorStorage->get($actorId, $scope);

            if (false === $completeActor->actor->isExternalAccess()) {
                throw new ActorNotAllowExternalAccessException($actorId);
            }

            return $this->completeActorStorage->getResult($actorId, $scope);
        }

        if (false === $this->eventRelationStorage->isExists($actorId, $scope)) {
            throw new ResultNotExistsException($actorId);
        }

        $eventRelation = $this->eventRelationStorage->getLast($actorId, $scope);

        return Result::success(
            $eventRelation->event->data,
        );
    }

    public function resultIsExists(string $actorId, string $scope = 'common'): bool
    {
        return $this->completeActorStorage->isExists($actorId, $scope)
            || $this->eventRelationStorage->isExists($actorId, $scope);
    }
}
