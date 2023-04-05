<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscribe;
use Duyler\EventBus\Enum\ResultStatus;

readonly class BusControlService
{
    public function __construct(
        public ResultStatus  $resultStatus,
        public object | null $resultData,
        public string        $actionId,
        private BusControl   $busControl
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

    public function getResult(string $actionId): Result
    {
        return $this->busControl->getResult($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->busControl->resultIsExists($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->busControl->actionIsExists($actionId);
    }

    public function subscribeIsExists(string $actionId): bool
    {
        return $this->busControl->subscribeIsExists($actionId);
    }
}
