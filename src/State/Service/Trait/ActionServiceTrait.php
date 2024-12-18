<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\SharedService;
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
            $this->actionService->addDynamicAction($action);
        }
    }

    public function doAction(Action $action): void
    {
        $this->actionService->doDynamicAction($action);
    }

    public function doExistsAction(string|UnitEnum $actionId): void
    {
        $this->actionService->doExistsAction(IdFormatter::toString($actionId));
    }

    public function actionIsExists(string|UnitEnum $actionId): bool
    {
        return $this->actionService->actionIsExists(IdFormatter::toString($actionId));
    }

    public function removeAction(string|UnitEnum $actionId): void
    {
        $this->actionService->removeAction(IdFormatter::toString($actionId));
    }

    /** @return array<string, Action> */
    public function getByContract(string $contract): array
    {
        return $this->actionService->getByContract($contract);
    }

    public function getById(string|UnitEnum $actionId): Action
    {
        return $this->actionService->getById(IdFormatter::toString($actionId));
    }

    public function addSharedService(SharedService $sharedService): void
    {
        $this->actionService->addSharedService($sharedService);
    }
}
