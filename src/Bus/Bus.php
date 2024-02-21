<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\CompleteActionCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\UnableToContinueWithFailActionException;

use function count;

class Bus
{
    /** @var Task[] */
    protected array $heldTasks = [];

    /** @var array<string, string[]>  */
    private array $alternates = [];

    public function __construct(
        private readonly TaskQueue $taskQueue,
        private readonly ActionCollection $actionCollection,
        private readonly CompleteActionCollection $completeActionCollection,
    ) {}

    public function doAction(Action $action): void
    {
        if ($this->isRepeat($action->id) && false === $action->repeatable) {
            return;
        }

        $requiredIterator = new ActionRequiredIterator($action->required, $this->actionCollection->getAll());

        /** @var string $subject */
        foreach ($requiredIterator as $subject) {
            $requiredAction = $this->actionCollection->get($subject);

            if (!empty($requiredAction->sealed) && !in_array($action->id, $requiredAction->sealed)) {
                return;
            }

            if ($requiredAction->private) {
                return;
            }

            if ($this->isRepeat($requiredAction->id) && false === $requiredAction->repeatable) {
                continue;
            }

            $this->pushTask($this->createTask($requiredAction));
        }

        $this->pushTask($this->createTask($action));
    }

    protected function isRepeat(string $actionId): bool
    {
        return $this->taskQueue->inQueue($actionId) || $this->completeActionCollection->isExists($actionId);
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
        foreach ($this->heldTasks as $key => $task) {
            if ($this->isSatisfiedConditions($task)) {
                $this->taskQueue->push($task);
                unset($this->heldTasks[$key]);
            }
        }
    }

    protected function isSatisfiedConditions(Task $task): bool
    {
        if ($task->action->required->count() === 0) {
            return true;
        }

        $completeActions = $this->completeActionCollection->getAllByArray($task->action->required->getArrayCopy());

        if (count($completeActions) < $task->action->required->count()) {
            return false;
        }

        /** @var CompleteAction[] $failActions */
        $failActions = [];
        /** @var CompleteAction[] $replacedActions */
        $replacedActions = [];

        foreach ($completeActions as $completeAction) {
            if (ResultStatus::Fail === $completeAction->result->status) {
                if (false === $completeAction->action->continueIfFail) {
                    throw new UnableToContinueWithFailActionException($completeAction->action->id);
                }

                $failActions[] = $completeAction;
            }
        }

        foreach ($failActions as $failAction) {
            if ($failAction->action->contract === null) {
                continue;
            }

            $actionsWithContract = $this->actionCollection->getByContract($failAction->action->contract);

            unset($actionsWithContract[$failAction->action->id]);

            if ($this->isReplacedFailAction($actionsWithContract)) {
                $replacedActions[] = $actionsWithContract;
            }
        }

        if (count($failActions) > count($replacedActions)) {
            return false;
        }

        return true;
    }

    /** @param array<string, Action> $actionsWithContract  */
    protected function isReplacedFailAction(array $actionsWithContract): bool
    {
        foreach ($actionsWithContract as $actionWithContract) {
            if ($this->completeActionCollection->isExists($actionWithContract->id)) {
                $completeAction = $this->completeActionCollection->get($actionWithContract->id);
                if (ResultStatus::Success === $completeAction->result->status) {
                    return true;
                }
            }
        }

        return false;
    }
}
