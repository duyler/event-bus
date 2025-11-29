<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskBeforeRunEvent;
use Throwable;

class StateMainBeforeEventListener
{
    public function __construct(
        private readonly StateMainInterface $stateMain,
        private readonly State $state,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {}

    public function __invoke(TaskBeforeRunEvent $event): void
    {
        try {
            $this->state->setBeginAction($event->task->action->getId());
            $this->stateMain->before($event->task);
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
