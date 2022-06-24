<?php

declare(strict_types=1);

namespace Konveyer\EventBus;

use Closure;
use Konveyer\EventBus\Container;
use Konveyer\EventBus\Contract\AroundAdviceInterface;
use Konveyer\EventBus\DTO\Result;
use Konveyer\EventBus\Enum\ResultStatus;
use Konveyer\EventBus\Storage\ContainerStorage;
use Konveyer\EventBus\Storage\TaskStorage;

class ActionHandler
{
    private Action $action;
    private TaskStorage $taskStorage;
    private ContainerStorage $containerStorage;
    private array $arguments;
    private object $actionInstance;
    private array $before;
    private array $after;
    private null | AroundAdviceInterface | Closure $around;

    public function __construct(
        ContainerStorage $containerStorage,
        TaskStorage $taskStorage
    ) {
        $this->containerStorage = $containerStorage;
        $this->taskStorage = $taskStorage;
    }

    public function handle(): Result
    {
        $this->runBefore();

        $resultData = $this->runAction();

        $this->runAfter();

        if (empty($resultData) && !$this->action->void) {
            return new Result(ResultStatus::NEGATIVE);
        }

        return new Result(ResultStatus::POSITIVE, $resultData);
    }

    private function runAction(): mixed
    {
        if (empty($this->around)) {
            return ($this->actionInstance)(...$this->arguments);
        }

        return ($this->around)(...$this->arguments);
    }

    private function runBefore(): void
    {
        foreach ($this->before as $advice) {
            $advice(...$this->arguments);
        }
    }

    private function runAfter(): void
    {
        foreach ($this->after as $advice) {
            $advice(...$this->arguments);
        }
    }

    public function prepare(Action $action): void
    {
        $this->action = $action;
        
        $container = new Container();
        $container->setClassMap($action->classMap);
        $this->containerStorage->save(ActionIdBuilder::byAction($action), $container);

        $this->prepareResults($container);
        $this->prepareArguments($container);
        $this->prepareAspects($container);
        $this->prepareAction($container);
    }

    private function prepareResults(Container $container): void
    {
        $completeTasks = $this->taskStorage->getAllByRequested($this->action->require);

        foreach ($completeTasks as $task) {
            if (is_object($task->result->data)) {
                $container->set($task->result->data);
            }
        }
    }

    private function prepareArguments(Container $container): void
    {
        $this->arguments = [];

        foreach ($this->action->arguments as $name => $providerClass) {
            $provider = $container->instance($providerClass);
            $argument = $provider();
            $container->set($argument);
            $this->arguments[$name] = $argument;
        }
    }

    private function prepareAspects(Container $container): void
    {
        $this->before = [];

        foreach ($this->action->before as $advice) {
            if (is_callable($advice)) {
                $this->before[] = $advice;
                continue;
            }
            $this->before[] = $container->create($advice);
        }

        $this->after = [];

        foreach ($this->action->after as $advice) {
            if (is_callable($advice)) {
                $this->after[] = $advice;
                continue;
            }
            $this->after[] = $container->create($advice);
        }

        $this->around = null;

        if (!empty($this->action->around)) {
            if (is_callable($this->action->around)) {
                $this->around = $this->action->around;
            } else {
                $this->around = $container->instance($this->action->around);
            }
        }
    }

    private function prepareAction(Container $container): void
    {
        if (!is_callable($this->action->handler)) {
            $this->actionInstance = $container->instance($this->action->handler);
        } else {
            $this->actionInstance = $this->action->handler;
        }
    }
}
