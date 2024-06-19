<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State\Service\Trait;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Formatter\ActionIdFormatter;
use Duyler\ActionBus\Service\ActionService;
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
        $this->actionService->doExistsAction(ActionIdFormatter::toString($actionId));
    }

    public function actionIsExists(string|UnitEnum $actionId): bool
    {
        return $this->actionService->actionIsExists(ActionIdFormatter::toString($actionId));
    }

    public function removeAction(string|UnitEnum $actionId): void
    {
        $this->actionService->removeAction(ActionIdFormatter::toString($actionId));
    }

    /** @return array<string, Action> */
    public function getByContract(string $contract): array
    {
        return $this->actionService->getByContract($contract);
    }

    public function getById(string|UnitEnum $actionId): Action
    {
        return $this->actionService->getById(ActionIdFormatter::toString($actionId));
    }

    /** @param array<string, string> $bind */
    public function addSharedService(object $service, array $bind = []): void
    {
        $this->actionService->addSharedService($service, $bind);
    }
}
