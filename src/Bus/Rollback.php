<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Contract\RollbackActionInterface;
use Duyler\EventBus\Dto\Result;

use function is_callable;

class Rollback
{
    public function __construct(
        private EventCollection $eventCollection,
        private ActionContainerCollection $containerCollection,
    ) {}

    public function run(array $slice = []): void
    {
        $events = empty($slice) ? $this->eventCollection->getAll() : $this->eventCollection->getAllByArray($slice);

        foreach ($events as $event) {
            if (empty($event->action->rollback)) {
                continue;
            }

            if (is_callable($event->action->rollback)) {
                ($event->action->rollback)();
                continue;
            }

            $actionContainer = $this->containerCollection->get($event->action->id);

            $this->rollback($actionContainer->make($event->action->rollback), $event->result);
        }
    }

    private function rollback(RollbackActionInterface $rollback, Result $result): void
    {
        $rollback->run($result);
    }
}
