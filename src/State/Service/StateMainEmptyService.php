<?php

declare(strict_types=1);

namespace Duyler\EventBus\State\Service;

use Duyler\EventBus\Internal\Event\BusIsResetEvent;
use Duyler\EventBus\Service\ActorService;
use Duyler\EventBus\Service\EventService;
use Duyler\EventBus\Service\LogService;
use Duyler\EventBus\Service\ResultService;
use Duyler\EventBus\Service\RollbackService;
use Duyler\EventBus\State\Service\Trait\ActorServiceTrait;
use Duyler\EventBus\State\Service\Trait\EventServiceTrait;
use Duyler\EventBus\State\Service\Trait\LogServiceTrait;
use Duyler\EventBus\State\Service\Trait\ResultServiceTrait;
use Duyler\EventBus\State\Service\Trait\RollbackServiceTrait;
use Psr\EventDispatcher\EventDispatcherInterface;

class StateMainEmptyService
{
    use ActorServiceTrait;
    use ResultServiceTrait;
    use LogServiceTrait;
    use EventServiceTrait;
    use RollbackServiceTrait;

    public function __construct(
        private readonly ActorService $actorService,
        private readonly ResultService $resultService,
        private readonly LogService $logService,
        private readonly EventService $eventService,
        private readonly RollbackService $rollbackService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function reset(): void
    {
        $this->eventDispatcher->dispatch(new BusIsResetEvent());
    }
}
