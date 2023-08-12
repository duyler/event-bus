<?php

declare(strict_types=1);

namespace Duyler\EventBus;

use Duyler\EventBus\Exception\CircularCallActionException;
use Duyler\EventBus\Exception\ConsecutiveRepeatedActionException;
use Duyler\EventBus\Service\SubscriptionService;
use Duyler\EventBus\Service\TaskService;

readonly class Dispatcher
{
    public function __construct(
        private TaskService         $taskService,
        private SubscriptionService $subscriptionService,
    ) {
    }

    /**
     * @throws ConsecutiveRepeatedActionException
     * @throws CircularCallActionException
     */
    public function dispatchResultTask(Task $resultTask): void
    {
        $this->taskService->saveResultTask($resultTask);
        $this->taskService->validateResultTask($resultTask);
        $this->subscriptionService->resolveSubscriptions($resultTask->action->id, $resultTask->result->status);
        $this->taskService->resolveHeldTasks();
    }
}
