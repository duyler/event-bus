<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Task;

class TaskQueue extends \SplQueue
{
    public function addTask(Task $task): void
    {
        $this->push($task);
    }
}
