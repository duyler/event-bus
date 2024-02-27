<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\BusConfig;
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
        private readonly BusConfig $config,
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

            if ($this->isRepeat($requiredAction->id) && false === $requiredAction->repeatable) {
                continue;
            }

            if ($requiredAction->triggeredOn !== null) {
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
                $failActions[] = $completeAction;
            }
        }

        foreach ($failActions as $failAction) {
            $this->alternates[$failAction->action->id] = $failAction->action->alternates;

            if ($this->isReplacedFailAction($failAction->action->id)) {
                $replacedActions[] = $failAction;
            }
        }

        if (count($failActions) > count($replacedActions)) {
            if ($this->taskQueue->isEmpty()) {
                if ($this->config->allowSkipUnresolvedActions) {
                    return false;
                }
                throw new UnableToContinueWithFailActionException($task->action->id);
            }
            return false;
        }

        return true;
    }

    private function isReplacedFailAction(string $failActionId): bool
    {
        foreach ($this->alternates[$failActionId] as $actionId) {
            $alternate = $this->actionCollection->get($actionId);

            if ($this->completeActionCollection->isExists($alternate->id)) {
                $completeAction = $this->completeActionCollection->get($alternate->id);
                if (ResultStatus::Success === $completeAction->result->status) {
                    return true;
                }
                continue;
            }

            $this->doAction($alternate);
            return false;
        }

        return false;
    }
}
