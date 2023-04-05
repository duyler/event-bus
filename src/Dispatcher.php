<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Exception\CircularCallActionException;

use function count;
use function in_array;

class Dispatcher
{
    protected array $mainEventLog = [];
    protected array $repeatedEventLog = [];
    protected string $startActionId = '';

    public function __construct(
        private readonly Storage    $storage,
        private readonly State      $state,
        private readonly BusControl $busControl,
    ) {
    }

    public function prepareStartedTask(string $startActionId): void
    {
        $this->startActionId = $startActionId;

        $action = $this->storage->action()->get($startActionId);

        $this->busControl->resolveAction($action);
    }

    public function dispatchResultEvent(Task $resultTask): void
    {
        $this->storage->task()->save($resultTask);

        $this->checkCyclicActionCalls($resultTask);

        $this->busControl->resolveHeldTasks();

        $this->busControl->resolveSubscribers($resultTask->action->id, $resultTask->result->status);

        $this->state->tick($resultTask);
    }

    protected function checkCyclicActionCalls(Task $task): void
    {
        if ($this->storage->task()->isExists($task->action->id)) {

            $actionId = $task->action->id . $task->result->status->value;

            if (in_array($actionId, $this->mainEventLog)) {
                $this->repeatedEventLog[] = $actionId;
            } else {
                $this->mainEventLog[] = $actionId;
            }

            if (count($this->mainEventLog) === count($this->repeatedEventLog)) {
                throw new CircularCallActionException(
                    $task->action->id,
                    $task->subscribe->subjectId ?? $this->startActionId
                );
            }
        }
    }
}
