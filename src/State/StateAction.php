<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Contract\State\StateActionAfterHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionBeforeHandlerInterface;
use Duyler\EventBus\Contract\State\StateActionThrowingHandlerInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Enum\StateType;
use Duyler\EventBus\State\Service\StateActionAfterService;
use Duyler\EventBus\State\Service\StateActionBeforeService;
use Duyler\EventBus\State\Service\StateActionThrowingService;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Throwable;

readonly class StateAction
{
    public function __construct(
        private StateHandlerProvider   $stateHandlerProvider,
        private ActionContainerCollection $actionContainerCollection,
    ) {
    }

    public function before(Action $action): void
    {
        $stateService = new StateActionBeforeService(
            $this->actionContainerCollection->get($action->id),
            $action->id,
        );

        /** @var StateActionBeforeHandlerInterface $handler */
        foreach ($this->stateHandlerProvider->getHandlers(StateType::ActionBefore) as $handler) {
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

        /** @var StateActionAfterHandlerInterface $handler */
        foreach ($this->stateHandlerProvider->getHandlers(StateType::ActionAfter) as $handler) {
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

        /** @var StateActionThrowingHandlerInterface $handler */
        foreach ($this->stateHandlerProvider->getHandlers(StateType::ActionThrowing) as $handler) {
            if (empty($handler->observed()) || in_array($action->id, $handler->observed())) {
                $handler->handle($stateService);
            }
        }
    }
}
