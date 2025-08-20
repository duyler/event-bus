<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use DateTimeImmutable;
use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Enum\TaskStatus;
use Duyler\EventBus\Exception\UnableToContinueWithFailActionException;
use Duyler\EventBus\Internal\Event\TaskUnresolvedEvent;
use Duyler\EventBus\Storage\ActionStorage;
use Duyler\EventBus\Storage\CompleteActionStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\TaskStorage;
use Psr\EventDispatcher\EventDispatcherInterface;

#[Finalize(method: 'reset')]
final class Bus
{
    /** @var array<string, Task> */
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
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * Processes an action by checking conditions and adding to task queue
     */
    public function doAction(Action $action): void
    {
        if (false === $this->canExecuteAction($action)) {
            return;
        }

        $this->processActionRequirements($action);
        $this->pushTask($this->createPrimaryTask($action));
    }

    /**
     * Attempts to move held tasks to the main queue if conditions are satisfied
     */
    public function resolveHeldTasks(): void
    {
        foreach ($this->heldTasks as $key => $task) {
            if (true === $this->isSatisfiedConditions($task)) {
                $this->promoteTaskToQueue($task, $key);
            }
        }
    }

    /**
     * Handles completion of an action (success or failure)
     */
    public function afterCompleteAction(CompleteAction $completeAction): void
    {
        if (ResultStatus::Success === $completeAction->result->status) {
            $this->finalizeSuccessfulAction($completeAction);
            return;
        }

        $this->handleFailedAction($completeAction);
    }

    /**
     * Removes a specific task from held tasks
     */
    public function removeHeldTask(string $taskId): void
    {
        unset($this->heldTasks[$taskId]);
    }

    /**
     * Resets internal state
     */
    public function reset(): void
    {
        $this->heldTasks = [];
        $this->retries = [];
        $this->alternates = [];
        $this->finalized = [];
    }

    /**
     * Checks if an action can be executed based on events and repeatability
     */
    private function canExecuteAction(Action $action): bool
    {
        return $this->isSatisfiedEvents($action)
            && (false === $this->isRepeat($action->getId()) || $action->isRepeatable());
    }

    /**
     * Checks if an action has already been executed or is in progress
     */
    private function isRepeat(string $actionId): bool
    {
        $isInHeldTasks = array_any($this->heldTasks, fn($task) => $task->action->getId() === $actionId);
        return $isInHeldTasks
            || $this->taskQueue->inQueue($actionId)
            || $this->completeActionStorage->isExists($actionId);
    }

    /**
     * Processes all required actions for the given action
     */
    private function processActionRequirements(Action $action): void
    {
        $requiredIterator = new ActionRequiredIterator(
            $action->getRequired(),
            $this->actionStorage->getAll(),
        );

        /** @var string $subject */
        foreach ($requiredIterator as $subject) {
            $requiredAction = $this->actionStorage->get($subject);

            if (false === $this->canExecuteRequiredAction($requiredAction)) {
                continue;
            }

            $this->pushTask($this->createPrimaryTask($requiredAction));
        }
    }

    /**
     * Checks if a required action can be executed
     */
    private function canExecuteRequiredAction(Action $action): bool
    {
        return (false === $this->isRepeat($action->getId())
            || $action->isRepeatable())
            && 0 === count($action->getListen());
    }

    /**
     * Creates a primary task for an action
     */
    private function createPrimaryTask(Action $action): Task
    {
        $task = new Task($action);
        $task->setStatus(TaskStatus::Primary);
        $this->taskStorage->add($task);
        return $task;
    }

    /**
     * Either queues a task or holds it based on conditions
     */
    private function pushTask(Task $task): void
    {
        if (true === $this->isSatisfiedConditions($task)) {
            $this->enqueueTask($task);
        } else {
            $this->heldTask($task);
        }
    }

    /**
     * Adds a task to the execution queue
     */
    private function enqueueTask(Task $task): void
    {
        $this->taskQueue->push($task);
        $this->retries[$task->getId()] = 0;
        $this->finalized[$task->action->getId()] = false;
    }

    /**
     * Task on held
     */
    private function heldTask(Task $task): void
    {
        $task->setStatus(TaskStatus::Held);
        $this->heldTasks[$task->getId()] = $task;
    }

    /**
     * Moves a task from held to queue
     */
    private function promoteTaskToQueue(Task $task, string $key): void
    {
        $task->setStatus(TaskStatus::Primary);
        $this->taskQueue->push($task);
        unset($this->heldTasks[$key]);
    }

    /**
     * Checks if all required events for an action are satisfied
     */
    private function isSatisfiedEvents(Action $action): bool
    {
        return array_all(
            $action->getListen(),
            fn($eventId) => false !== $this->eventRelationStorage->isExists($eventId),
        );
    }

    /**
     * Checks if all conditions for task execution are satisfied
     */
    private function isSatisfiedConditions(Task $task): bool
    {
        if (true === $this->isLocked($task)) {
            return false;
        }

        if (false === $this->hasAllDependenciesCompleted($task)) {
            return false;
        }

        return $this->hasRequiredActionsCompleted($task);
    }

    /**
     * Checks if a task is locked from execution
     */
    private function isLocked(Task $task): bool
    {
        if (false === $task->action->isLock()) {
            return false;
        }

        if (true === $this->taskQueue->inQueue($task->action->getId())) {
            return true;
        }

        $otherTasks = $this->taskStorage->getAllByActionId($task->action->getId());
        unset($otherTasks[$task->getId()]);
        return array_any($otherTasks, fn($otherTask) => $this->isTaskBlocking($otherTask));
    }

