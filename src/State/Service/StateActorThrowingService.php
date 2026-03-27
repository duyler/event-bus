<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Build\Actor as ExternalActor;
use Duyler\EventBus\Bus\Actor as InternalActor;
use Duyler\EventBus\Bus\ActorContainer;
use Throwable;

class StateActorThrowingService
{
    public function __construct(
        private readonly ActorContainer $container,
        private readonly Throwable $exception,
        private readonly InternalActor $actor,
    ) {}

    public function getContainer(): ActorContainer
    {
        return $this->container;
    }

    public function getActor(): ExternalActor
    {
        return ExternalActor::fromInternal($this->actor);
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }
}
