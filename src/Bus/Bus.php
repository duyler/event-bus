<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use DateTimeImmutable;
use Duyler\DI\Attribute\Finalize;
use Duyler\EventBus\BusConfig;
use Duyler\EventBus\Enum\Mode;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Enum\TaskStatus;
use Duyler\EventBus\Exception\UnableToContinueWithFailActorException;
use Duyler\EventBus\Internal\Event\TaskUnresolvedEvent;
use Duyler\EventBus\Storage\ActorStorage;
use Duyler\EventBus\Storage\CompleteActorStorage;
use Duyler\EventBus\Storage\EventRelationStorage;
use Duyler\EventBus\Storage\TaskStorage;
use Psr\EventDispatcher\EventDispatcherInterface;

use function array_filter;
use function count;

#[Finalize(method: 'reset')]
final class Bus
{
    /** @var array<string, Task> */
    private array $heldTasks = [];

    /** @var array<string, string[]> */
    private array $fallbacks = [];

    /** @var array<string, int> */
    private array $retries = [];

    /** @var array<string, bool> */
    private array $finalized = [];

    public function __construct(
        private readonly TaskQueue $taskQueue,
        private readonly ActorStorage $actorStorage,
        private readonly CompleteActorStorage $completeActorStorage,
        private readonly BusConfig $config,
        private readonly EventRelationStorage $eventRelationStorage,
        private readonly TaskStorage $taskStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SubscriptionChecker $subscriptionChecker,
    ) {}

