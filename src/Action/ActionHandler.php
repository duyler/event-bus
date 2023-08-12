<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\TaskCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Exception\ActionReturnValueNotExistsException;
use Duyler\EventBus\Exception\ArgumentsNotResolvedException;
use Duyler\EventBus\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\State\StateAction;
use Throwable;

readonly class ActionHandler
{
    public function __construct(
        private ActionContainerBuilder    $containerBuilder,
        private StateAction               $stateAction,
        private TaskCollection            $taskCollection,
        private ActionContainerCollection $containerCollection
    ) {
    }

    public function handle(Action $action): Result
    {
        $container = $this->prepareContainer($action);

        $this->stateAction->before($action);

        try {
            $actionInstance = $this->prepareAction($action, $container);
            $arguments = $this->prepareArguments($action);
            $resultData = ($actionInstance)(...$arguments);
        } catch (Throwable $exception) {
            $this->stateAction->throwing($action, $exception);
            throw $exception;
        }

        $this->stateAction->after($action);

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

    private function prepareContainer(Action $action): ActionContainer
    {
        $container = $this->containerBuilder->build($action->id);

        $container->bind($action->classMap);
        $container->setProviders($action->providers);

        $this->containerCollection->save($container);

        return $container;
    }

    /**
     * @throws InvalidArgumentFactoryException
     * @throws ArgumentsNotResolvedException
     */
    private function prepareArguments(Action $action): array
    {
        $container = $this->containerBuilder->build($action->id);
        $completeTasks = $this->taskCollection->getAll();

        foreach ($completeTasks as $task) {
            if ($container->has($task->result->data::class) === false) {
                $container->set($task->result->data);
            }
        }

        $arguments = [];

        foreach ($action->arguments as $name => $class) {

            $contract = null;
            foreach ($completeTasks as $task) {
                if ($task->result->data instanceof $class) {
                    $contract = $task->result->data;
                    break;
                }
            }

            if ($contract === null) {
                $provider = $container->make($class);

                if (is_callable($provider) === false) {
                    throw new InvalidArgumentFactoryException($class);
                }
                $arguments[$name] = $provider();
            } else {
                $arguments[$name] = $contract;
            }
        }

        if (count($arguments) < count($action->arguments)) {
            throw new ArgumentsNotResolvedException();
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
}
