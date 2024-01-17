<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Service\ActionService;

/**
 * @property ActionService $actionService
 */
trait ActionServiceTrait
{
    public function addAction(Action $action): void
    {
        if (false === $this->actionService->actionIsExists($action->id)) {
            $this->actionService->addAction($action);
        }
    }

    public function doAction(Action $action): void
    {
        $this->actionService->doAction($action);
    }

    public function doExistsAction(string $actionId): void
    {
        $this->actionService->doExistsAction($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->actionService->actionIsExists($actionId);
    }

    public function removeAction(string $actionId): void
    {
        $this->actionService->removeAction($actionId);
    }
}
