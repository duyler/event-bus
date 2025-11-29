<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Internal\Event\ActionThrownExceptionEvent;
use Duyler\EventBus\Storage\ActionContainerStorage;
use Throwable;

class StateActionThrowingEventListener
{
    public function __construct(
        private readonly StateActionInterface $stateAction,
        private readonly BusConfig $config,
        private readonly ActionContainerStorage $actionContainerStorage,
        private readonly State $state,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {}

    public function __invoke(ActionThrownExceptionEvent $event): void
    {
        try {
            $this->state->setErrorAction($event->action->getId());
            $this->stateAction->throwing($event->action, $event->exception);

            if ($this->config->continueAfterException) {
                $actionContainer = $this->actionContainerStorage->get($event->action->getId());
                $actionContainer->finalize();
            } else {
                throw $event->exception;
            }
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