    /**
     * Processes an actor by checking conditions and adding to task queue
     */
    public function doActor(Actor $actor, string $scope): void
    {
        if (false === $this->canExecuteActor($actor)) {
            return;
        }

        $this->processActorRequirements($actor, $scope);
        $this->pushTask($this->createPrimaryTask($actor, $scope));
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
     * Handles completion of an actor (success or failure)
     */
    public function afterCompleteActor(CompleteActor $completeActor): void
    {
        if (ResultStatus::Success === $completeActor->result->status) {
            $this->finalizeSuccessfulActor($completeActor);
            return;
        }

        $this->handleFailedActor($completeActor);
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
        $this->fallbacks = [];
        $this->finalized = [];
    }

    /**
     * Checks if an actor can be executed based on events and repeatability
     */
    private function canExecuteActor(Actor $actor): bool
    {
        return $this->isSatisfiedEvents($actor)
            && (false === $this->isRepeat($actor->getId()) || $actor->isRepeatable());
    }

    /**
     * Checks if an actor has already been executed or is in progress
     */
    private function isRepeat(string $actorId): bool
    {
        $isInHeldTasks = array_any($this->heldTasks, fn($task) => $task->actor->getId() === $actorId);
        return $isInHeldTasks
            || $this->taskQueue->inQueue($actorId)
            || $this->completeActorStorage->isExists($actorId);
    }

    /**
     * Processes all required actors for the given actor
     */
    private function processActorRequirements(Actor $actor, string $scope): void
    {
        $requiredIterator = new ActorRequiredIterator(
            $actor->getRequired(),
            $this->actorStorage->getAll(),
        );

        /** @var string $subject */
        foreach ($requiredIterator as $subject) {
            $requiredActor = $this->actorStorage->get($subject);

            if (false === $this->canExecuteRequiredActor($requiredActor)) {
                continue;
            }

            $this->pushTask($this->createPrimaryTask($requiredActor, $scope));
        }
    }

    /**
     * Checks if a required actor can be executed
     */
    private function canExecuteRequiredActor(Actor $actor): bool
    {
        return (false === $this->isRepeat($actor->getId())
            || $actor->isRepeatable())
            && 0 === count($actor->getSubscriptionEvents());
    }

    /**
     * Creates a primary task for an actor
     */
    private function createPrimaryTask(Actor $actor, string $scope): Task
    {
        $task = new Task($actor, $scope);
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
        $this->finalized[$task->actor->getId() . '.' . $task->getScope()] = false;
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
     * Checks if all required events for an actor are satisfied
     */
    private function isSatisfiedEvents(Actor $actor): bool
    {
        return array_all(
            $actor->getSubscriptionEvents(),
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

        if (false === $this->subscriptionChecker->isSatisfied($task->actor)) {
            return false;
        }

        return $this->hasRequiredActorsCompleted($task);
    }

    /**
     * Checks if a task is locked from execution
     */
    private function isLocked(Task $task): bool
    {
        if (false === $task->actor->isLock()) {
            return false;
        }

        if ($this->taskQueue->inQueue($task->actor->getId())) {
            return true;
        }

        $otherTasks = $this->taskStorage->getAllByActorId($task->actor->getId());
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
            || ($this->retries[$otherTask->actor->getId()] ?? 0) < $otherTask->actor->getRetries();
    }

    /**
     * Checks if all required actors are completed
     */
    private function hasRequiredActorsCompleted(Task $task): bool
    {
        if (0 === $task->actor->getRequired()->count()) {
            return true;
        }

        $requiredArray = $task->actor->getRequired()->getArrayCopy();
        $completedRequirements = $this->completeActorStorage->getAllByArray($requiredArray);

        if (count($completedRequirements) < $task->actor->getRequired()->count()) {
            return false;
        }

        // Check for failed actors that haven't been finalized yet
        foreach ($completedRequirements as $completeRequiredActor) {
            if (ResultStatus::Fail === $completeRequiredActor->result->status) {
                if (($this->retries[$completeRequiredActor->taskId] ?? 0)
                    < $completeRequiredActor->actor->getRetries()
                ) {
                    return false;
                }

                if (false === ($this->finalized[$completeRequiredActor->actor->getId() . '.' . $task->getScope()] ?? false)) {
                    return false;
                }
            }
        }

        return $this->handleFailedRequirements($task, $completedRequirements);
    }

    /**
     * Handles failed required actors and attempts fallbacks
     *
     * @param array<CompleteActor> $completedRequirements
     */
    private function handleFailedRequirements(Task $task, array $completedRequirements): bool
    {
        $failActors = array_filter(
            $completedRequirements,
            fn(CompleteActor $ca) => ResultStatus::Fail === $ca->result->status,
        );

        if (0 === count($failActors)) {
            return true;
        }

        $replacedCount = 0;
        foreach ($failActors as $failActor) {
            $this->fallbacks[$failActor->actor->getId() . '.' . $task->getScope()] = $failActor->actor->getFallbacks();

            if (true === $this->tryReplaceFailedActor($failActor->actor->getId(), $task->getScope())) {
                $replacedCount++;
            }
        }

        if ($replacedCount === count($failActors)) {
            return true;
        }

        return $this->handleUnresolvedFailures($task, $failActors);
    }

    /**
     * Attempts to replace a failed actor with fallback
     */
    private function tryReplaceFailedActor(string $failActorId, string $scope): bool
    {
        foreach ($this->fallbacks[$failActorId . '.' . $scope] as $fallbackId) {
            $fallback = $this->actorStorage->get($fallbackId);

            if (true === $this->completeActorStorage->isExists($fallbackId, $scope)) {
                $completeActor = $this->completeActorStorage->get($fallbackId, $scope);
                if (ResultStatus::Success === $completeActor->result->status) {
                    return true;
                }
                continue;
            }

            $this->doActor($fallback, $scope);
            return false;
        }

        return false;
    }

    /**
     * Handles unresolved actor failures
     *
     * @param array<CompleteActor> $failActors
     */
    private function handleUnresolvedFailures(Task $task, array $failActors): bool
    {
        if (true === $this->hasPendingFallbacks($failActors, $task->getScope())) {
            return false;
        }

        $this->eventDispatcher->dispatch(new TaskUnresolvedEvent($task));

        if (true === $this->config->allowSkipUnresolvedActors) {
            unset($this->heldTasks[$task->getId()]);
            return false;
        }

        throw new UnableToContinueWithFailActorException($task->actor->getId());
    }

    /**
     * Checks if there are pending fallback actors
     *
     * @param array<CompleteActor> $failActors
     */
    private function hasPendingFallbacks(array $failActors, string $scope): bool
    {
        foreach ($failActors as $failActor) {
            foreach ($this->fallbacks[$failActor->actor->getId() . '.' . $scope] as $fallback) {
                if ($this->taskQueue->inQueue($fallback)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Finalizes a successfully completed actor
     */
    private function finalizeSuccessfulActor(CompleteActor $completeActor): void
    {
        $this->finalized[$completeActor->actor->getId() . '.' . $completeActor->scope] = true;
        $this->removeTask($completeActor);
    }

    /**
     * Handles a failed actor (retry or finalize)
     */
    private function handleFailedActor(CompleteActor $completeActor): void
    {
        if ($this->retries[$completeActor->taskId] < $completeActor->actor->getRetries()) {
            $this->retryTask($completeActor);
        } else {
            $this->finalized[$completeActor->actor->getId() . '.' . $completeActor->scope] = true;
            $this->removeTask($completeActor);
        }
    }

    /**
     * Retries a failed task
     */
    private function retryTask(CompleteActor $completeActor): void
    {
        $this->taskQueue->push($this->createRetryTask($completeActor));
        ++$this->retries[$completeActor->taskId];
    }

    /**
     * Removes a task from storage
     */
    private function removeTask(CompleteActor $completeActor): void
    {
        if (Mode::Loop === $this->config->mode || $this->config->allowCircularCall) {
            $this->taskStorage->remove($completeActor->actor->getId(), $completeActor->taskId);
            unset($this->retries[$completeActor->taskId]);
        }
    }

    /**
     * Creates a retry task for a failed actor
     */
    private function createRetryTask(CompleteActor $completeActor): Task
    {
        $task = $this->taskStorage->get($completeActor->actor->getId(), $completeActor->taskId);
        $task->setRetryTimestamp($this->calculateRetryTimestamp($completeActor));
        $task->setStatus(TaskStatus::Retry);
        return $task;
    }

    /**
     * Calculates retry timestamp for a task
     */
    private function calculateRetryTimestamp(CompleteActor $completeActor): DateTimeImmutable
    {
        $now = new DateTimeImmutable();
        return $completeActor->actor->getRetryDelay()
            ? $now->add($completeActor->actor->getRetryDelay())
            : $now;
    }
}
