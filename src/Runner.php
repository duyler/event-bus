<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Bus\DoWhile;
use Duyler\EventBus\Bus\Rollback;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\EventCollection;
use Throwable;

class Runner
{
    public function __construct(
        private DoWhile $doWhile,
        private Rollback $rollback,
        private EventCollection $eventCollection,
        private ActionContainerCollection $actionContainerCollection,
    ) {}

    /**
     * @throws Throwable
     */
    public function run(): void
    {
        try {
            $this->eventCollection->cleanUp();
            $this->actionContainerCollection->cleanUp();
            $this->doWhile->run();
        } catch (Throwable $exception) {
            $this->rollback->run();
            throw $exception;
        }
    }
}
