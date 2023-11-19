<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Contract\RollbackActionInterface;
use Duyler\EventBus\Dto\Result;
use function is_callable;

readonly class Rollback
{
    public function __construct(
        private EventCollection $eventCollection,
        private ActionContainerCollection $containerCollection,
    ) {
    }

    public function run(array $slice = []): void
    {
        $tasks = empty($slice) ? $this->eventCollection->getAll() : $this->eventCollection->getAllByArray($slice);

        foreach ($tasks as $task) {
            if (empty($task->action->rollback)) {
                continue;
            }

            if (is_callable($task->action->rollback)) {
                ($task->action->rollback)();
                continue;
            }

            $actionContainer = $this->containerCollection->get($task->action->id);

            $this->rollback($actionContainer->make($task->action->rollback), $task->result);
        }
    }

    private function rollback(RollbackActionInterface $rollback, Result $result): void
    {
        $rollback->run($result);
    }
}
