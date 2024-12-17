<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use DateTimeImmutable;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Enum\TaskStatus;
use Duyler\EventBus\Exception\UnableToContinueWithFailActionException;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\TaskStorage;

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
        private readonly EventRelationStorage $eventRelationStorage,
        private readonly TaskStorage $taskStorage,
    ) {}

    public function doAction(Action $action): void
    {
        if (false === $this->isSatisfiedEvents($action)) {
            return;
        }

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

            if (0 < count($requiredAction->listen)) {
                continue;
            }

            $this->pushTask($this->createPrimaryTask($requiredAction));
        }

        $this->pushTask($this->createPrimaryTask($action));
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

    private function createPrimaryTask(Action $action): Task
    {
        $task = new Task($action);
        $task->setStatus(TaskStatus::Primary);
        $this->taskStorage->add($task);
        return $task;
    }

    private function pushTask(Task $task): void
    {
        if ($this->isSatisfiedConditions($task)) {
            $this->taskQueue->push($task);
            $this->retries[$task->getId()] = 0;
            $this->finalized[$task->action->id] = false;
        } else {
            $task->setStatus(TaskStatus::Held);
            $this->heldTasks[$task->getId()] = $task;
        }
    }

    public function resolveHeldTasks(): void
    {
        foreach ($this->heldTasks as $key => $task) {
            if ($this->isSatisfiedConditions($task)) {
                $task->setStatus(TaskStatus::Primary);
                $this->taskQueue->push($task);
                unset($this->heldTasks[$key]);
            }
        }
    }

    private function isSatisfiedEvents(Action $action): bool
    {
        foreach ($action->listen as $eventId) {
            if (false === $this->eventRelationStorage->isExists($eventId)) {
                return false;
            }
        }
        return true;
    }

    private function isSatisfiedConditions(Task $task): bool
    {
        if ($this->isLock($task)) {
            return false;
        }

        if (0 === $task->action->required->count()) {
            return true;
        }

        $completeRequiredActions = $this->completeActionStorage->getAllByArray($task->action->required->getArrayCopy());

        if (count($completeRequiredActions) < $task->action->required->count()) {
            return false;
        }

        /** @var CompleteAction[] $failActions */
        $failActions = [];
        /** @var CompleteAction[] $replacedActions */
        $replacedActions = [];

        foreach ($completeRequiredActions as $completeRequiredAction) {
            if (ResultStatus::Fail === $completeRequiredAction->result->status) {
                if ($this->retries[$completeRequiredAction->taskId] < $completeRequiredAction->action->retries) {
                    return false;
                }

                if (false === $this->finalized[$completeRequiredAction->action->id]) {
                    return false;
                }

                $failActions[] = $completeRequiredAction;
            }
        }

        foreach ($failActions as $failAction) {
            $this->alternates[$failAction->action->id] = $failAction->action->alternates;

            if ($this->tryReplacedFailAction($failAction->action->id)) {
                $replacedActions[] = $failAction;
            }
        }

        if (count($failActions) > count($replacedActions)) {

            foreach ($failActions as $failAction) {
                foreach ($this->alternates[$failAction->action->id] as $alternate) {
                    if ($this->taskQueue->inQueue($alternate)) {
                        return false;
                    }
                }
            }

            if ($this->config->allowSkipUnresolvedActions) {
                unset($this->heldTasks[$task->getId()]);
                return false;
            }

            throw new UnableToContinueWithFailActionException($task->action->id);
        }

        return true;
    }

    private function isLock(Task $task): bool
    {
        if (false === $task->action->lock) {
            return false;
        }

        if ($this->taskQueue->inQueue($task->action->id)) {
            return true;
        }

        $tasks = $this->taskStorage->getAllByActionId($task->action->id);

        unset($tasks[$task->getId()]);

        foreach ($tasks as $currentTask) {
            if (TaskStatus::Primary === $task->getStatus()) {
                if (array_key_exists($currentTask->getId(), $this->heldTasks)) {
                    return true;
                }
            }

            if (($this->retries[$currentTask->action->id] ?? 0) < $task->action->retries) {
                return true;
            }
        }

        return false;
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

    public function afterCompleteAction(CompleteAction $completeAction): void
    {
        if (ResultStatus::Success === $completeAction->result->status) {
            $this->finalized[$completeAction->action->id] = true;
            $this->removeTask($completeAction);
            return;
        }

        if ($this->retries[$completeAction->taskId] < $completeAction->action->retries) {
            $this->taskQueue->push($this->createRetryTask($completeAction));
            ++$this->retries[$completeAction->taskId];
        } else {
            $this->finalized[$completeAction->action->id] = true;
            $this->removeTask($completeAction);
        }
    }

    private function removeTask(CompleteAction $completeAction): void
    {
        if (Mode::Loop === $this->config->mode || $this->config->allowCircularCall) {
            $this->taskStorage->remove($completeAction->action->id, $completeAction->taskId);
            unset($this->retries[$completeAction->taskId]);
        }
    }

    private function createRetryTask(CompleteAction $completeAction): Task
    {
        $task = $this->taskStorage->get($completeAction->action->id, $completeAction->taskId);

        $now = new DateTimeImmutable();

        $retryTimestamp = $completeAction->action->retryDelay ? $now->add($completeAction->action->retryDelay) : $now;

        $task->setRetryTimestamp($retryTimestamp);
        $task->setStatus(TaskStatus::Retry);
        return $task;
    }

    public function removeHeldTask(string $taskId): void
    {
        unset($this->heldTasks[$taskId]);
    }

    public function reset(): void
    {
        $this->heldTasks = [];
        $this->retries = [];
        $this->alternates = [];
        $this->finalized = [];
    }
}
