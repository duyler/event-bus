<?php

declare(strict_types=1);

namespace Duyler\ActionBus\State;

use Duyler\ActionBus\Build\Action;
use Duyler\ActionBus\Contract\State\StateHandlerObservedInterface;
use Duyler\ActionBus\Contract\StateActionInterface;
use Duyler\ActionBus\Formatter\ActionIdFormatter;
use Duyler\ActionBus\State\Service\StateActionAfterService;
use Duyler\ActionBus\State\Service\StateActionBeforeService;
use Duyler\ActionBus\State\Service\StateActionThrowingService;
use Duyler\ActionBus\Storage\ActionContainerStorage;
use Override;
use Throwable;
use UnitEnum;

class StateAction implements StateActionInterface
{
    public function __construct(
        private StateHandlerStorage $stateHandlerStorage,
        private ActionContainerStorage $actionContainerStorage,
        private StateContextScope $contextScope,
    ) {}

    #[Override]
    public function before(Action $action, object|null $argument): void
    {
        $stateService = new StateActionBeforeService(
            $this->actionContainerStorage->get($action->id),
            $action,
            $argument,
        );

        foreach ($this->stateHandlerStorage->getActionBefore() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $action, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function after(Action $action, mixed $resultData): void
    {
        $stateService = new StateActionAfterService(
            $this->actionContainerStorage->get($action->id),
            $action,
            $resultData,
        );

        foreach ($this->stateHandlerStorage->getActionAfter() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $action, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function throwing(Action $action, Throwable $exception): void
    {
        $stateService = new StateActionThrowingService(
            $this->actionContainerStorage->get($action->id),
            $exception,
            $action,
        );

        foreach ($this->stateHandlerStorage->getActionThrowing() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if ($this->isObserved($handler, $action, $context)) {
                $handler->handle($stateService, $context);
            }
        }
    }

    private function isObserved(StateHandlerObservedInterface $handler, Action $action, StateContext $context): bool
    {
        $observed = $handler->observed($context);
        /** @var string|UnitEnum $actionId */
        foreach ($observed as $actionId) {
            $observed[] = ActionIdFormatter::toString($actionId);
        }
        return count($observed) === 0 || in_array($action->id, $observed);
    }
}
