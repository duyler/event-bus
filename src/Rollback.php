<?php

declare(strict_types=1);

namespace Konveyer\EventBus;

use Konveyer\EventBus\Contract\RollbackInterface;
use Konveyer\EventBus\Storage\ContainerStorage;
use Konveyer\EventBus\Storage\TaskStorage;

class Rollback
{
    private TaskStorage $taskStorage;
    private ContainerStorage $containerStorage;

    public function __construct(
        TaskStorage $taskStorage,
        ContainerStorage $containerStorage
    ) {
        $this->taskStorage = $taskStorage;
        $this->containerStorage = $containerStorage;
    }

    public function run(): void
    {
        foreach ($this->taskStorage->getAll() as $task) {
            if (empty($task->action->rollback)) {
                continue;
            }

            $taskContainer = $this->containerStorage->get(
                ActionIdBuilder::byAction($task->action)
            );

            if (is_callable($task->action->rollback)) {
                ($task->action->rollback)();
                continue;
            }

            $taskContainer = $this->containerStorage->get(
                ActionIdBuilder::byAction($task->action)
            );

            $this->rollback($taskContainer->instance($task->action->rollback));
        }
    }
    
    private function rollback(RollbackInterface $rollback): void
    {
        $rollback->run();
    }
}
