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

final class Bus
{
    /** @var Task[] */
    private array $heldTasks = [];

    /** @var array<string, string[]> */
    private array $alternates = [];

    /** @var array<string, int> */
    private array $retries = [];

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

            if (null !== $requiredAction->triggeredOn) {
                continue;
            }

            $this->pushTask($this->createTask($requiredAction));
        }

        $this->pushTask($this->createTask($action));
    }

    private function isRepeat(string $actionId): bool
    {
        foreach ($this->heldTasks as $task) {
            if ($task->action->id === $actionId) {
                return true;
            }
        }

        return $this->taskQueue->inQueue($actionId) || $this->completeActionCollection->isExists($actionId);
    }

    private function createTask(Action $action): Task
    {
        return new Task($action);
    }

    private function pushTask(Task $task): void
    {
        if ($this->isSatisfiedConditions($task)) {
            $this->taskQueue->push($task);
            $this->retries[$task->action->id] = 0;
        } else {
            $this->heldTasks[] = $task;
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

    private function isSatisfiedConditions(Task $task): bool
    {
        if ($task->action->lock && $this->taskQueue->inQueue($task->action->id)) {
            return false;
        }

        if (0 === $task->action->required->count()) {
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

            if ($this->tryReplacedFailAction($failAction->action->id)) {
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

    private function tryReplacedFailAction(string $failActionId): bool
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

    public function completeDoAction(CompleteAction $completeAction): void
    {
        if (ResultStatus::Fail === $completeAction->result->status) {
            if ($completeAction->action->retries > 0
                && $this->retries[$completeAction->action->id] < $completeAction->action->retries
            ) {
                $this->taskQueue->push($this->createTask($completeAction->action));
                ++$this->retries[$completeAction->action->id];
            }
        }
    }
}
