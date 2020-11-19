<?php

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Task;
use Jine\EventBus\Dto\Subscribe;
use Jine\EventBus\Dto\Action;

class TaskFactory
{
    // Репозиторий всех участников
    private ServiceStorage $repository;
    
    public function __construct(ServiceStorage $repository)
    {
        $this->repository = $repository;
    }
    
    public function create(Action $action): Task
    {
        $task = new Task();
        $task->serviceId = $action->serviceId;
        $task->action = $action->name;
        $task->handler = $action->handler;
        $task->required = $action->required;
        
        return $task;
    }
}

