<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Task;

class TaskFactory
{
    public function create(Action $action): Task
    {
        $task = new Task();
        $task->serviceId = $action->serviceId;
        $task->action = $action->name;
        $task->handler = $action->handler;
        $task->required = $action->required;
        $task->classMap = $action->classMap;
        $task->rollback = $action->rollback;
        $task->repeat = $action->repeat;
        
        return $task;
    }
}

