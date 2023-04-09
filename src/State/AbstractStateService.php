<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Control;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscribe;
use Duyler\EventBus\Enum\ResultStatus;

class AbstractStateService
{
    public function __construct(
        private readonly Control $control
    ) {
    }

    public function addSubscribe(Subscribe $subscribe): void
    {
        $this->control->addSubscribe($subscribe);
    }

    public function rollback(): void
    {
        $this->control->rollback();
    }

    public function addAction(Action $action): void
    {
        $this->control->addAction($action);
    }

    public function getResult(string $actionId): Result
    {
        return $this->control->getResult($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->control->resultIsExists($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->control->actionIsExists($actionId);
    }
}
