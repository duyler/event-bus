<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\DependencyInjection\Exception\DefinitionIsNotObjectTypeException;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\TaskCollection;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Exception\ActionReturnValueNotExistsException;
use Duyler\EventBus\Exception\ActionReturnValueWillBeCompatibleException;
use Duyler\EventBus\Exception\ArgumentsNotResolvedException;
use Duyler\EventBus\Exception\InvalidArgumentFactoryException;
use Duyler\EventBus\State\StateAction;
use Duyler\EventBus\Task;
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

    /**
     * @throws ActionReturnValueNotExistsException
     * @throws ActionReturnValueWillBeCompatibleException
     * @throws DefinitionIsNotObjectTypeException
     * @throws InvalidArgumentFactoryException
     * @throws Throwable
     * @throws ArgumentsNotResolvedException
     * @throws ActionReturnValueExistsException
     */
    public function handle(Action $action): Result
    {
        $container = $this->prepareContainer($action);

        $this->stateAction->before($action);

        try {
            $actionInstance = $this->prepareAction($action, $container);
            $arguments = $this->prepareArguments($action, $container);
            $resultData = ($actionInstance)(...$arguments);
            $result = $this->prepareResult($action, $resultData);
        } catch (Throwable $exception) {
            $this->stateAction->throwing($action, $exception);
            throw $exception;
        }

        $this->stateAction->after($action);
        return $result;
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
     * @throws DefinitionIsNotObjectTypeException
     */
    private function prepareArguments(Action $action, ActionContainer $container): array
    {
        $completeTasks = $this->taskCollection->getAllByArray($action->required->getArrayCopy());

        foreach ($completeTasks as $task) {
            if ($task->result->status === ResultStatus::Success && $task->result->data !== null) {
                if ($container->has($task->result->data::class)) {
                    $container->set($task->result->data);
                }
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
                $factory = $container->make($class);

                if (is_callable($factory) === false) {
                    throw new InvalidArgumentFactoryException($class);
                }
                $arguments[$name] = $factory();
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

    /**
     * @throws ActionReturnValueExistsException
     * @throws ActionReturnValueNotExistsException
     * @throws ActionReturnValueWillBeCompatibleException
     */
    private function prepareResult(Action $action, mixed $resultData): Result
    {
        if ($resultData instanceof Result) {
            return $resultData;
        }

        if (empty($resultData) === false) {
            if ($action->contract === null) {
                throw new ActionReturnValueExistsException($action->id);
            }

            if ($resultData instanceof $action->contract) {
                return new Result(ResultStatus::Success, $resultData);
            }

            throw new ActionReturnValueWillBeCompatibleException($action->id, $action->contract);
        }

        if ($action->contract !== null) {
            throw new ActionReturnValueNotExistsException($action->id);
        }

        return new Result(ResultStatus::Success);
    }
}
