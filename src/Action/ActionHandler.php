<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Enum\StateType;
use Duyler\EventBus\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Exception\ActionReturnValueNotExistsException;
use Duyler\EventBus\State;
use Duyler\EventBus\Collections;
use Throwable;

readonly class ActionHandler
{
    public function __construct(
        private Collections            $collections,
        private ActionContainerBuilder $containerBuilder,
        private State                  $state,
    ) {
    }

    public function handle(Action $action): Result
    {
        $container = $this->prepareContainer($action);
        $arguments = $this->prepareArguments($action, $container);

        $this->state->declare(StateType::ActionBefore, $action);

        try {
            $resultData = $this->runAction($action, $container, $arguments);
        } catch (Throwable $exception) {
            $this->state->declare(StateType::ActionThrowing, $action, $exception);
            throw $exception;
        }

        $this->state->declare(StateType::ActionAfter, $action);

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
        return ($actionInstance)(...$arguments);
    }

    private function prepareContainer(Action $action): ActionContainer
    {
        $container = $this->containerBuilder->build($action->id);

        $completeTasks = $this->collections->task()->getAllByArray($action->required->getArrayCopy());

        foreach ($completeTasks as $task) {
            $container->set($task->result->data);
        }

        $container->bind($action->classMap);
        $container->setProviders($action->providers);

        $this->collections->container()->add($container);

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
}
