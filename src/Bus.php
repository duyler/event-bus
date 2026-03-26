<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Dto\Log;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Internal\Event\BusCompletedEvent;
use Duyler\EventBus\Internal\Event\EventDispatchedEvent;
use Duyler\EventBus\Service\ResultService;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use UnitEnum;

class Bus implements BusInterface
{
    public function __construct(
        private readonly Runner $runner,
        private readonly ResultService $resultService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Termination $termination,
    ) {}

    #[Override]
    public function run(): BusInterface
    {
        $this->runner->run();
        $this->eventDispatcher->dispatch(new BusCompletedEvent());

        return $this;
    }

    #[Override]
    public function getResult(string|UnitEnum $actionId, string $correlationId = 'common'): Result
    {
        return $this->resultService->getResult(IdFormatter::toString($actionId), $correlationId);
    }

    #[Override]
    public function resultIsExists(string|UnitEnum $actionId, string $correlationId = 'common'): bool
    {
        return $this->resultService->resultIsExists(IdFormatter::toString($actionId), $correlationId);
    }

    #[Override]
    public function dispatchEvent(Event $event, string $correlationId = 'common'): BusInterface
    {
        $this->eventDispatcher->dispatch(new EventDispatchedEvent($event, $correlationId));

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
