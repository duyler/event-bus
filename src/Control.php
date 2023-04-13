<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscription;
use Duyler\EventBus\Enum\ResultStatus;

use function array_key_first;
use function array_key_last;
use function count;

class Control
{
    protected array $heldTasks = [];
    protected array $log = [];

    public function __construct(
        private readonly Validator $validator,
        private readonly Rollback  $rollback,
        private readonly Storage   $storage,
        private readonly TaskQueue $taskQueue,
    ) {
    }

    public function log(Task $task): void
    {
        $this->log[$task->action->id] = $task;
    }

    public function addSubscription(Subscription $subscription): void
    {
        $this->storage->subscription()->save($subscription);
    }

    public function subscriptionIsExists(Subscription $subscription): bool
    {
        return $this->storage->subscription()->isExists($subscription);
    }

    public function validateSubscriptions()
    {
        $this->validator->validateSubscriptions();
    }

    public function rollback(): void
    {
        $this->rollback->run();
    }

    public function addAction(Action $action): void
    {
        $this->storage->action()->save($action);
        $this->validator->validateAction($action);
    }

    public function getResult(string $actionId): Result
    {
        return $this->storage->task()->getResult($actionId);
    }

    public function resultIsExists(string $actionId): bool
    {
        return $this->storage->task()->isExists($actionId);
    }

    public function actionIsExists(string $actionId): bool
    {
        return $this->storage->action()->isExists($actionId);
    }

    public function getFirstAction(): string
    {
        return array_key_first($this->log);
    }

    public function getLastAction(): string
    {
        return array_key_last($this->log);
    }

    public function validateResultTask(Task $task): void
    {
        $this->validator->checkCyclicActionCalls($task);
    }

    public function resolveSubscriptions(string $actionId, ResultStatus $status): void
    {
        $subscriptions = $this->storage->subscription()->getSubscriptions($actionId, $status);

        foreach ($subscriptions as $subscription) {

            $action = $this->storage->action()->get($subscription->actionId);

            $this->doAction($action);
        }
    }

    public function doExistsAction(string $actionId): void
    {
        $action = $this->storage->action()->get($actionId);

        $this->doAction($action);
    }

    public function doAction(Action $action): void
    {
        if ($this->actionIsExists($action->id) === false) {
            $this->addAction($action);
        }

        $requiredIterator = new ActionRequiredIterator($action->required, $this->storage->action());

        foreach ($requiredIterator as $subject) {

            $requiredAction = $this->storage->action()->get($subject);

            if ($this->storage->task()->isExists($requiredAction->id)) {
                $result = $this->storage->task()->getResult($requiredAction->id);
                if ($result->status === ResultStatus::Success) {
                    continue;
                }
                break;
            }

            $this->pushTask(new Task($requiredAction));
        }

        $this->pushTask(new Task($action));
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

        $completeTasks = $this->storage->task()->getAllByRequired($task->action->required);

        /** @var Task $completeTask */
        foreach ($completeTasks as $completeTask) {
            if ($completeTask->result->status === ResultStatus::Fail) {
                return false;
            }
        }

        return count($completeTasks) === count($task->action->required);
    }
}
