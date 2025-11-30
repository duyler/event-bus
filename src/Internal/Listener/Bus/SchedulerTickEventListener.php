<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\Bus;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Internal\Event\DoCyclicEvent;
use Duyler\EventBus\Scheduler\Scheduler;
use Throwable;

final readonly class SchedulerTickEventListener
{
    public function __construct(
        private State $state,
        private ErrorHandlerInterface $errorHandler,
        private Scheduler $scheduler,
    ) {}

    public function __invoke(DoCyclicEvent $event): void
    {
        try {
            $this->scheduler->tick();
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
