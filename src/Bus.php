<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\TaskCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Enum\ResultStatus;

use RuntimeException;

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
        if ($this->isRepeat($action->id) && $action->repeatable === false) {
            return;
        }

        $requiredIterator = new ActionRequiredIterator($action->required, $this->actionCollection->getAll());

        foreach ($requiredIterator as $subject) {

            $requiredAction = $this->actionCollection->get($subject);

            if ($this->isRepeat($requiredAction->id) && $requiredAction->repeatable === false) {
                continue;
            }

            $this->pushTask($this->createTask($requiredAction));
        }

        $this->pushTask($this->createTask($action));
    }

    protected function isRepeat(string $actionId): bool
    {
        return $this->taskQueue->inQueue($actionId) || $this->taskCollection->isExists($actionId);
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

        if (count($completeTasks) < count($task->action->required)) {
            return false;
        }

        foreach ($completeTasks as $completeTask) {
            if ($completeTask->result->status === ResultStatus::Fail) {
                if (empty($completeTask->action->contract)) {
                    continue;
                }

                $actionsWithContract = $this->actionCollection->getByContract($completeTask->action->contract);

                unset($actionsWithContract[$completeTask->action->id]);

                if ($this->taskQueue->isEmpty()) {
                    if ($this->isReplacedFailAction($actionsWithContract)) {
                        return true;
                    }
                    throw new RuntimeException(
                        'Replacement was not made for fail action ' . $task->action->id
                    );
                }

                if ($this->isReplacedFailAction($actionsWithContract)) {
                    return true;
                }

                return false;
            }
        }

        return true;
    }

    protected function isReplacedFailAction(array $actionsWithContract): bool
    {
        foreach ($actionsWithContract as $actionWithContract) {
            if ($this->taskCollection->isExists($actionWithContract->id)) {
                $task = $this->taskCollection->get($actionWithContract->id);
                if ($task->result->status === ResultStatus::Success) {
                    return true;
                }
            }
        }
        return false;
    }
}
