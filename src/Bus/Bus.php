<?php

declare(strict_types=1);

namespace Duyler\EventBus\Bus;

use function count;

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
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function doAction(Action $action): void
    {
        if (false === $this->isSatisfiedEvents($action)) {
            return;
        }

        if ($this->isRepeat($action->getId()) && false === $action->isRepeatable()) {
            return;
        }

        $requiredIterator = new ActionRequiredIterator($action->getRequired(), $this->actionStorage->getAll());

        /** @var string $subject */
        foreach ($requiredIterator as $subject) {
            $requiredAction = $this->actionStorage->get($subject);

            if ($this->isRepeat($requiredAction->getId()) && false === $requiredAction->isRepeatable()) {
                continue;
            }

            if (0 < count($requiredAction->getListen())) {
                continue;
            }

            $this->pushTask($this->createPrimaryTask($requiredAction));
        }

        $this->pushTask($this->createPrimaryTask($action));
    }

    private function isRepeat(string $actionId): bool
    {
        foreach ($this->heldTasks as $task) {
            if ($task->action->getId() === $actionId) {
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
            $this->finalized[$task->action->getId()] = false;
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
        foreach ($action->getListen() as $eventId) {
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

        if (0 === $task->action->getRequired()->count()) {
            return true;
        }

        $requiredArray = $task->action->getRequired()->getArrayCopy();
        $completeRequiredActions = $this->completeActionStorage->getAllByArray($requiredArray);

        if (count($completeRequiredActions) < $task->action->getRequired()->count()) {
            return false;
        }

        /** @var CompleteAction[] $failActions */
        $failActions = [];
        /** @var CompleteAction[] $replacedActions */
        $replacedActions = [];

        foreach ($completeRequiredActions as $completeRequiredAction) {
            if (ResultStatus::Fail === $completeRequiredAction->result->status) {
                if ($this->retries[$completeRequiredAction->taskId] < $completeRequiredAction->action->getRetries()) {
                    return false;
                }

                if (false === $this->finalized[$completeRequiredAction->action->getId()]) {
                    return false;
                }

                $failActions[] = $completeRequiredAction;
            }
        }

        foreach ($failActions as $failAction) {
            $this->alternates[$failAction->action->getId()] = $failAction->action->getAlternates();

            if ($this->tryReplacedFailAction($failAction->action->getId())) {
                $replacedActions[] = $failAction;
            }
        }

        if (count($failActions) > count($replacedActions)) {
            foreach ($failActions as $failAction) {
                foreach ($this->alternates[$failAction->action->getId()] as $alternate) {
                    if ($this->taskQueue->inQueue($alternate)) {
                        return false;
                    }
                }
            }

            $this->eventDispatcher->dispatch(new TaskUnresolvedEvent($task));

            if ($this->config->allowSkipUnresolvedActions) {
                unset($this->heldTasks[$task->getId()]);
                return false;
            }

            throw new UnableToContinueWithFailActionException($task->action->getId());
        }

        return true;
    }

    private function isLock(Task $task): bool
    {
        if (false === $task->action->isLock()) {
            return false;
        }

        if ($this->taskQueue->inQueue($task->action->getId())) {
            return true;
        }

        $tasks = $this->taskStorage->getAllByActionId($task->action->getId());

        unset($tasks[$task->getId()]);

        foreach ($tasks as $currentTask) {
            if (TaskStatus::Primary === $task->getStatus()) {
                if (array_key_exists($currentTask->getId(), $this->heldTasks)) {
                    return true;
                }
            }

            if (($this->retries[$currentTask->action->getId()] ?? 0) < $task->action->getRetries()) {
                return true;
            }
        }

        return false;
    }

    private function tryReplacedFailAction(string $failActionId): bool
    {
        foreach ($this->alternates[$failActionId] as $actionId) {
            $alternate = $this->actionStorage->get($actionId);

            if ($this->completeActionStorage->isExists($alternate->getId())) {
                $completeAction = $this->completeActionStorage->get($alternate->getId());
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
            $this->finalized[$completeAction->action->getId()] = true;
            $this->removeTask($completeAction);
            return;
        }

        if ($this->retries[$completeAction->taskId] < $completeAction->action->getRetries()) {
            $this->taskQueue->push($this->createRetryTask($completeAction));
            ++$this->retries[$completeAction->taskId];
        } else {
            $this->finalized[$completeAction->action->getId()] = true;
            $this->removeTask($completeAction);
        }
    }

    private function removeTask(CompleteAction $completeAction): void
    {
        if (Mode::Loop === $this->config->mode || $this->config->allowCircularCall) {
            $this->taskStorage->remove($completeAction->action->getId(), $completeAction->taskId);
            unset($this->retries[$completeAction->taskId]);
        }
    }

    private function createRetryTask(CompleteAction $completeAction): Task
    {
        $task = $this->taskStorage->get($completeAction->action->getId(), $completeAction->taskId);

        $now = new DateTimeImmutable();

        $retryTimestamp = $completeAction->action->getRetryDelay()
            ? $now->add($completeAction->action->getRetryDelay())
            : $now;

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
