<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Action\ActionRequiredIterator;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Dto\Subscribe;
use Duyler\EventBus\Enum\ResultStatus;

class Control
{
    protected array $heldTasks = [];

    public function __construct(
        private readonly Validator $validator,
        private readonly Rollback  $rollback,
        private readonly Storage   $storage,
        private readonly TaskQueue $taskQueue,
    ) {
    }

    public function addSubscribe(Subscribe $subscribe): void
    {
        $this->storage->subscribe()->save($subscribe);
    }

    public function validateSubscribers()
    {
        $this->validator->validateSubscribes();
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

    public function resolveSubscribers(string $actionId, ResultStatus $status): void
    {
        $subscribers = $this->storage->subscribe()->getSubscribers($actionId, $status);

        foreach ($subscribers as $subscribe) {

            $action = $this->storage->action()->get($subscribe->actionId);

            $this->resolveAction($action);
        }
    }

    public function resolveAction(Action $action): void
    {
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
