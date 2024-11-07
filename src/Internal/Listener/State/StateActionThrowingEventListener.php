<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Internal\Event\ActionThrownExceptionEvent;
use Duyler\EventBus\Storage\ActionContainerStorage;

class StateActionThrowingEventListener
{
    public function __construct(
        private StateActionInterface $stateAction,
        private BusConfig $config,
        private ActionContainerStorage $actionContainerStorage,
        private State $state,
    ) {}

    public function __invoke(ActionThrownExceptionEvent $event): void
    {
        $this->state->setErrorAction($event->action->id);
        $this->stateAction->throwing($event->action, $event->exception);

        if ($this->config->continueAfterException) {
            $actionContainer = $this->actionContainerStorage->get($event->action->id);
            $actionContainer->finalize();
        } else {
            throw $event->exception;
        }
    }
}
