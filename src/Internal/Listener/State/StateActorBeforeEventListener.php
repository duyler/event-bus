<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\StateActorInterface;
use Duyler\EventBus\Internal\Event\ActorBeforeRunEvent;
use Throwable;

class StateActorBeforeEventListener
{
    public function __construct(
        private readonly StateActorInterface $stateActor,
        private readonly State $state,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {}

    public function __invoke(ActorBeforeRunEvent $event): void
    {
        try {
            $this->stateActor->before($event->actor, $event->argument);
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
