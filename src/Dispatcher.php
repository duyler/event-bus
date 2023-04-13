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

    public function dispatchStartedAction(string $startActionId): void
    {
        $this->control->doExistsAction($startActionId);
    }

    public function dispatchResultTask(Task $resultTask): void
    {
        $this->storage->task()->save($resultTask);
        $this->state->after($resultTask);

        $this->control->log($resultTask);
        $this->control->validateResultTask($resultTask);
        $this->control->validateSubscriptions();
        $this->control->resolveHeldTasks();
        $this->control->resolveSubscriptions($resultTask->action->id, $resultTask->result->status);
    }
}
