<?php

declare(strict_types=1);

namespace Duyler\EventBus;

readonly class Dispatcher
{
    public function __construct(
        private Collections $collections,
        private BusService  $busService,
    ) {
    }

    public function dispatchResultTask(Task $resultTask): void
    {
        $this->collections->task()->save($resultTask);

        $this->busService->log($resultTask);
        $this->busService->validateResultTask($resultTask);
        $this->busService->resolveHeldTasks();
        $this->busService->resolveSubscriptions($resultTask->action->id, $resultTask->result->status);
    }
}
