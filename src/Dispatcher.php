<?php

declare(strict_types=1);

namespace Konveyer\EventBus;

use Konveyer\EventBus\DTO\Subscribe;
use Konveyer\EventBus\Enum\ResultStatus;
use Konveyer\EventBus\Exception\CyclicCallActionException;
use Konveyer\EventBus\Storage\TaskStorage;
use Konveyer\EventBus\Storage\ActionStorage;
use Konveyer\EventBus\Storage\SubscribeStorage;

use function count;
use function key;

class Dispatcher
{
    protected TaskStorage $taskStorage;
    protected TaskQueue $taskQueue;
    protected SubscribeStorage $subscribeStorage;
    protected ActionStorage $actionStorage;
    protected array $heldTasks = [];
    protected array $mainEventLog = [];
    protected array $repeatedEventLog = [];

    public function __construct(
        TaskStorage $taskStorage,
        TaskQueue $taskQueue,
        SubscribeStorage $subscribeManager,
        ActionStorage $actionStorage
    ) {
        $this->taskQueue = $taskQueue;
        $this->taskStorage = $taskStorage;
        $this->subscribeStorage = $subscribeManager;
        $this->actionStorage = $actionStorage;
    }

    public function prepareStartedTask(string $startAction): void
    {
        $action = $this->actionStorage->get($startAction);

        $task = $this->createTask($action);

        $this->dispatchRequired($action);

        $this->dispatchTask($task);
    }

    public function dispatchResultEvent(Task $resultTask): void
    {
        $this->taskStorage->save($resultTask);

        $this->checkCyclicActionCalls($resultTask);

        $this->dispatchHeld();

        if (!$resultTask->subscribe?->silent) {
            $this->dispatchSubscribersTasks($resultTask);
        }
    }

    protected function checkCyclicActionCalls(Task $task): void
    {
        if ($this->taskStorage->isExists(ActionIdBuilder::byAction($task->action))) {

            if (in_array(ActionIdBuilder::byTask($task), $this->mainEventLog)) {
                $this->repeatedEventLog[] = ActionIdBuilder::byTask($task);
            } else {
                $this->mainEventLog[] = ActionIdBuilder::byTask($task);
            }

            if (count($this->mainEventLog) === count($this->repeatedEventLog)) {
                throw new CyclicCallActionException(
                    ActionIdBuilder::byAction($task->action),
                    $task->subscribe->subject ?? "started task"
                );
            }
        }
    }

    protected function dispatchSubscribersTasks(Task $resultTask): void
    {
        $subscribers = $this->subscribeStorage->getSubscribers(ActionIdBuilder::byTask($resultTask));

        foreach ($subscribers as $subscribe) {

            $action = $this->actionStorage->get($subscribe->actionFullName);

            $task = $this->createTask($action, $subscribe);

            $this->dispatchRequired($action);
            $this->dispatchTask($task);
        }
    }

    protected function dispatchRequired(Action $action): void
    {
        foreach ($action->require as $subject) {

            $requiredAction = $this->actionStorage->get($subject);
            
            $requiredTask = $this->createTask($requiredAction);

            if ($this->taskStorage->isExists(ActionIdBuilder::byAction($requiredTask->action))) {
                $result = $this->taskStorage->getResult(ActionIdBuilder::byAction($requiredTask->action));
                if ($result->status === ResultStatus::POSITIVE) {
                    continue;
                }
                break;
            }

            $this->dispatchTask($requiredTask);
            $this->dispatchRequired($requiredAction);
        }
    }

    protected function dispatchTask(Task $task): void
    {
        if ($this->isSatisfiedConditions($task)) {
            $this->taskQueue->add($task);
        } else {
            $this->heldTasks[ActionIdBuilder::byAction($task->action)] = $task;
        }
    }

    private function dispatchHeld(): void
    {
        foreach($this->heldTasks as $task) {
            if ($this->isSatisfiedConditions($task)) {
                $this->taskQueue->add($task);
                unset($this->heldTasks[key($this->heldTasks)]);
            }
        }
    }

    private function isSatisfiedConditions(Task $task): bool
    {
        if (empty($task->action->require)) {
            return true;
        }

        $completeTasks = $this->taskStorage->getAllByRequested($task->action->require);

        foreach ($completeTasks as $completeTask) {
            if ($completeTask->result->status === ResultStatus::NEGATIVE) {
                return false;
            }
        }

        return count($completeTasks) === count($task->action->require);
    }

    protected function createTask(Action $action, Subscribe $subscribe = null): Task
    {
        return new Task($action, $subscribe);
    }
}
