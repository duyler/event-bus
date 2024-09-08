<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\UnableToContinueWithFailActionException;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\DependencyInjection\Attribute\Finalize;

use function count;

#[Finalize(method: 'reset')]
final class Bus
{
    /** @var Task[] */
    private array $heldTasks = [];

    /** @var array<string, string[]> */
    private array $alternates = [];

    /** @var array<string, int> */
    private array $retries = [];

    /** @var array<string, bool> */
    private array $finalized = [];

    public function __construct(
        private readonly TaskQueue $taskQueue,
        private readonly ActionStorage $actionStorage,
        private readonly CompleteActionStorage $completeActionStorage,
        private readonly BusConfig $config,
    ) {}

    public function doAction(Action $action): void
    {
        if ($this->isRepeat($action->id) && false === $action->repeatable) {
            return;
        }

        $requiredIterator = new ActionRequiredIterator($action->required, $this->actionStorage->getAll());

        /** @var string $subject */
        foreach ($requiredIterator as $subject) {
            $requiredAction = $this->actionStorage->get($subject);

            if ($this->isRepeat($requiredAction->id) && false === $requiredAction->repeatable) {
                continue;
            }

            if (null !== $requiredAction->listen) {
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

        return $this->taskQueue->inQueue($actionId) || $this->completeActionStorage->isExists($actionId);
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
            $this->finalized[$task->action->id] = false;
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

        $completeActions = $this->completeActionStorage->getAllByArray($task->action->required->getArrayCopy());

        if (count($completeActions) < $task->action->required->count()) {
            return false;
        }

        /** @var CompleteAction[] $failActions */
        $failActions = [];
        /** @var CompleteAction[] $replacedActions */
        $replacedActions = [];

        foreach ($completeActions as $completeAction) {
            if (ResultStatus::Fail === $completeAction->result->status) {
                if ($this->retries[$completeAction->action->id] < $completeAction->action->retries) {
                    return false;
                }

                if (false === $this->finalized[$completeAction->action->id]) {
                    return false;
                }

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
            $alternate = $this->actionStorage->get($actionId);

            if ($this->completeActionStorage->isExists($alternate->id)) {
                $completeAction = $this->completeActionStorage->get($alternate->id);
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

    public function finalizeCompleteAction(CompleteAction $completeAction): void
    {
        if (ResultStatus::Success === $completeAction->result->status) {
            $this->finalized[$completeAction->action->id] = true;
            return;
        }

        if ($this->retries[$completeAction->action->id] < $completeAction->action->retries) {
            $this->taskQueue->push($this->createTask($completeAction->action));
            ++$this->retries[$completeAction->action->id];
        } else {
            $this->finalized[$completeAction->action->id] = true;
        }
    }

    public function reset(): void
    {
        $this->heldTasks = [];
        $this->retries = [];
        $this->alternates = [];
        $this->finalized = [];
    }
}
