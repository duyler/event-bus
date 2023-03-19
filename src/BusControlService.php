<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscribe;

class BusControlService
{
    public function __construct(
        public readonly string        $resultStatus,
        public readonly object | null $resultData,
        public readonly string        $actionId,
        public readonly string | null $event,
        private readonly BusControl   $busControl
    ) {
    }

    public function addSubscribe(Subscribe $subscribe): void
    {
        $this->busControl->addSubscribe($subscribe);
    }

    public function rollback(): void
    {
        $this->busControl->rollback();
    }

    public function addAction(Action $action): void
    {
        $this->busControl->addAction($action);
    }

    public function removeAction(): void
    {
        $this->busControl->removeAction();
    }

    public function removeSubscribe(): void
    {
        $this->busControl->removeSubscribe();
    }

    public function getResult(string $actionFullName): Result
    {
        return $this->busControl->getResult($actionFullName);
    }

    public function resultIsExists(string $actionFullName): bool
    {
        return $this->busControl->resultIsExists($actionFullName);
    }

    public function actionIsExists(string $actionFullName): bool
    {
        return $this->busControl->actionIsExists($actionFullName);
    }

    public function subscribeIsExists(string $actionFullName): bool
    {
        return $this->busControl->subscribeIsExists($actionFullName);
    }

    public function taskIsInQueue(string $actionFullName): bool
    {

    }
}
