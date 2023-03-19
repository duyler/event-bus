<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscribe;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\SubscribeStorage;
use Duyler\EventBus\Storage\TaskStorage;

class BusControl
{
    public function __construct(
        private readonly SubscribeStorage $subscribeStorage,
        private readonly BusValidator     $busValidator,
        private readonly Rollback         $rollback,
        private readonly ActionStorage    $actionStorage,
        private readonly TaskStorage      $taskStorage,
    ) {
    }

    public function addSubscribe(Subscribe $subscribe): void
    {
        $this->subscribeStorage->save($subscribe);
        $this->busValidator->validate();
    }

    public function rollback(): void
    {
        $this->rollback->run();
    }

    public function addAction(Action $action): void
    {
        $this->actionStorage->save($action);
        $this->busValidator->validate();
    }

    public function getResult(string $actionId): Result
    {
        return $this->taskStorage->getResult($actionId);
    }

    public function removeAction(string $actionId): void
    {
        $this->actionStorage->remove($actionId);
    }

    public function removeSubscribe(string $actionId): void
    {
        $this->subscribeStorage->remove($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->taskStorage->isExists($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->actionStorage->isExists($actionId);
    }

    public function subscribeIsExists(string $actionId): bool
    {
        return $this->subscribeStorage->isExists($actionId);
    }
}
