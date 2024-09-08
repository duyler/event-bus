<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\Rollback;
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
        private Rollback $rollback,
    ) {}

    public function __invoke(ActionThrownExceptionEvent $event): void
    {
        $this->stateAction->throwing($event->action, $event->exception);

        if ($this->config->continueAfterException) {
            $actionContainer = $this->actionContainerStorage->get($event->action->id);
            $actionContainer->finalize();
            $this->rollback->rollbackSingleAction($event->action);
        } else {
            throw $event->exception;
        }
    }
}
