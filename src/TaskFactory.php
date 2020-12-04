<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Task;
use Jine\EventBus\Dto\Subscribe;
use Jine\EventBus\Dto\Action;

class TaskFactory
{
    public function create(Action $action, Subscribe $subscribe): Task
    {
        $task = new Task();
        $task->serviceId = $action->serviceId;
        $task->action = $action->name;
        $task->handler = $action->handler;
        $task->required = $action->required;
        $task->subscribe = $subscribe->subject;
        
        return $task;
    }
}

