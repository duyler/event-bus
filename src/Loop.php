<?php 

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Task;

class Loop
{
    private TaskManager $taskManager;

    private TaskQueue $queue;
    
    private Task $currentTask;
    
    private bool $loopStarted = false;

    private \Closure $callback;

    public function __construct(TaskManager $taskManager)
    {
        $this->taskManager = $taskManager;
        
        $this->queue = new TaskQueue();
        $this->queue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
    }
    
    public function addTask(Task $task): void
    {
        $this->queue->addTask($task);
    }
    
    public function getCurrentTask(): Task
    {
        return $this->currentTask;
    }
    
    public function run($callback): void
    {
        $this->callback = $callback;

        if ($this->loopStarted) {
            // Исключение
        }
    
        if ($this->queue->isEmpty()) {
            // Исключение
        }
        
        $this->currentTask = $this->queue->dequeue();
        $this->loopStarted = true;
        $this->taskManager->handle($this->currentTask, $this->callback);
    }
    
    public function next(): void
    {
        if (!$this->queue->isEmpty()) {
            $this->currentTask = $this->queue->dequeue();
            $this->taskManager->handle($this->currentTask, $this->callback);
        } else {
            $this->loopStarted = false;
        }
    }

    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }

    public function started(): bool
    {
        return $this->loopStarted;
    }
}