    /**
     * Checks if another task is blocking execution
     */
    private function isTaskBlocking(Task $otherTask): bool
    {
        return (TaskStatus::Primary === $otherTask->getStatus()
            && array_key_exists($otherTask->getId(), $this->heldTasks))
            || ($this->retries[$otherTask->action->getId()] ?? 0) < $otherTask->action->getRetries();
    }

    /**
     * Checks if all task dependencies are completed
     */
    private function hasAllDependenciesCompleted(Task $task): bool
    {
        $completedCount = count($this->completeActionStorage->getAllAllowedByTypeArray(
            $task->action->getDependsOn(),
            $task->action->getId(),
        ));

        return $completedCount >= count($task->action->getDependsOn());
    }

    /**
     * Checks if all required actions are completed
     */
    private function hasRequiredActionsCompleted(Task $task): bool
    {
        if (0 === $task->action->getRequired()->count()) {
            return true;
        }

        $requiredArray = $task->action->getRequired()->getArrayCopy();
        $completedRequirements = $this->completeActionStorage->getAllByArray($requiredArray);

        if (count($completedRequirements) < $task->action->getRequired()->count()) {
            return false;
        }

        // Check for failed actions that haven't been finalized yet
        foreach ($completedRequirements as $completeRequiredAction) {
            if (ResultStatus::Fail === $completeRequiredAction->result->status) {
                if (($this->retries[$completeRequiredAction->taskId] ?? 0)
                    < $completeRequiredAction->action->getRetries()
                ) {
                    return false;
                }

                if (false === ($this->finalized[$completeRequiredAction->action->getId()] ?? false)) {
                    return false;
                }
            }
        }

        return $this->handleFailedRequirements($task, $completedRequirements);
    }

    /**
     * Handles failed required actions and attempts alternates
     *
     * @param array<CompleteAction> $completedRequirements
     */
    private function handleFailedRequirements(Task $task, array $completedRequirements): bool
    {
        $failActions = array_filter(
            $completedRequirements,
            fn(CompleteAction $ca) => ResultStatus::Fail === $ca->result->status,
        );

        if (0 === count($failActions)) {
            return true;
        }

        $replacedCount = 0;
        foreach ($failActions as $failAction) {
            $this->alternates[$failAction->action->getId()] = $failAction->action->getAlternates();

            if (true === $this->tryReplaceFailedAction($failAction->action->getId())) {
                $replacedCount++;
            }
        }

        if ($replacedCount === count($failActions)) {
            return true;
        }

        return $this->handleUnresolvedFailures($task, $failActions);
    }

    /**
     * Attempts to replace a failed action with alternates
     */
    private function tryReplaceFailedAction(string $failActionId): bool
    {
        foreach ($this->alternates[$failActionId] as $alternateId) {
            $alternate = $this->actionStorage->get($alternateId);

            if (true === $this->completeActionStorage->isExists($alternateId)) {
                $completeAction = $this->completeActionStorage->get($alternateId);
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

    /**
     * Handles unresolved action failures
     *
     * @param array<CompleteAction> $failActions
     */
    private function handleUnresolvedFailures(Task $task, array $failActions): bool
    {
        if (true === $this->hasPendingAlternates($failActions)) {
            return false;
        }

        $this->eventDispatcher->dispatch(new TaskUnresolvedEvent($task));

        if (true === $this->config->allowSkipUnresolvedActions) {
            unset($this->heldTasks[$task->getId()]);
            return false;
        }

        throw new UnableToContinueWithFailActionException($task->action->getId());
    }

    /**
     * Checks if there are pending alternate actions
     *
     * @param array<CompleteAction> $failActions
     */
    private function hasPendingAlternates(array $failActions): bool
    {
        foreach ($failActions as $failAction) {
            foreach ($this->alternates[$failAction->action->getId()] as $alternate) {
                if ($this->taskQueue->inQueue($alternate)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Finalizes a successfully completed action
     */
    private function finalizeSuccessfulAction(CompleteAction $completeAction): void
    {
        $this->finalized[$completeAction->action->getId()] = true;
        $this->removeTask($completeAction);
    }

    /**
     * Handles a failed action (retry or finalize)
     */
    private function handleFailedAction(CompleteAction $completeAction): void
    {
        if ($this->retries[$completeAction->taskId] < $completeAction->action->getRetries()) {
            $this->retryTask($completeAction);
        } else {
            $this->finalized[$completeAction->action->getId()] = true;
            $this->removeTask($completeAction);
        }
    }

    /**
     * Retries a failed task
     */
    private function retryTask(CompleteAction $completeAction): void
    {
        $this->taskQueue->push($this->createRetryTask($completeAction));
        ++$this->retries[$completeAction->taskId];
    }

    /**
     * Removes a task from storage
     */
    private function removeTask(CompleteAction $completeAction): void
    {
        if (Mode::Loop === $this->config->mode || $this->config->allowCircularCall) {
            $this->taskStorage->remove($completeAction->action->getId(), $completeAction->taskId);
            unset($this->retries[$completeAction->taskId]);
        }
    }

    /**
     * Creates a retry task for a failed action
     */
    private function createRetryTask(CompleteAction $completeAction): Task
    {
        $task = $this->taskStorage->get($completeAction->action->getId(), $completeAction->taskId);
        $task->setRetryTimestamp($this->calculateRetryTimestamp($completeAction));
        $task->setStatus(TaskStatus::Retry);
        return $task;
    }

    /**
     * Calculates retry timestamp for a task
     */
    private function calculateRetryTimestamp(CompleteAction $completeAction): DateTimeImmutable
    {
        $now = new DateTimeImmutable();
        return $completeAction->action->getRetryDelay()
            ? $now->add($completeAction->action->getRetryDelay())
            : $now;
    }
}
