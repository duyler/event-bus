<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\TaskUnresolvedEvent;
use Throwable;

readonly class StateMainUnresolvedEventListener
{
    public function __construct(
        private StateMainInterface $stateMain,
        private readonly State $state,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {}

    public function __invoke(TaskUnresolvedEvent $event): void
    {
        try {
            $this->stateMain->unresolved($event->task);
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
