<?php

declare(strict_types=1);

namespace Duyler\EventBus\State;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\State\Service\StateActionAfterService;
use Duyler\EventBus\State\Service\StateActionBeforeService;
use Duyler\EventBus\State\Service\StateActionThrowingService;
use Override;
use Throwable;

class StateAction implements StateActionInterface
{
    public function __construct(
        private StateHandlerStorage $stateHandlerStorage,
        private ActionContainerCollection $actionContainerCollection,
        private StateContextScope $contextScope,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    #[Override]
    public function before(Action $action): void
    {
        $stateService = new StateActionBeforeService(
            $this->actionContainerCollection->get($action->id),
            $action->id,
            $this->subscriptionService,
        );

        foreach ($this->stateHandlerStorage->getActionBefore() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if (empty($handler->observed($context)) || in_array($action->id, $handler->observed($context))) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function after(Action $action): void
    {
        $stateService = new StateActionAfterService(
            $this->actionContainerCollection->get($action->id),
            $action->id,
            $this->subscriptionService,
        );

        foreach ($this->stateHandlerStorage->getActionAfter() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if (empty($handler->observed($context)) || in_array($action->id, $handler->observed($context))) {
                $handler->handle($stateService, $context);
            }
        }
    }

    #[Override]
    public function throwing(Action $action, Throwable $exception): void
    {
        $stateService = new StateActionThrowingService(
            $this->actionContainerCollection->get($action->id),
            $exception,
            $action->id,
        );

        foreach ($this->stateHandlerStorage->getActionThrowing() as $handler) {
            $context = $this->contextScope->getContext($handler::class);
            if (empty($handler->observed($context)) || in_array($action->id, $handler->observed($context))) {
                $handler->handle($stateService, $context);
            }
        }
    }
}
