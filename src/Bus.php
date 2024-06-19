<?php

declare(strict_types=1);

namespace Duyler\ActionBus;

use Duyler\ActionBus\Dto\Log;
use Duyler\ActionBus\Dto\Result;
use Duyler\ActionBus\Dto\Event;
use Duyler\ActionBus\Formatter\IdFormatter;
use Duyler\ActionBus\Internal\Event\BusCompletedEvent;
use Duyler\ActionBus\Internal\Event\EventDispatchedEvent;
use Duyler\ActionBus\Service\ResultService;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use UnitEnum;

class Bus implements BusInterface
{
    public function __construct(
        private Runner $runner,
        private ResultService $resultService,
        private EventDispatcherInterface $eventDispatcher,
        private Termination $termination,
    ) {}

    #[Override]
    public function run(): BusInterface
    {
        $this->runner->run();
        $this->eventDispatcher->dispatch(new BusCompletedEvent());

        return $this;
    }

    #[Override]
    public function getResult(string|UnitEnum $actionId): Result
    {
        return $this->resultService->getResult(IdFormatter::toString($actionId));
    }

    #[Override]
    public function resultIsExists(string|UnitEnum $actionId): bool
    {
        return $this->resultService->resultIsExists(IdFormatter::toString($actionId));
    }

    #[Override]
    public function dispatchEvent(Event $event): BusInterface
    {
        $this->eventDispatcher->dispatch(new EventDispatchedEvent($event));

        return $this;
    }

    #[Override]
    public function reset(): BusInterface
    {
        $this->termination->run();

        return $this;
    }

    #[Override]
    public function getLog(): Log
    {
        return $this->termination->getLog();
    }
}
