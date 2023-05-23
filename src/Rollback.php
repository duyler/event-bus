<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\RollbackActionInterface;
use function is_callable;

readonly class Rollback
{
    public function __construct(private Storage $storage)
    {
    }

    public function run(array $slice = []): void
    {
        $tasks = empty($slice) ? $this->storage->task()->getAll() : $this->storage->task()->getAllByArray($slice);

        /** @var Task $task */
        foreach ($tasks as $task) {
            if (empty($task->action->rollback)) {
                continue;
            }

            if (is_callable($task->action->rollback)) {
                ($task->action->rollback)();
                continue;
            }

            $actionContainer = $this->storage->container()->get($task->action->id);

            $this->rollback($actionContainer->make($task->action->rollback));
        }
    }
    
    private function rollback(RollbackActionInterface $rollback): void
    {
        $rollback->run();
    }
}
