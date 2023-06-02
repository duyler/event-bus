<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Collector\ActionCollector;
use Duyler\EventBus\Collector\SubscriptionCollector;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;

use function array_key_first;
use function array_key_last;
use function count;

class BusService
{
    protected array $heldTasks = [];
    protected array $log = [];
    private array $mainEventLog = [];
    private array $repeatedEventLog = [];

    public function __construct(
        private readonly Rollback             $rollback,
        private readonly Collections          $collections,
        private readonly TaskQueue            $taskQueue,
        private readonly ActionCollector      $actionCollector,
        private readonly SubscriptionCollector $subscriptionCollector
    ) {
    }

    public function log(Task $task): void
    {
        $this->log[] = $task->action->id;
    }

    public function addSubscription(Subscription $subscription): void
    {
        $this->subscriptionCollector->add($subscription);
    }

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->collections->subscription()->isExists($subscription);
    }

    public function rollbackWithoutException(int $step = 0): void
    {
        $this->rollback->run($step > 0 ? array_slice($this->log, -1, $step) : []);
    }

    public function addAction(Action $action): void
    {
        $this->actionCollector->add($action);
    }

    public function getResult(string $actionId): Result
    {
        return $this->collections->task()->getResult($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->collections->task()->isExists($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->collections->action()->isExists($actionId);
    }

    public function getFirstAction(): string
    {
        return $this->log[array_key_first($this->log)];
    }

    public function getLastAction(): string
    {
        return $this->log[array_key_last($this->log)];
    }

    public function validateResultTask(Task $task): void
    {
        if ($this->collections->task()->isExists($task->action->id)) {

            $actionId = $task->action->id . '.' . $task->result->status->value;

            if (in_array($actionId, $this->mainEventLog)) {
                $this->repeatedEventLog[] = $actionId;
            } else {
                $this->mainEventLog[] = $actionId;
            }

            if (end($this->repeatedEventLog) === $actionId) {
                throw new ConsecutiveRepeatedActionException(
                    $task->action->id,
                    $task->result->status->value
                );
            }

            if (count($this->mainEventLog) === count($this->repeatedEventLog)) {
                throw new CircularCallActionException(
                    $task->action->id,
                    end($this->mainEventLog)
                );
            }
        }
    }

    public function resolveSubscriptions(string $actionId, ResultStatus $status): void
    {
        $subscriptions = $this->collections->subscription()->getSubscriptions($actionId, $status);

        foreach ($subscriptions as $subscription) {

            $action = $this->collections->action()->get($subscription->actionId);

            $this->doAction($action);
        }
    }

    public function doExistsAction(string $actionId): void
    {
        $action = $this->collections->action()->get($actionId);

        $this->doAction($action);
    }

    public function doAction(Action $action): void
    {
        if ($this->actionIsExists($action->id) === false) {
            $this->addAction($action);
        }

        $requiredIterator = new ActionRequiredIterator($action->required, $this->collections->action());

        foreach ($requiredIterator as $subject) {

            $requiredAction = $this->collections->action()->get($subject);

            if ($this->collections->task()->isExists($requiredAction->id)) {
                $result = $this->collections->task()->getResult($requiredAction->id);
                if ($result->status === ResultStatus::Success) {
                    continue;
                }
                break;
            }

            $this->pushTask($this->createTask($requiredAction));
        }

        $this->pushTask($this->createTask($action));
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
        /** @var Task $task */
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

        $completeTasks = $this->collections->task()->getAllByArray($task->action->required->getArrayCopy());

        /** @var Task $completeTask */
        foreach ($completeTasks as $completeTask) {
            if ($completeTask->result->status === ResultStatus::Fail) {
                return false;
            }
        }

        return count($completeTasks) === count($task->action->required);
    }
}
