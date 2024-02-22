<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;

class Termination
{
    public function __construct(
        private CompleteActionCollection $completeActionCollection,
        private ActionContainerCollection $actionContainerCollection,
        private TriggerRelationCollection $triggerRelationCollection,
        private Log $log,
    ) {}

    public function run(): void
    {
        $this->completeActionCollection->reset();
        $this->actionContainerCollection->reset();
        $this->log->reset();
        $this->triggerRelationCollection->reset();
    }
}
