<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Enum\ResultStatus;
use RuntimeException;
use function count;

class Bus
{
    /** @var Task[] $heldTasks */
    protected array $heldTasks = [];

    public function __construct(
        private readonly TaskQueue $taskQueue,
        private readonly ActionCollection $actionCollection,
        private readonly EventCollection $eventCollection,
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
        return $this->taskQueue->inQueue($actionId) || $this->eventCollection->isExists($actionId);
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

        $completeTaskEvents = $this->eventCollection->getAllByArray($task->action->required->getArrayCopy());

        if (count($completeTaskEvents) < count($task->action->required)) {
            return false;
        }

        foreach ($completeTaskEvents as $completeTask) {
            if ($completeTask->result->status === ResultStatus::Fail) {

                if ($completeTask->action->continueIfFail === false) {
                    throw new RuntimeException(
                        'Cannot be continued because fail action ' . $completeTask->action->id
                    );
                }

                if (empty($completeTask->action->contract)) {
                    continue;
                }

                $actionsWithContract = $this->actionCollection->getByContract($completeTask->action->contract);

                unset($actionsWithContract[$completeTask->action->id]);

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
            if ($this->eventCollection->isExists($actionWithContract->id)) {
                $event = $this->eventCollection->get($actionWithContract->id);
                if ($event->result->status === ResultStatus::Success) {
                    return true;
                }
            }
        }
        return false;
    }
}
