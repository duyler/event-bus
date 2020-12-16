<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Contract\RollbackInterface;

class Rollback
{
    private Container $container;
    private TaskStorage $taskStorage;

    public function __construct(Container $container, TaskStorage $taskStorage)
    {
        $this->container = $container;
        $this->taskStorage = $taskStorage;
    }

    public function run(array $taskContainers)
    {
        foreach ($this->taskStorage->getAll() as $task) {
            if (empty($task->rollback)) {
                continue;
            }
            $taskContainer = $taskContainers($task->serviceId . '.' . $task->action);
            $this->rollback($taskContainer->instance($task->rollback));
        }
    }
    
    private function rollback(RollbackInterface $rollback)
    {
        $rollback->run();
    }
}
