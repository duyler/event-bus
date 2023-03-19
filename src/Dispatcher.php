<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Subscribe;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\CyclicCallActionException;

use function count;
use function in_array;

class Dispatcher
{
    protected array $heldTasks = [];
    protected array $mainEventLog = [];
    protected array $repeatedEventLog = [];
    protected string $startActionId = '';

    public function __construct(
        private readonly Storage          $storage,
        private readonly TaskQueue        $taskQueue,
        private readonly State            $state
    ) {
    }

    public function prepareStartedTask(string $startActionId): void
    {
        $this->startActionId = $startActionId;

        $action = $this->storage->action()->get($startActionId);

        $task = $this->createTask($action);

        $this->dispatchRequired($action);

        $this->dispatchTask($task);
    }

    public function dispatchResultEvent(Task $resultTask): void
    {
        $this->storage->task()->save($resultTask);

        $this->checkCyclicActionCalls($resultTask);

        $this->dispatchHeld();

        if (!$resultTask->subscribe?->silent) {
            $this->dispatchSubscribersTasks($resultTask);
        }

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
                throw new CyclicCallActionException(
                    $task->action->id,
                    $task->subscribe->subject ?? $this->startActionId
                );
            }
        }
    }

    protected function dispatchSubscribersTasks(Task $resultTask): void
    {
        $subscribers = $this->storage->subscribe()->getSubscribers($resultTask->action->id, $resultTask->result->status);

        foreach ($subscribers as $subscribe) {

            $action = $this->storage->action()->get($subscribe->actionId);

            $task = $this->createTask($action, $subscribe);

            $this->dispatchRequired($action);
            $this->dispatchTask($task);
        }
    }

    protected function dispatchRequired(Action $action): void
    {
        $requiredIterator = new ActionRequiredIterator($action->require, $this->storage->action());

        foreach ($requiredIterator as $subject) {

            $requiredAction = $this->storage->action()->get($subject);

            if ($this->storage->task()->isExists($requiredAction->id)) {
                $result = $this->storage->task()->getResult($requiredAction->id);
                if ($result->status === ResultStatus::Success) {
                    continue;
                }
                break;
            }

            $this->dispatchTask($this->createTask($requiredAction));
        }
    }

    protected function dispatchTask(Task $task): void
    {
        if ($this->isSatisfiedConditions($task)) {
            $this->taskQueue->add($task);
        } else {
            $this->heldTasks[$task->action->id] = $task;
        }
    }

    private function dispatchHeld(): void
    {
        foreach($this->heldTasks as $key => $task) {
            if ($this->isSatisfiedConditions($task)) {
                $this->taskQueue->add($task);
                unset($this->heldTasks[$key]);
            }
        }
    }

    private function isSatisfiedConditions(Task $task): bool
    {
        if (empty($task->action->require)) {
            return true;
        }

        $completeTasks = $this->storage->task()->getAllByRequired($task->action->require);

        foreach ($completeTasks as $completeTask) {
            if ($completeTask->result->status === ResultStatus::Fail) {
                return false;
            }
        }

        return count($completeTasks) === count($task->action->require);
    }

    protected function createTask(Action $action, Subscribe $subscribe = null): Task
    {
        return new Task($action, $subscribe);
    }
}
