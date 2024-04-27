<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Log;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\Internal\Event\BusCompletedEvent;
use Duyler\EventBus\Internal\Event\TriggerPushedEvent;
use Duyler\EventBus\Service\ResultService;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use UnitEnum;

class BusFacade implements BusInterface
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
        return $this->resultService->getResult(IdFormatter::format($actionId));
    }

    #[Override]
    public function resultIsExists(string|UnitEnum $actionId): bool
    {
        return $this->resultService->resultIsExists(IdFormatter::format($actionId));
    }

    #[Override]
    public function dispatchTrigger(Trigger $trigger): BusInterface
    {
        $this->eventDispatcher->dispatch(new TriggerPushedEvent($trigger));

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
