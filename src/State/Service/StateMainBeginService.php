<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Build\Actor as ExternalActor;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Service\ActorService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\State\Service\Trait\ActorServiceTrait;
use Duyler\EventBus\State\Service\Trait\EventServiceTrait;
use UnitEnum;

class StateMainBeginService
{
    use ActorServiceTrait;
    use EventServiceTrait;

    public function __construct(
        private readonly ActorService $actorService,
        private readonly EventService $eventService,
    ) {}

    public function getById(string|UnitEnum $actorId): ExternalActor
    {
        $internalActor = $this->actorService->getById(IdFormatter::toString($actorId));
        return ExternalActor::fromInternal($internalActor);
    }
}
