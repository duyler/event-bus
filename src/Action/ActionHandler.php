<?php

declare(strict_types=1);

namespace Duyler\EventBus\Action;

use Duyler\DependencyInjection\Exception\DefinitionIsNotObjectTypeException;
use Duyler\EventBus\Bus\Event;
use Duyler\EventBus\Collection\ActionCollection;
use Duyler\EventBus\Collection\ActionContainerCollection;
use Duyler\EventBus\Collection\EventCollection;
use Duyler\EventBus\Contract\ActionHandlerInterface;
use Duyler\EventBus\Contract\StateActionInterface;
use Duyler\EventBus\Dto\Action;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Exception\ActionReturnValueExistsException;
use Duyler\EventBus\Exception\ActionReturnValueNotExistsException;
use Duyler\EventBus\Exception\ActionReturnValueWillBeCompatibleException;
use Duyler\EventBus\Exception\InvalidArgumentFactoryException;
use Throwable;

readonly class ActionHandler implements ActionHandlerInterface
{
    public function __construct(
        private ActionContainerBuilder $containerBuilder,
        private StateActionInterface $stateAction,
        private ActionContainerCollection $containerCollection,
        private ActionCollection $actionCollection,
        private ActionSubstitution $actionSubstitution,
        private EventCollection $eventCollection,
    ) {
    }

    /**
     * @throws ActionReturnValueNotExistsException
     * @throws ActionReturnValueWillBeCompatibleException
     * @throws DefinitionIsNotObjectTypeException
     * @throws InvalidArgumentFactoryException
     * @throws Throwable
     * @throws ActionReturnValueExistsException
     */
    public function handle(Action $action): Result
    {
        $container = $this->prepareContainer($action);

        try {
            $actionInstance = $this->prepareAction($action, $container);
            $argument = $this->prepareArgument($action, $container);
            $this->stateAction->before($action);
            $resultData = ($actionInstance)($argument);
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
     * @throws DefinitionIsNotObjectTypeException
     */
    private function prepareArgument(Action $action, ActionContainer $container): mixed
    {
        if (empty($action->argument)) {
            return null;
        }

        $completeTasks = $this->eventCollection->getAllByArray($action->required->getArrayCopy());

        $results = [];

        foreach ($completeTasks as $task) {
            $results = $this->prepareRequiredResults($task) + $results;
        }

        if ($this->actionSubstitution->isSubstituteResult($action->id)) {
            $results = $this->actionSubstitution->getSubstituteResult($action->id) + $results;
        }

        foreach ($results as $interface => $definition) {
            if ($definition instanceof $action->argument) {
                return $definition;
            }
            $container->bind([$interface => $definition::class]);
            $container->set($definition);
        }

        $factory = $container->make($action->argument);

        if (is_callable($factory) === false) {
            throw new InvalidArgumentFactoryException($action->argument);
        }

        return $factory();
    }

    private function prepareRequiredResults(Event $requiredTaskEvent): array
    {
        $results = [];

        if ($requiredTaskEvent->result->status === ResultStatus::Fail) {
            $actionsWithContract = $this->actionCollection->getByContract($requiredTaskEvent->action->contract);

            foreach ($actionsWithContract as $actionWithContract) {
                if ($this->eventCollection->isExists($actionWithContract->id)) {
                    $replaceTaskEvent = $this->eventCollection->get($actionWithContract->id);
                    if ($replaceTaskEvent->result->status === ResultStatus::Success) {
                        $interface = array_search($replaceTaskEvent->result->data::class, $actionWithContract->classMap)
                            ?: $replaceTaskEvent->result->data::class;
                        $results[$interface] = $replaceTaskEvent->result->data;
                        return $results;
                    }
                }
            }
        }

        $interface = array_search($requiredTaskEvent->result->data::class, $requiredTaskEvent->action->classMap)
            ?: $requiredTaskEvent->result->data::class;
        $results[$interface] = $requiredTaskEvent->result->data;

        return $results;
    }

    private function prepareAction(Action $action, ActionContainer $container): callable
    {
        if ($this->actionSubstitution->isSubstituteHandler($action->id)) {
            return $container->make($this->actionSubstitution->getSubstituteHandler($action->id));
        }

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
