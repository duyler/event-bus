<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Bus\Action;
use Duyler\EventBus\Contract\State\StateHandlerObservedInterface;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\State\Service\StateActionAfterService;
use Duyler\EventBus\State\Service\StateActionBeforeService;
use Duyler\EventBus\State\Service\StateActionThrowingService;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Override;
use Throwable;

class StateAction implements StateActionInterface
{
    public function __construct(
        private readonly StateHandlerStorage $stateHandlerStorage,
        private readonly ActionContainerStorage $actionContainerStorage,
        private readonly StateContextScope $contextScope,
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
        return count($observed) === 0 || in_array($action->externalId, $observed);
    }
}
