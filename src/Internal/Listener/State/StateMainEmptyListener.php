<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskQueueIsEmptyEvent;
use Throwable;

class StateMainEmptyListener
{
    public function __construct(
        private readonly StateMainInterface $stateMain,
        private readonly State $state,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {}

    public function __invoke(TaskQueueIsEmptyEvent $event): void
    {
        try {
            $this->stateMain->empty();
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
