<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Build\Actor as ExternalActor;
use Duyler\EventBus\Bus\Actor as InternalActor;
use Duyler\EventBus\Bus\ActorContainer;

class StateActorAfterService
{
    public function __construct(
        private readonly ActorContainer $container,
        private readonly InternalActor $actor,
        private readonly mixed $resultData,
    ) {}

    public function getContainer(): ActorContainer
    {
        return $this->container;
    }

    public function getActor(): ExternalActor
    {
        return ExternalActor::fromInternal($this->actor);
    }

    public function getResultData(): mixed
    {
        return $this->resultData;
    }
}
