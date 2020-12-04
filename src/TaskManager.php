<?php 

declare(strict_types=1);

namespace Jine\EventBus;

use Jine\EventBus\Dto\Result;
use Jine\EventBus\Dto\Service;
use Jine\EventBus\Dto\Task;
use Jine\EventBus\Contract\HandlerInterface;
use Closure;
use Throwable;

class TaskManager
{
    private ConfigProvider $config;
    private Rollback $rollback;
    private ResultStorage $resultStorage;
    private TaskStorage $taskStorage;
    private Container $container;
    private array $containers = [];
    
    public function __construct(
        ConfigProvider $config,
        Rollback $rollback,
        ResultStorage $resultStorage,
        TaskStorage $taskStorage
    ) {
        $this->config = $config;
        $this->rollback = $rollback;
        $this->resultStorage = $resultStorage;
        $this->taskStorage = $taskStorage;
        $this->container = new Container();
    }

    public function handle(Task $task, Closure $callback): void
    {
        try {
            $handler = new $task->handler;
            $service = $this->prepareService($handler, $task);
            $result = $this->run($handler, $task, $service);
            $callback($result);
        } catch(Throwable $exception) {
            $this->rollback($task, $exception);
            throw $exception;
        }
    }

    private function prepareService(HandlerInterface $handler, Task $task): Service
    {
        $this->containers[$task->serviceId] = clone $this->container;

        $prevResult = $this->resultStorage->getResult($task->subscribe);

        if ($prevResult->data !== null) {
            $this->containers[$task->serviceId]->set($prevResult->data);
        }

        $data = $this->resultStorage->getAllByArray($task->required);

        foreach ($data as $value) {
            $this->containers[$task->serviceId]->set($value);
        }

        $classMap = $handler->getClassMap();

        if (!empty($classMap)) {
            $this->containers[$task->serviceId]->setClassMap($classMap);
        }

        $service = $this->containers[$task->serviceId]->instance($handler->getClass());

        return $service;
    }

    private function run(HandlerInterface $handler, Task $task, Service $service): ?Result
    {
        $result = null;
        $result ??= $handler->run($service);

        if ($result !== null) {
            $this->resultStorage->save($task->serviceId . '.' . $task->action, $result);
        }

        return $result;
    }

    public function rollback(Task $task, \Throwable $exception): void
    {
        $this->taskStorage->save($task);

        $this->rollback->run($this->taskStorage->getAll());
    }

    public function registerSharedDefinitions(array $definitions): void
    {
        foreach ($definitions as $definition) {
            $this->container->set($definition);
        }
    }
}
