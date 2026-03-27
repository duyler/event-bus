<?php

declare(strict_types=1);

namespace Duyler\EventBus\Internal\Listener\State;

use Duyler\EventBus\Bus\State;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Contract\ErrorHandlerInterface;
use Duyler\EventBus\Contract\StateActorInterface;
use Duyler\EventBus\Internal\Event\ActorThrownExceptionEvent;
use Duyler\EventBus\Storage\ActorContainerStorage;
use Throwable;

class StateActorThrowingEventListener
{
    public function __construct(
        private readonly StateActorInterface $stateActor,
        private readonly BusConfig $config,
        private readonly ActorContainerStorage $actorContainerStorage,
        private readonly State $state,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {}

    public function __invoke(ActorThrownExceptionEvent $event): void
    {
        try {
            $this->state->setErrorActor($event->actor->getId());
            $this->stateActor->throwing($event->actor, $event->exception);

            if ($this->config->continueAfterException) {
                $actorContainer = $this->actorContainerStorage->get($event->actor->getId());
                $actorContainer->finalize();
            } else {
                throw $event->exception;
            }
        } catch (Throwable $e) {
            $this->errorHandler->handle($e, $this->state->getLog());
        }
    }
}
