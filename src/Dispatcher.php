<?php

declare(strict_types=1);

namespace Duyler\EventBus;

readonly class Dispatcher
{
    public function __construct(
        private Storage   $storage,
        private State     $state,
        private Control   $control,
    ) {
    }

    public function dispatchStartedTask(string $startActionId): void
    {
        $action = $this->storage->action()->get($startActionId);

        $this->control->resolveAction($action);
    }

    public function dispatchResultTask(Task $resultTask): void
    {
        $this->storage->task()->save($resultTask);

        $this->control->validate($resultTask);
        $this->control->log($resultTask);
        $this->control->resolveHeldTasks();
        $this->control->resolveSubscribers($resultTask->action->id, $resultTask->result->status);

        $this->state->after($resultTask);
    }
}
