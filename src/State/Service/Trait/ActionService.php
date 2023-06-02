<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\BusService;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscription;

/**
 * @property BusService $busService
 */
trait ActionService
{
    public function addAction(Action $action): void
    {
        if ($this->busService->actionIsExists($action->id) === false) {
            $this->busService->addAction($action);
        }
    }

    public function doAction(Action $action): void
    {
        $this->busService->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        $this->busService->doExistsAction($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->busService->actionIsExists($actionId);
    }
}
