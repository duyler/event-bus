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

    public function getResult(string $actionFullName): Result
    {
        return $this->taskStorage->getResult($actionFullName);
    }

    public function removeAction(string $actionFullName): void
    {
        $this->actionStorage->remove($actionFullName);
    }

    public function removeSubscribe(string $actionFullName): void
    {
        $this->subscribeStorage->remove($actionFullName);
    }

    public function resultIsExists(string $actionFullName): bool
    {
        return $this->taskStorage->isExists($actionFullName);
    }

    public function actionIsExists(string $actionFullName): bool
    {
        return $this->actionStorage->isExists($actionFullName);
    }

    public function subscribeIsExists(string $actionFullName): bool
    {
        return $this->subscribeStorage->isExists($actionFullName);
    }
}
