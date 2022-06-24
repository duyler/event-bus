<?php

declare(strict_types=1);

namespace Konveyer\EventBus;

class Loop
{
    private TaskRunner $taskRunner;
    private TaskQueue $taskQueue;

    public function __construct(TaskRunner $taskRunner, TaskQueue $taskQueue)
    {
        $this->taskRunner = $taskRunner;
        $this->taskQueue = $taskQueue;
    }

    public function run(): void
    {
        do {
            $task = $this->taskQueue->dequeue();
            if ($task->isRunning()) {
                $this->taskRunner->resume($task);
                continue;
            }
            $this->taskRunner->run($task);
        } while ($this->taskQueue->isNotEmpty());
    }
}
