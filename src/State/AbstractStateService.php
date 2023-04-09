<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Control;
use Duyler\EventBus\Dto\Result;

class AbstractStateService
{
    public function __construct(
        protected readonly Control $control
    ) {
    }

    public function rollback(): void
    {
        $this->control->rollback();
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
