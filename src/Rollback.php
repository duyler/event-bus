<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\TaskCollection;
use Duyler\EventBus\Contract\RollbackActionInterface;

use function is_callable;

readonly class Rollback
{
    public function __construct(
        private TaskCollection            $taskCollection,
        private ActionContainerCollection $containerCollection,
    ) {
    }

    public function run(array $slice = []): void
    {
        $tasks = empty($slice) ? $this->taskCollection->getAll() : $this->taskCollection->getAllByArray($slice);

        foreach ($tasks as $task) {
            if (empty($task->action->rollback)) {
                continue;
            }

            if (is_callable($task->action->rollback)) {
                ($task->action->rollback)();
                continue;
            }

            $actionContainer = $this->containerCollection->get($task->action->id);

            $this->rollback($actionContainer->make($task->action->rollback));
        }
    }

    private function rollback(RollbackActionInterface $rollback): void
    {
        $rollback->run();
    }
}
