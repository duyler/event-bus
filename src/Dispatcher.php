<?php 

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Action;
use Jine\EventBus\Dto\Result;
use Jine\EventBus\Dto\Task;

use function array_flip;
use function array_intersect_key;
use function count;
use function key;

class Dispatcher
{
    private TaskFactory $taskFactory;
    private TaskStorage $taskStorage;
    private ConfigProvider $config;
    private Loop $loop;
    private SubscribeStorage $subscribeStorage;
    private ServiceStorage $serviceStorage;
    private ActionStorage $actionStorage;
    private array $heldTasks = [];
    
    public function __construct(
        TaskFactory $taskFactory,
        TaskStorage $taskStorage,
        Loop $loop,
        ConfigProvider $config,
        SubscribeStorage $subscribeManager,
        ServiceStorage $serviceStorage,
        ActionStorage $actionStorage
    ) {
        $this->loop = $loop;
        $this->taskFactory = $taskFactory;
        $this->taskStorage = $taskStorage;
        $this->config = $config;
        $this->subscribeStorage = $subscribeManager;
        $this->serviceStorage = $serviceStorage;
        $this->actionStorage = $actionStorage;
    }

    public function startLoop(string $startAction): void
    {
        $action = $this->actionStorage->get($startAction);

        $task = $this->taskFactory->create($action);

        $this->dispatchRequired($task);

        $this->loop->addTask($task);

        $this->loop->run(
            function ($result) {
                if ($result === null) {
                    $this->loop->next();
                } else {
                    $this->dispatchResultEvent($result);
                }
            }
        );
    }

    public function dispatchResultEvent(Result $result): void
    {
        $resultTask = $this->loop->getCurrentTask();

        if ($result->status === Result::STATUS_SUCCESS) {
            $this->taskStorage->save($resultTask);
        }

        $this->dispatchHeld();

        $this->dispatchSubscribersTasks($result, $resultTask);
        
        $this->loop->next();
    }

    private function dispatchSubscribersTasks(Result $result, Task $resultTask): void
    {
        $subject = $resultTask->serviceId . '.' . $resultTask->action . '.' . $result->status;

        $subscribers = $this->subscribeStorage->getSubscribers($subject);

        if (!empty($subscribers)) {
            foreach ($subscribers as $subscribe) {

                $action = $this->actionStorage->get($subscribe->actionFullName);

                $task = $this->taskFactory->create($action, $subscribe);

                $this->dispatchRequired($task);

                $this->dispatchTask($task);
            }
        }
    }

    private function dispatchRequired(Task $task): void
    {
        if (empty($task->required)) {
            return;
        }

        foreach ($task->required as $subject) {

            $serviceAction = $this->actionStorage->get($subject);

            $this->prepareRequiredTasks($serviceAction,);
        }
    }

    private function prepareRequiredTasks(Action $action): void
    {
        if ($this->taskStorage->isExists($action->serviceId . '.' . $action->name)) {
            return;
        }

        $task = $this->taskFactory->create($action);

        $this->dispatchTask($task);

        $this->dispatchRequired($task);
    }

    private function dispatchTask(Task $task): void
    {
        if ($this->isSatisfied($task)) {
            $this->loop->addTask($task);
        } else {
            $this->heldTasks[$task->serviceId . '.' . $task->action] = $task;
        }
    }

    private function dispatchHeld()
    {
        foreach($this->heldTasks as $task) {
            if ($this->isSatisfied($task)) {
                $this->loop->addTask($task);
                unset($this->heldTasks[key($this->heldTasks)]);
            }
        }
    }

    private function isSatisfied(Task $task): bool
    {
        $completeTasks = $this->taskStorage->getAll();

        $requiredTasksData = array_intersect_key(array_flip($task->required), $completeTasks);

        return count($requiredTasksData) === count($task->required);
    }
}
