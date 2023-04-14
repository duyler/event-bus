<?php

declare(strict_types=1);

namespace Duyler\EventBus;

class DoWhile
{
    public function __construct(
        private readonly TaskRunner $taskRunner,
        private readonly TaskQueue $taskQueue,
        private readonly State $state,
    ) {
    }

    public function run(): void
    {
        $this->state->start();
        do {
            $task = $this->taskQueue->dequeue();
            if ($task->isRunning()) {
                $this->taskRunner->resume($task);
                continue;
            }
            $this->state->before($task);
            $this->taskRunner->run($task);
        } while ($this->taskQueue->isNotEmpty());

        $this->state->final();
    }
}
