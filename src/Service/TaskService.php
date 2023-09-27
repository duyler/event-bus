<?php

declare(strict_types=1);

namespace Duyler\EventBus\Service;

use Duyler\EventBus\Bus;
use Duyler\EventBus\Collection\TaskCollection;
use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;
use Duyler\EventBus\Log;
use Duyler\EventBus\Task;

readonly class TaskService
{
    public function __construct(
        private Bus            $bus,
        private TaskCollection $taskCollection,
        private Log            $log,
    ) {
    }

    public function resolveHeldTasks(): void
    {
        $this->bus->resolveHeldTasks();
    }

    public function saveResultTask(Task $task): void
    {
        $this->taskCollection->save($task);

        $this->log->pushActionLog($task->action->id);
    }

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function validateResultTask(Task $task): void
    {
        if ($this->taskCollection->isExists($task->action->id)) {

            $actionId = $task->action->id . '.' . $task->result->status->value;

            if (in_array($actionId, $this->log->getMainEventLog()) && $task->action->repeatable === false) {
                $this->log->pushRepeatedEventLog($actionId);
            } else {
                $this->log->pushMainEventLog($actionId);
            }

            $mainEventLog = $this->log->getMainEventLog();
            $repeatedEventLog = $this->log->getRepeatedEventLog();

            if (end($repeatedEventLog) === $actionId && $task->action->repeatable === false) {
                throw new ConsecutiveRepeatedActionException(
                    $task->action->id,
                    $task->result->status->value
                );
            }

            if (count($mainEventLog) === count($repeatedEventLog)) {
                throw new CircularCallActionException(
                    $task->action->id,
                    end($mainEventLog)
                );
            }
        }
    }
}
