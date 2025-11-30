<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\StateMainInterface;
use Duyler\EventBus\Internal\Event\DoWhileEndEvent;
use Throwable;

class StateMainEndEventListener
{
    public function __construct(
        private readonly StateMainInterface $stateMain,
        private readonly State $state,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {}

    public function __invoke(DoWhileEndEvent $event): void
    {
        try {
            $this->stateMain->end();
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
