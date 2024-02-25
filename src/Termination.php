<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Dto\Log as LogDto;
use Duyler\EventBus\Bus\Log;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Collection\TriggerRelationCollection;

class Termination
{
    private null|LogDto $logDto = null;

    public function __construct(
        private CompleteActionCollection $completeActionCollection,
        private ActionContainerCollection $actionContainerCollection,
        private TriggerRelationCollection $triggerRelationCollection,
        private Log $log,
    ) {}

    public function run(): void
    {
        $this->logDto = $this->log->getLog();
        $this->completeActionCollection->reset();
        $this->actionContainerCollection->reset();
        $this->log->reset();
        $this->triggerRelationCollection->reset();
    }

    public function getLog(): LogDto
    {
        if ($this->logDto === null) {
            $this->logDto = $this->log->getLog();
        }
        return $this->logDto;
    }
}
