<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Contract\RollbackActionInterface;
use function is_callable;

readonly class Rollback
{
    public function __construct(private Collections $collections)
    {
    }

    public function run(array $slice = []): void
    {
        $tasks = empty($slice) ? $this->collections->task()->getAll() : $this->collections->task()->getAllByArray($slice);

        /** @var Task $task */
        foreach ($tasks as $task) {
            if (empty($task->action->rollback)) {
                continue;
            }

            if (is_callable($task->action->rollback)) {
                ($task->action->rollback)();
                continue;
            }

            $actionContainer = $this->collections->container()->get($task->action->id);

            $this->rollback($actionContainer->make($task->action->rollback));
        }
    }
    
    private function rollback(RollbackActionInterface $rollback): void
    {
        $rollback->run();
    }
}
