<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\StateActorInterface;
use Duyler\EventBus\Internal\Event\ActorAfterRunEvent;
use Throwable;

class StateActorAfterEventListener
{
    public function __construct(
        private readonly StateActorInterface $stateActor,
        private readonly State $state,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {}

    public function __invoke(ActorAfterRunEvent $event): void
    {
        try {
            $this->stateActor->after($event->actor, $event->result);
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
