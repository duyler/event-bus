<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\TaskCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Enum\ResultStatus;

use function count;

class Bus
{
    /** @var Task[] $heldTasks */
    protected array $heldTasks = [];

    public function __construct(
        private readonly TaskQueue            $taskQueue,
        private readonly TaskCollection       $taskCollection,
        private readonly ActionCollection     $actionCollection,
    ) {
    }

    public function doAction(Action $action): void
    {
        $requiredIterator = new ActionRequiredIterator($action->required, $this->actionCollection->getAll());

        foreach ($requiredIterator as $subject) {

            $requiredAction = $this->actionCollection->get($subject);

            if ($this->taskCollection->isExists($requiredAction->id)) {
                continue;
            }

            $this->pushTask($this->createTask($requiredAction));
        }

        $this->pushTask($this->createTask($action));
    }

    protected function createTask(Action $action): Task
    {
        return new Task($action);
    }

    protected function pushTask(Task $task): void
    {
        if ($this->isSatisfiedConditions($task)) {
            $this->taskQueue->push($task);
        } else {
            $this->heldTasks[$task->action->id] = $task;
        }
    }

    public function resolveHeldTasks(): void
    {
        foreach($this->heldTasks as $key => $task) {
            if ($this->isSatisfiedConditions($task)) {
                $this->taskQueue->push($task);
                unset($this->heldTasks[$key]);
            }
        }
    }

    protected function isSatisfiedConditions(Task $task): bool
    {
        if (empty($task->action->required)) {
            return true;
        }

        $completeTasks = $this->taskCollection->getAllByArray($task->action->required->getArrayCopy());

        foreach ($completeTasks as $completeTask) {
            if ($completeTask->result->status === ResultStatus::Fail) {
                return false;
            }
        }

        return count($completeTasks) === count($task->action->required);
    }
}
