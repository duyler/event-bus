<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\State\Service\StateActionAfterService;
use Duyler\EventBus\State\Service\StateActionBeforeService;
use Duyler\EventBus\State\Service\StateActionThrowingService;
use Throwable;

class StateAction implements StateActionInterface
{
    public function __construct(
        private StateHandlerStorage $stateHandlerStorage,
        private ActionContainerCollection $actionContainerCollection,
    ) {
    }

    public function before(Action $action): void
    {
        $stateService = new StateActionBeforeService(
            $this->actionContainerCollection->get($action->id),
            $action->id,
        );

        foreach ($this->stateHandlerStorage->getActionBefore() as $handler) {
            if (empty($handler->observed()) || in_array($action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }

    public function after(Action $action): void
    {
        $stateService = new StateActionAfterService(
            $this->actionContainerCollection->get($action->id),
            $action->id,
        );

        foreach ($this->stateHandlerStorage->getActionAfter() as $handler) {
            if (empty($handler->observed()) || in_array($action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }

    public function throwing(Action $action, Throwable $exception): void
    {
        $stateService = new StateActionThrowingService(
            $this->actionContainerCollection->get($action->id),
            $exception,
            $action->id,
        );

        foreach ($this->stateHandlerStorage->getActionThrowing() as $handler) {
            if (empty($handler->observed()) || in_array($action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }
}
