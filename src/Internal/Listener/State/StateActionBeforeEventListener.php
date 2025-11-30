<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Internal\Event\ActionBeforeRunEvent;
use Throwable;

class StateActionBeforeEventListener
{
    public function __construct(
        private readonly StateActionInterface $stateAction,
        private readonly State $state,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {}

    public function __invoke(ActionBeforeRunEvent $event): void
    {
        try {
            $this->stateAction->before($event->action, $event->argument);
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
