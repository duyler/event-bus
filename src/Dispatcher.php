<?php

declare(strict_types=1);

namespace Duyler\EventBus;

readonly class Dispatcher
{
    public function __construct(
        private Storage   $storage,
        private Control   $control,
    ) {
    }

    public function dispatchResultTask(Task $resultTask): void
    {
        $this->storage->task()->save($resultTask);

        $this->control->log($resultTask);
        $this->control->validateResultTask($resultTask);
        $this->control->validateSubscriptions();
        $this->control->resolveHeldTasks();
        $this->control->resolveSubscriptions($resultTask->action->id, $resultTask->result->status);
    }
}
