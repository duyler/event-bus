<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\AspectHandler;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Coroutine;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Exception\ActionReturnValueNotExistsException;
use Duyler\EventBus\Storage;

use function is_callable;

readonly class ActionHandler
{
    public function __construct(
        private AspectHandler $aspectHandler,
        private Storage       $storage,
        private ActionContainerBuilder $containerBuilder,
    ) {
    }

    public function handle(Action $action): Result
    {
        $container = $this->prepareContainer($action);
        $arguments = $this->prepareArguments($action, $container);

        $this->aspectHandler->runBefore($action, $container, $arguments);

        $resultData = $this->runAction($action, $container, $arguments);

        $this->aspectHandler->runAfter($action, $container, $arguments);

        if ($resultData instanceof Result) {
            return $resultData;
        }

        if (empty($resultData) === false) {
            if ($action->void === true) {
                throw new ActionReturnValueExistsException($action->id);
            }

            return new Result(ResultStatus::Success, $resultData);
        }

        if ($action->void === false) {
            throw new ActionReturnValueNotExistsException($action->id);
        }

        return new Result(ResultStatus::Success);
    }

    private function runAction(Action $action, ActionContainer $container, array $arguments): mixed
    {
        $actionInstance = $this->prepareAction($action, $container);

        if (empty($action->around)) {
            return ($actionInstance)(...$arguments);
        }

        return $this->aspectHandler->runAround($action, $container, $arguments);
    }

    private function prepareContainer(Action $action): ActionContainer
    {
        $container = $this->containerBuilder->build($action->id);

        $completeTasks = $this->storage->task()->getAllByRequired($action->required);

        foreach ($completeTasks as $task) {
            $container->set($task->result->data);
        }

        $container->bind($action->classMap);
        $container->setProviders($action->providers);

        $this->storage->container()->save($container);

        return $container;
    }

    private function prepareArguments(Action $action, ActionContainer $container): array
    {
        $arguments = [];

        foreach ($action->arguments as $name => $providerClass) {
            $provider = $container->make($providerClass);
            $argument = $provider();
            $container->set($argument);
            $arguments[$name] = $argument;
        }

        return $arguments;
    }

    private function prepareAction(Action $action, ActionContainer $container): callable
    {
        if (is_callable($action->handler)) {
            return $action->handler;
        }

        return $container->make($action->handler);
    }

    public function handleCoroutine(Action $action, Coroutine $coroutine, mixed $value): void
    {
        if (is_callable($coroutine->handler)) {
            $coroutine = $coroutine->handler;
        } else {
            $container = $this->storage->container()->get($action->id);
            $container->bind($coroutine->classMap);
            $container->setProviders($coroutine->providers);
            $coroutine = $container->make($coroutine->handler);
        }

        if (extension_loaded('pcntl')) {
            $pid = pcntl_fork();
            if (0 === $pid) {
                $coroutine($value);
            }
        } else {
            $coroutine($value);
        }
    }
}
