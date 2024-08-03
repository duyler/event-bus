<?php

declare(strict_types=1);

namespace Duyler\ActionBus\Internal\Listener\State;

use Duyler\ActionBus\Bus\Rollback;
use Duyler\ActionBus\BusConfig;
use Duyler\ActionBus\Contract\StateActionInterface;
use Duyler\ActionBus\Internal\Event\ActionThrownExceptionEvent;
use Duyler\ActionBus\Storage\ActionContainerStorage;

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
