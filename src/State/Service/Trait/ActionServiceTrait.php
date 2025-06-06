<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service\Trait;

use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Bus\Action as InternalAction;
use Duyler\EventBus\Build\Action as ExternalAction;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ActionService;
use UnitEnum;

/**
 * @property ActionService $actionService
 */
trait ActionServiceTrait
{
    public function addAction(ExternalAction $action): void
    {
        $internalAction = InternalAction::fromExternal($action);
        if (false === $this->actionService->actionIsExists($internalAction->id)) {
            $this->actionService->addDynamicAction($internalAction);
        }
    }

    public function doAction(ExternalAction $action): void
    {
        $this->actionService->doDynamicAction(InternalAction::fromExternal($action));
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

    /** @return array<string, ExternalAction> */
    public function getByContract(string $contract): array
    {
        $externalByType = [];

        foreach ($this->actionService->getByContract($contract) as $action) {
            $externalByType[$action->id] = ExternalAction::fromInternal($action);
        }

        return $externalByType;
    }

    public function getById(string|UnitEnum $actionId): ExternalAction
    {
        $internal = $this->actionService->getById(IdFormatter::toString($actionId));
        return ExternalAction::fromInternal($internal);
    }

    public function addSharedService(SharedService $sharedService): void
    {
        $this->actionService->addSharedService($sharedService);
    }
}
