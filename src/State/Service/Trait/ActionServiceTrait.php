<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ActionService;
use UnitEnum;

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

    public function doExistsAction(string|UnitEnum $actionId): void
    {
        $this->actionService->doExistsAction(IdFormatter::format($actionId));
    }

    public function actionIsExists(string|UnitEnum $actionId): bool
    {
        return $this->actionService->actionIsExists(IdFormatter::format($actionId));
    }

    public function removeAction(string|UnitEnum $actionId): void
    {
        $this->actionService->removeAction(IdFormatter::format($actionId));
    }

    /** @return array<string, Action> */
    public function getByContract(string $contract): array
    {
        return $this->actionService->getByContract($contract);
    }

    public function getById(string|UnitEnum $actionId): Action
    {
        return $this->actionService->getById(IdFormatter::format($actionId));
    }

    /** @param array<string, string> $bind */
    public function addSharedService(object $service, array $bind = []): void
    {
        $this->actionService->addSharedService($service, $bind);
    }
}
