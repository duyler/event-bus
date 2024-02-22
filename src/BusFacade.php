<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Bus\Log;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Internal\Event\TriggerPushedEvent;
use Duyler\EventBus\Service\ResultService;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;

class BusFacade implements BusInterface
{
    public function __construct(
        private Runner $runner,
        private ResultService $resultService,
        private CompleteActionCollection $completeActionCollection,
        private ActionContainerCollection $actionContainerCollection,
        private TriggerRelationCollection $triggerRelationCollection,
        private EventDispatcherInterface $eventDispatcher,
        private Log $log,
    ) {}

    #[Override]
    public function run(): BusInterface
    {
        $this->runner->run();
        return $this;
    }

    #[Override]
    public function getResult(string $actionId): Result
    {
        return $this->resultService->getResult($actionId);
    }

    #[Override]
    public function resultIsExists(string $actionId): bool
    {
        return $this->resultService->resultIsExists($actionId);
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
        $this->completeActionCollection->cleanUp();
        $this->actionContainerCollection->cleanUp();
        $this->log->cleanUp();
        $this->triggerRelationCollection->cleanUp();
        return $this;
    }
}
